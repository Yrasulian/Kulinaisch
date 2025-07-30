<?php
// Use the new header file which contains the corrected navbar
require_once __DIR__ . '/include/header.php';

// --- 1. FETCH AND PREPARE THE DATA ---

// Check if a filter has been applied from the navbar link
$filter_id = isset($_GET['filter_ernaehrung_id']) ? (int)$_GET['filter_ernaehrung_id'] : null;

$categorized_recipes = [];
$error_message = '';

try {
    // Base of the query
    $query = "
        SELECT
            g.id,
            g.title AS gericht_title,
            g.beschreibung,
            e.title AS ernaehrungsweise_title
        FROM
            gericht g
        JOIN
            ernaehrungsweise e ON g.ernaehrungsweise_id = e.id
        WHERE
            g.beschreibung IS NOT NULL AND g.ernaehrungsweise_id IS NOT NULL
    ";

    // If a filter ID is present in the URL, add a WHERE clause to the query
    if ($filter_id) {
        $query .= " AND g.ernaehrungsweise_id = :filter_id ";
    }

    // Add the final ordering
    $query .= " ORDER BY e.title ASC, g.title ASC";

    $stmt = $conn->prepare($query);

    // If we have a filter ID, bind it to the prepared statement to prevent SQL injection
    if ($filter_id) {
        $stmt->bindParam(':filter_id', $filter_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $all_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group the results into categories (this works for both filtered and unfiltered results)
    foreach ($all_recipes as $recipe) {
        $category_name = $recipe['ernaehrungsweise_title'];
        $categorized_recipes[$category_name][] = $recipe;
    }

} catch (PDOException $e) {
    $error_message = "Fehler beim Laden der Rezepte: " . $e->getMessage();
}
?>

<!-- Welcome Header -->
<div class="container py-5">
    <div class="text-center">
        <h1 class="display-4">Willkommen in unserer Küche</h1>
        <p class="lead text-muted">Entdecken Sie eine Vielfalt an köstlichen Gerichten, sortiert nach Ernährungsweise.</p>
    </div>
</div>

<!-- Main Content: Displaying the Recipes -->
<div class="container">

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif (empty($categorized_recipes)): ?>
        <div class="alert alert-warning text-center">
            Für die gewählte Kategorie wurden keine Rezepte gefunden. <a href="<?= $currentPage ?>" class="alert-link">Alle Kategorien anzeigen</a>.
        </div>
    <?php else: ?>
        
        <!-- Loop through each category -->
        <?php foreach ($categorized_recipes as $category_name => $recipes_in_category): ?>
            
            <div class="category-section mb-5">
                <h2 class="pb-2 border-bottom mb-4"><?= htmlspecialchars($category_name) ?></h2>
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    
                    <!-- For each category, loop through its recipes -->
                    <?php foreach ($recipes_in_category as $recipe): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <img src="https://placehold.co/600x400/EFEFEF/AAAAAA?text=<?= urlencode($recipe['gericht_title']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['gericht_title']) ?>">
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($recipe['gericht_title']) ?></h5>
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars(substr($recipe['beschreibung'], 0, 100)) ?>...
                                    </p>
                                    <a href="recipe_detail.php?id=<?= htmlspecialchars($recipe['id']) ?>" class="btn btn-outline-primary mt-auto">Rezept ansehen</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>