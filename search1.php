<?php
require_once __DIR__ . '/include/header.php';

// --- SEARCH LOGIC ---

$searchTerm = $_GET['gericht'] ?? '';
$results = [];
$error = null;

// Perform search only if a search term is provided
if (!empty($searchTerm)) {
    try {
        // Get all filter values from the form
        $kueachenart = $_GET['cuisine_id'] ?? '';
        $dauer = $_GET['dauer'] ?? '';
        // Note: 'lebensmittel' and 'menge' are not used in this query yet.
        // That would require a more complex JOIN-based search.

        // --- 1. THE NEW, POWERFUL SQL QUERY ---
        // We use a Common Table Expression (CTE) with UNION ALL to search across tables.
        // The CASE statement creates a priority score for sorting.
        $sql = "
            WITH AllSearchableItems AS (
                -- Search in 'gericht' (Dishes)
                SELECT
                    id,
                    title AS name,
                    beschreibung AS description,
                    'Rezept' AS result_type,
                    CONCAT('recipe.php?id=', id) AS link
                FROM gericht
                WHERE 1=1 --GERICHT_FILTERS--

                UNION ALL

                -- Search in 'lebensmittel' (Food Items)
                SELECT
                    id,
                    title AS name,
                    NULL AS description, -- No description here
                    'Lebensmittel' AS result_type,
                    CONCAT('food_item.php?id=', id) AS link
                FROM lebensmittel

                UNION ALL

                -- Search in 'gewuerz' (Spices)
                SELECT
                    id,
                    title AS name,
                    NULL AS description, -- No description here
                    'Gewürz' AS result_type,
                    CONCAT('spice.php?id=', id) AS link
                FROM gewuerz

                UNION ALL

                -- Search in 'ernaehrungsweise' (Dietary Styles)
                SELECT
                    id,
                    bezeichnung AS name,
                    NULL AS description, -- CORRECTED: Assumes no description column here
                    'Ernährungsweise' AS result_type,
                    CONCAT('diet.php?id=', id) AS link
                FROM ernaehrungsweise
            )
            -- The rest of the query remains the same
            SELECT
                id, name, description, result_type, link,
                CASE
                    WHEN LOWER(name) = :searchTermExact    THEN 1
                    WHEN LOWER(name) LIKE :searchTermStart THEN 2
                    WHEN LOWER(name) LIKE :searchTermAll   THEN 3
                END AS match_priority
            FROM AllSearchableItems
            WHERE LOWER(name) LIKE :searchTermAll
            ORDER BY
                match_priority ASC,
                name ASC;
        ";

        // --- 2. DYNAMICALLY ADD FILTERS FOR 'gericht' TABLE ---
        $gerichtFilters = "";
        $params = [];

        if (!empty($kueachenart)) {
            $gerichtFilters .= " AND cuisine_id = :cuisine";
            $params[':cuisine'] = $kueachenart;
        }
        if (!empty($dauer)) {
            $gerichtFilters .= " AND dauer <= :dauer";
            $params[':dauer'] = (int)$dauer;
        }

        // Replace the placeholder in the SQL with the actual filter conditions
        $query = str_replace('--GERICHT_FILTERS--', $gerichtFilters, $sql);

        // --- 3. PREPARE PARAMETERS FOR THE SEARCH ---
        // We need three versions of the search term for our ranking logic
        $params[':searchTermExact'] = $searchTerm;
        $params[':searchTermStart'] = $searchTerm . '%';
        $params[':searchTermAll'] = '%' . $searchTerm . '%';

        // --- 4. EXECUTE THE QUERY ---
        $stmt = $conn->prepare($query);
        // No need for a foreach loop, just pass the params array to execute()
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $error = "Ein Fehler ist bei der Suche aufgetreten. Bitte versuchen Sie es erneut.";
    }
}
?>


<form class="py-5 container" action="search1.php" method="get">
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
                            <span id="dauer-wert" class="badge bg-light text-dark fs-6 py-2 px-3"><?= htmlspecialchars($_GET['dauer'] ?? '60') ?> min</span>
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
    
</form>

<!-- --- UPDATED RESULTS SECTION --- -->
<div class="container py-5">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($searchTerm)): ?>
        <h2 class="text-center mb-4">Suchergebnisse für "<?= htmlspecialchars($searchTerm) ?>"</h2>
        
        <?php if (empty($results)): ?>
            <div class="alert alert-warning text-center">Keine passenden Ergebnisse gefunden.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($results as $result): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge 
                                        <?php 
                                            // Assign a different color based on the result type
                                            switch ($result['result_type']) {
                                                case 'Rezept': echo 'bg-primary'; break;
                                                case 'Lebensmittel': echo 'bg-success'; break;
                                                case 'Gewürz': echo 'bg-danger'; break;
                                                case 'Ernährungsweise': echo 'bg-info text-dark'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>">
                                        <?= htmlspecialchars($result['result_type']) ?>
                                    </span>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($result['name']) ?></h5>
                                
                                <?php if (!empty($result['description'])): ?>
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars(substr($result['description'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <!-- The link is now dynamic based on the result type -->
                                <a href="<?= htmlspecialchars($result['link']) ?>" class="btn btn-outline-primary w-100">Details ansehen</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Your JavaScript (no changes needed here) -->
<script>
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