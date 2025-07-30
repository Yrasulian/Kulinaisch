<?php
require_once __DIR__ . '/include/header.php';

// --- 1. DATA PREPARATION FOR THE FORM ---

$cuisines = [];
$ernaehrungsweisen = [];
$error_message = '';

try {
    $cuisine_stmt = $conn->prepare("SELECT id, title FROM cuisine ORDER BY title");
    $cuisine_stmt->execute();
    $cuisines = $cuisine_stmt->fetchAll(PDO::FETCH_ASSOC);

    $ernaehrung_stmt = $conn->prepare("SELECT id, title FROM ernaehrungsweise ORDER BY title");
    $ernaehrung_stmt->execute();
    $ernaehrungsweisen = $ernaehrung_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Fehler beim Laden der Formulardaten: " . $e->getMessage();
}

// --- 2. GET AND SANITIZE USER INPUT ---

$is_search_submitted = !empty(array_filter($_GET));
$searchTerm = isset($_GET['gericht']) ? trim($_GET['gericht']) : '';
$cuisineId = isset($_GET['cuisine_id']) && $_GET['cuisine_id'] !== '' ? (int)$_GET['cuisine_id'] : null;
$ernaehrungsweiseId = isset($_GET['ernaehrungsweisen_id']) && $_GET['ernaehrungsweisen_id'] !== '' ? (int)$_GET['ernaehrungsweisen_id'] : null;
$maxZubereitungszeit = isset($_GET['dauer']) && $_GET['dauer'] !== '' ? (int)$_GET['dauer'] : null;
$inputLebensmittel = isset($_GET['lebensmittel']) ? array_filter((array)$_GET['lebensmittel'], 'trim') : [];
$searchResults = [];

// --- 3. BUILD AND EXECUTE THE ADVANCED SEARCH QUERY ---

if ($is_search_submitted) {
    try {
        $sql = "
            SELECT
                g.id,
                g.title,
                g.beschreibung,
                g.zubereitungszeit_min,
                'Rezept' as result_type,
                CONCAT('recipe_detail.php?id=', g.id) as link,
                (CASE WHEN :searchTerm != '' THEN MATCH(g.title, g.beschreibung) AGAINST (:searchTerm IN NATURAL LANGUAGE MODE) ELSE 0 END) AS title_score,
                (CASE WHEN g.cuisine_id = :cuisineId THEN 20 ELSE 0 END) AS cuisine_score,
                (CASE WHEN g.ernaehrungsweise_id = :ernaehrungsweiseId THEN 10 ELSE 0 END) AS ernaehrung_score,
                COUNT(DISTINCT CASE WHEN l.title IN (dummy) THEN l.id END) AS matching_ingredients_count
            FROM gericht g
            LEFT JOIN gericht_lebensmittel gl ON g.id = gl.gericht_id
            LEFT JOIN lebensmittel l ON gl.lebensmittel_id = l.id
        ";

        $whereClauses = [];
        if (!empty($searchTerm)) {
            $whereClauses[] = "MATCH(g.title, g.beschreibung) AGAINST (:searchTermWhere IN NATURAL LANGUAGE MODE)";
        }
        if (!empty($maxZubereitungszeit)) {
            $whereClauses[] = "g.zubereitungszeit_min <= :maxZubereitungszeit";
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " GROUP BY g.id ";

        if (!empty($inputLebensmittel)) {
             $sql .= " HAVING matching_ingredients_count > 0";
        }

        $sql .= "
            ORDER BY
                (title_score * 5) + (cuisine_score) + (ernaehrung_score) + (matching_ingredients_count * 50) DESC,
                matching_ingredients_count DESC,
                title_score DESC,
                g.title ASC
        ";

        // *** FIX: GENERATE NAMED PLACEHOLDERS FOR INGREDIENTS ***
        if (!empty($inputLebensmittel)) {
            $ingredientPlaceholders = [];
            foreach ($inputLebensmittel as $key => $value) {
                $ingredientPlaceholders[] = ':ingred' . $key;
            }
            $sql = str_replace('IN (dummy)', 'IN (' . implode(',', $ingredientPlaceholders) . ')', $sql);
        } else {
            $sql = str_replace('IN (dummy)', 'IN (NULL)', $sql);
        }

        $stmt = $conn->prepare($sql);

        // BIND PARAMETERS
        // Bind static and scoring parameters
        $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':cuisineId', $cuisineId, PDO::PARAM_INT);
        $stmt->bindValue(':ernaehrungsweiseId', $ernaehrungsweiseId, PDO::PARAM_INT);

        // Bind parameters for the WHERE clause
        if (!empty($searchTerm)) {
            $stmt->bindValue(':searchTermWhere', $searchTerm, PDO::PARAM_STR);
        }
        if (!empty($maxZubereitungszeit)) {
            $stmt->bindValue(':maxZubereitungszeit', $maxZubereitungszeit, PDO::PARAM_INT);
        }

        // *** FIX: BIND INGREDIENTS USING THEIR NEW NAMED PLACEHOLDERS ***
        if (!empty($inputLebensmittel)) {
            foreach ($inputLebensmittel as $key => $value) {
                $stmt->bindValue(':ingred' . $key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($searchResults)) {
            $error_message = "Keine passenden Rezepte für Ihre Kriterien gefunden.";
        }

    } catch (PDOException $e) {
        $error_message = "Fehler bei der Suche: " . $e->getMessage();
    }
}
?>

<!-- The HTML form is correct and does not need changes. It uses 'dauer' as the name, which we handle in PHP. -->
<form class="py-5 container" action="search.php" method="get">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title text-center text-primary mb-4 fw-bold">Erweiterte Rezeptsuche</h1>
                    
                    <?php if ($error_message && !$is_search_submitted): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="gericht" class="form-label fw-semibold">Suchen nach (Titel, Beschreibung)</label>
                        <input type="text" class="form-control" id="gericht" name="gericht" placeholder="z.B. Spaghetti, Auflauf, Hähnchen..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cuisine" class="form-label fw-semibold">Küchenart / Cuisine</label>
                            <select class="form-select" id="cuisine" name="cuisine_id">
                                <option value="">Alle Küchen</option>
                                <?php foreach ($cuisines as $cuisine): ?>
                                    <option value="<?= htmlspecialchars($cuisine['id']) ?>" <?= ($cuisineId === (int)$cuisine['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cuisine['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ernaehrungsweise" class="form-label fw-semibold">Ernährungsweise</label>
                            <select id="ernaehrungsweisen_id" name="ernaehrungsweisen_id" class="form-select">
                                <option value="">Alle Ernährungsweisen</option>
                                <?php foreach ($ernaehrungsweisen as $ernaehrung): ?>
                                    <option value="<?= htmlspecialchars($ernaehrung['id']) ?>" <?= ($ernaehrungsweiseId === (int)$ernaehrung['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ernaehrung['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="dauer" class="form-label fw-semibold">Maximale Zubereitungszeit</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-range" id="dauer" name="dauer" min="10" max="240" value="<?= htmlspecialchars($maxZubereitungszeit ?? '240') ?>" step="5">
                            <span id="dauer-wert" class="badge bg-light text-dark fs-6 py-2 px-3"><?= htmlspecialchars($maxZubereitungszeit ?? '240') ?> min</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Benötigte Lebensmittel / Zutaten (optional)</label>
                        <div id="zutaten-container" class="p-3 border border-2 border-dashed rounded bg-light">
                            <div id="zutaten-liste">
                                <?php
                                $lebensmittelToDisplay = !empty($inputLebensmittel) ? $inputLebensmittel : [''];
                                foreach ($lebensmittelToDisplay as $lebensmittel) {
                                    echo '<div class="row g-2 mb-2 zutat-zeile">
                                        <div class="col">
                                            <input type="text" class="form-control" name="lebensmittel[]" placeholder="z.B. Tomate" value="'.htmlspecialchars($lebensmittel).'">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-outline-danger remove-zutat-btn">×</button>
                                        </div>
                                    </div>';
                                }
                                ?>
                            </div>
                            <button type="button" id="add-zutat-btn" class="btn btn-sm btn-success mt-2">
                                <i class="bi bi-plus-circle me-1"></i> Weitere Zutat hinzufügen
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gradient-primary w-100 p-3 fs-5 fw-bold mt-3 rounded-3">Rezepte finden</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- --- 4. DISPLAY SEARCH RESULTS --- -->
<div class="container py-5">
    <?php if ($is_search_submitted): ?>
        <h2 class="text-center mb-4">Suchergebnisse</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-warning text-center"><?= htmlspecialchars($error_message) ?></div>
        <?php elseif (!empty($searchResults)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($searchResults as $result): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm recipe-card">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-primary"><?= htmlspecialchars($result['result_type']) ?></span>
                                    <?php if ($result['matching_ingredients_count'] > 0): ?>
                                        <span class="badge bg-success">
                                            <?= $result['matching_ingredients_count'] ?> Zutat(en) passen
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($result['title']) ?></h5>
                                
                                <?php if (!empty($result['beschreibung'])): ?>
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars(substr($result['beschreibung'], 0, 120)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 pt-0">
                                <a href="<?= htmlspecialchars($result['link']) ?>" class="btn btn-outline-primary w-100">Details ansehen</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>


<script>
    // The JavaScript does not need any changes.
    const dauerSlider = document.getElementById('dauer');
    const dauerWertAnzeige = document.getElementById('dauer-wert');
    if (dauerSlider) {
        function updateDauerAnzeige() {
            dauerWertAnzeige.textContent = dauerSlider.value + ' min';
        }
        dauerSlider.addEventListener('input', updateDauerAnzeige);
        updateDauerAnzeige();
    }

    const addZutatButton = document.getElementById('add-zutat-btn');
    const zutatenListe = document.getElementById('zutaten-liste');

    if (addZutatButton) {
        addZutatButton.addEventListener('click', function() {
            const neueZeile = document.createElement('div');
            neueZeile.className = 'row g-2 mb-2 zutat-zeile';
            neueZeile.innerHTML = `
                <div class="col">
                    <input type="text" class="form-control" name="lebensmittel[]" placeholder="z.B. Mehl">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger remove-zutat-btn">×</button>
                </div>
            `;
            zutatenListe.appendChild(neueZeile);
        });
    }

    if (zutatenListe) {
        zutatenListe.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-zutat-btn')) {
                if (document.querySelectorAll('.zutat-zeile').length > 1) {
                    e.target.closest('.zutat-zeile').remove();
                } else {
                    e.target.closest('.zutat-zeile').querySelector('input[name="lebensmittel[]"]').value = '';
                }
            }
        });
    }
</script>

<?php require_once __DIR__ . '/include/footer.php'; ?>