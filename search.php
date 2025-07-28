<?php
require_once __DIR__ . '/include/header.php';

$searchTerm = $_GET['gericht'] ?? '';
$results = [];

// Perform search if search term exists
if (!empty($searchTerm)) {
    try {
        $kueachenart = $_GET['cuisine_id'] ?? '';
        $dauer = $_GET['dauer'] ?? '';
        $lebensmittel = $_GET['lebensmittel'] ?? [];
        $menge = $_GET['menge'] ?? [];

        // Build the base query
        $query = "SELECT * FROM gericht WHERE name LIKE :searchTerm";
        $params = [
            ':searchTerm' => '%' . htmlspecialchars($searchTerm) . '%'
        ];

        // Add cuisine filter if selected
        if (!empty($kueachenart)) {
            $query .= " AND cuisine_id = :cuisine";
            $params[':cuisine'] = htmlspecialchars($kueachenart);
        }

        // Add duration filter
        if (!empty($dauer)) {
            $query .= " AND dauer <= :dauer";
            $params[':dauer'] = (int)$dauer;
        }

        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error and show user-friendly message
        error_log("Search error: " . $e->getMessage());
        $error = "An error occurred during the search. Please try again.";
    }
}
?>


<form class="py-5 container" action="search.php" method="get">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title text-center text-primary mb-4 fw-bold">Rezeptsuche</h1>
                    <div class="mb-3">
                        <label for="gericht" class="form-label fw-semibold">Gerichtename</label>
                        <input type="text" class="form-control" id="gericht" name="gericht" placeholder="z.B. Spaghetti Bolognese, Moussaka..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="cuisine" class="form-label fw-semibold">Küchenart / Cuisine</label>
                        <select class="form-select" id="cuisine" name="cuisine_id">
                            <option value="">Alle Küchen</option>
                            <option value="1" <?= ($_GET['cuisine_id'] ?? '') == '1' ? 'selected' : '' ?>>Italienische Küche</option>
                            <option value="7" <?= ($_GET['cuisine_id'] ?? '') == '7' ? 'selected' : '' ?>>Deutsche Küche</option>
                            <option value="3" <?= ($_GET['cuisine_id'] ?? '') == '3' ? 'selected' : '' ?>>Mexikanische Küche</option>
                            <option value="5" <?= ($_GET['cuisine_id'] ?? '') == '5' ? 'selected' : '' ?>>Indische Küche</option>
                            <option value="4" <?= ($_GET['cuisine_id'] ?? '') == '4' ? 'selected' : '' ?>>Japanische Küche</option>
                            <option value="6" <?= ($_GET['cuisine_id'] ?? '') == '6' ? 'selected' : '' ?>>Thailändische Küche</option>
                            <option value="34" <?= ($_GET['cuisine_id'] ?? '') == '34' ? 'selected' : '' ?>>Asiatische Fusionsküche</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="dauer" class="form-label fw-semibold">Maximale Zubereitungszeit</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-range" id="dauer" name="dauer" min="10" max="240" value="<?= htmlspecialchars($_GET['dauer'] ?? '60') ?>" step="5">
                            <span id="dauer-wert" class="badge bg-light text-dark fs-6 py-2 px-3"><?= htmlspecialchars($_GET['dauer'] ?? '10') ?> min</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Benötigte Lebensmittel / Zutaten</label>
                        <div id="zutaten-container" class="p-3 border border-2 border-dashed rounded bg-light">
                            <div id="zutaten-liste">
                                <?php
                                $lebensmittelCount = max(1, count($_GET['lebensmittel'] ?? []));
                                for ($i = 0; $i < $lebensmittelCount; $i++) {
                                    echo '<div class="row g-2 mb-2">
                                        <div class="col">
                                            <input type="text" class="form-control" name="lebensmittel[]" placeholder="Lebensmittel, z.B. Tomate" value="'.htmlspecialchars($_GET['lebensmittel'][$i] ?? '').'">
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" name="menge[]" placeholder="Menge, z.B. 2 Stk." value="'.htmlspecialchars($_GET['menge'][$i] ?? '').'">
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

<!-- Results Section -->
<div class="container py-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($searchTerm)): ?>
        <h2 class="text-center mb-4">Suchergebnisse</h2>
        
        <?php if (empty($results)): ?>
            <div class="alert alert-warning">Keine passenden Rezepte gefunden.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($results as $gericht): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <!-- You might want to add an image here -->
                            <!-- <img src="<?= htmlspecialchars($gericht['image_path'] ?? '') ?>" class="card-img-top" alt="<?= htmlspecialchars($gericht['name']) ?>"> -->
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($gericht['name']) ?></h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary"><?= htmlspecialchars($gericht['cuisine_name'] ?? $gericht['cuisine_id']) ?></span>
                                    <span class="text-muted"><?= htmlspecialchars($gericht['dauer']) ?> min</span>
                                </div>
                                <p class="card-text"><?= htmlspecialchars(substr($gericht['beschreibung'] ?? '', 0, 100)) ?>...</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="recipe.php?id=<?= $gericht['id'] ?>" class="btn btn-outline-primary w-100">Rezept ansehen</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- JavaScript for dynamic form elements -->
<script>
    // Update duration display
    const dauerSlider = document.getElementById('dauer');
    const dauerWertAnzeige = document.getElementById('dauer-wert');

    function updateDauerAnzeige() {
        dauerWertAnzeige.textContent = dauerSlider.value + ' min';
    }

    dauerSlider.addEventListener('input', updateDauerAnzeige);
    updateDauerAnzeige();

    // Add ingredient fields dynamically
    const addZutatButton = document.getElementById('add-zutat-btn');
    const zutatenListe = document.getElementById('zutaten-liste');

    addZutatButton.addEventListener('click', function() {
        const neueZeile = document.createElement('div');
        neueZeile.className = 'row g-2 mb-2';
        neueZeile.innerHTML = `
            <div class="col">
                <input type="text" class="form-control" name="lebensmittel[]" placeholder="Lebensmittel">
            </div>
            <div class="col">
                <input type="text" class="form-control" name="menge[]" placeholder="Menge">
            </div>
        `;
        zutatenListe.appendChild(neueZeile);
    });
</script>

<?php require_once __DIR__ . '/include/footer.php'; ?>