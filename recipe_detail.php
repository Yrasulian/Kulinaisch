<?php
// Include the header which contains the database connection and navbar
require_once __DIR__ . '/include/header.php';

// --- 1. VALIDATE AND GET THE RECIPE ID ---
// Check if an 'id' is provided in the URL and if it's a valid number.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If not, we can't proceed. Show an error and stop the script.
    echo '<div class="container"><div class="alert alert-danger mt-4">Ungültige oder fehlende Rezept-ID.</div></div>';
    require_once __DIR__ . '/include/footer.php';
    exit(); // Stop execution
}
$recipe_id = (int)$_GET['id'];


// --- 2. FETCH ALL DATA FOR THE SPECIFIC RECIPE ---
$recipe = null;
$ingredients = [];
$spices = [];
$tools = [];
$error_message = '';

try {
    // --- Query for the main recipe details ---
    // We use LEFT JOINs in case a recipe doesn't have a cuisine or dietary style assigned.
    $query_main = "
        SELECT
            g.title, g.beschreibung, g.zubereitung, g.kalorien, g.zubereitungszeit_min,
            c.title AS cuisine_title,
            e.title AS ernaehrungsweise_title
        FROM gericht g
        LEFT JOIN cuisine c ON g.cuisine_id = c.id
        LEFT JOIN ernaehrungsweise e ON g.ernaehrungsweise_id = e.id
        WHERE g.id = :id
    ";
    $stmt_main = $conn->prepare($query_main);
    $stmt_main->bindParam(':id', $recipe_id, PDO::PARAM_INT);
    $stmt_main->execute();
    $recipe = $stmt_main->fetch(PDO::FETCH_ASSOC);

    // --- Query for the ingredients ---
    $query_ingredients = "
        SELECT l.title, gl.menge
        FROM gericht_lebensmittel gl
        JOIN lebensmittel l ON gl.lebensmittel_id = l.id
        WHERE gl.gericht_id = :id
        ORDER BY l.title
    ";
    $stmt_ingredients = $conn->prepare($query_ingredients);
    $stmt_ingredients->bindParam(':id', $recipe_id, PDO::PARAM_INT);
    $stmt_ingredients->execute();
    $ingredients = $stmt_ingredients->fetchAll(PDO::FETCH_ASSOC);

    // --- Query for the spices (Gewürze) ---
    $query_spices = "
        SELECT gw.title, gg.menge
        FROM gericht_gewuerz gg
        JOIN gewuerz gw ON gg.gewuerz_id = gw.id
        WHERE gg.gericht_id = :id
        ORDER BY gw.title
    ";
    $stmt_spices = $conn->prepare($query_spices);
    $stmt_spices->bindParam(':id', $recipe_id, PDO::PARAM_INT);
    $stmt_spices->execute();
    $spices = $stmt_spices->fetchAll(PDO::FETCH_ASSOC);

    // --- Query for the kitchen tools (Küchengeräte) ---
    $query_tools = "
        SELECT k.title
        FROM gericht_kuechengeraet gk
        JOIN kuechengeraet k ON gk.kuechengeraet_id = k.id
        WHERE gk.gericht_id = :id
        ORDER BY k.title
    ";
    $stmt_tools = $conn->prepare($query_tools);
    $stmt_tools->bindParam(':id', $recipe_id, PDO::PARAM_INT);
    $stmt_tools->execute();
    $tools = $stmt_tools->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    $error_message = "Fehler beim Abrufen der Rezeptdetails: " . $e->getMessage();
}

// --- 3. DISPLAY THE PAGE ---
?>

<div class="container mt-5">

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>

    <?php elseif (!$recipe): ?>
        <div class="alert alert-warning">Das angeforderte Rezept mit der ID <?= htmlspecialchars($recipe_id) ?> konnte nicht gefunden werden.</div>
        <a href="index.php" class="btn btn-primary">Zurück zur Übersicht</a>

    <?php else: ?>
        <!-- Recipe content exists, display it -->
        <div class="row">
            <div class="col-md-5 mb-4">
                <!-- Placeholder Image -->
                <img src="https://placehold.co/800x600/EFEFEF/AAAAAA?text=<?= urlencode($recipe['title']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($recipe['title']) ?>">
            </div>
            <div class="col-md-7">
                <h1 class="display-5"><?= htmlspecialchars($recipe['title']) ?></h1>
                <p class="lead text-muted"><?= htmlspecialchars($recipe['beschreibung']) ?></p>
                <hr>
                <!-- Meta Information Badges -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php if ($recipe['zubereitungszeit_min']): ?>
                        <span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill fs-6"><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($recipe['zubereitungszeit_min']) ?> Min.</span>
                    <?php endif; ?>
                    <?php if ($recipe['kalorien']): ?>
                        <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill fs-6"><i class="fas fa-fire-alt me-1"></i> ca. <?= htmlspecialchars($recipe['kalorien']) ?> kcal</span>
                    <?php endif; ?>
                    <?php if ($recipe['cuisine_title']): ?>
                        <span class="badge bg-warning-subtle border border-warning-subtle text-warning-emphasis rounded-pill fs-6"><i class="fas fa-globe-americas me-1"></i> <?= htmlspecialchars($recipe['cuisine_title']) ?></span>
                    <?php endif; ?>
                    <?php if ($recipe['ernaehrungsweise_title']): ?>
                        <span class="badge bg-info-subtle border border-info-subtle text-info-emphasis rounded-pill fs-6"><i class="fas fa-leaf me-1"></i> <?= htmlspecialchars($recipe['ernaehrungsweise_title']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Ingredients & Tools Column -->
            <div class="col-lg-4">
                <?php if (!empty($ingredients)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Zutaten</h4>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($ingredients as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['title']) ?>
                                    <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($item['menge']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($spices)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h4 class="mb-0">Gewürze</h4></div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($spices as $item): ?>
                                <li class="list-group-item"><?= htmlspecialchars($item['title']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tools)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h4 class="mb-0">Benötigte Küchengeräte</h4></div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($tools as $item): ?>
                                <li class="list-group-item"><?= htmlspecialchars($item['title']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Preparation Column -->
            <!-- Preparation Column -->
        <div class="col-lg-8">
            <h2>Zubereitung</h2>
            <div class="bg-light p-4 rounded" style="line-height: 1.7;">
                <?php
                    // Use the null coalescing operator (??) to provide a default value
                    // if $recipe['zubereitung'] is null. This prevents the deprecated warning.
                    $zubereitung_text = $recipe['zubereitung'] ?? '<p class="text-muted">Für dieses Rezept ist keine Zubereitungsanleitung vorhanden.</p>';
                    
                    // The nl2br() function should be used on the raw text BEFORE htmlspecialchars()
                    // if you want to preserve HTML in your fallback message.
                    // However, for plain text from DB, the order is less critical.
                    // A safer approach is to handle them separately.

                    if ($recipe['zubereitung'] !== null) {
                        // If text exists, escape it and add line breaks
                        echo nl2br(htmlspecialchars($recipe['zubereitung']));
                    } else {
                        // If it's null, just output the safe fallback message
                        echo '<p class="text-muted fst-italic">Für dieses Rezept ist keine Zubereitungsanleitung vorhanden.</p>';
                    }
                ?>
            </div>
        </div>
        </div>

        <div class="text-center my-5">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">« Zurück zur vorherigen Seite</a>
        </div>

    <?php endif; ?>

</div>

<?php
// Include the footer
require_once __DIR__ . '/include/footer.php';
?>