<?php
require_once __DIR__ . '/include/header.php';

// Initialize variables
$searchTerm = $_GET['search'] ?? '';
$results = [];

// Perform search if search term exists
if (!empty($searchTerm)) {
    try {
        $query = "SELECT * FROM gericht WHERE name LIKE :searchTerm";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . htmlspecialchars($searchTerm) . '%', PDO::PARAM_STR);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error and show user-friendly message
        error_log("Search error: " . $e->getMessage());
        $error = "An error occurred during the search. Please try again.";
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>
            
            <h3>Ihre Ergebnisse</h3>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($results) && !empty($searchTerm)): ?>
                <div class="alert alert-info">No results found for "<?php echo htmlspecialchars($searchTerm); ?>"</div>
            <?php elseif (!empty($results)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Zubereitung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['zubereitung'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>