<?php

require_once __DIR__ . '/../admin/include/db.php';

// Fetch the dietary styles for the navbar dropdown
$ernaehrungsweisen_nav = [];
try {
    $ernaehrung_nav_query = "SELECT id, title FROM ernaehrungsweise ORDER BY title";
    $ernaehrung_nav_stmt = $conn->prepare($ernaehrung_nav_query);
    $ernaehrung_nav_stmt->execute();
    $ernaehrungsweisen_nav = $ernaehrung_nav_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a real app, you might log this error instead of displaying it.
    // For now, we'll just have an empty dropdown on failure.
}

// Get the name of the current page to build self-referencing links
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulinarisch</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <!-- Add other CSS files as needed -->
    <style>
        :root {
            --bs-primary: #e67e22;
            --bs-primary-rgb: 230, 126, 34;
            --bs-body-bg: #f8f9fa; 
        }
        .btn-gradient-primary {
            color: white;
            background: linear-gradient(90deg, #f39c12, #e67e22);
            border: none;
        }
        .btn-gradient-primary:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        }
        .category-section .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .category-section .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary rounded mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="./index.php">Kulinarisch</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                    <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="./index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./search1.php">Rezeptsuche</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./import.php">Rezept Hinzufügen</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Ernährungsweise</a>
                            
                            <!-- *** CORRECTED DROPDOWN MENU *** -->
                            <ul class="dropdown-menu">
                                <!-- Link to show all categories (removes the filter) -->
                                <li><a class="dropdown-item" href="<?= $currentPage ?>">Alle anzeigen</a></li>
                                <li><hr class="dropdown-divider"></li>

                                <!-- Dynamically create a link for each dietary style -->
                                <?php foreach ($ernaehrungsweisen_nav as $ernaehrung): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= $currentPage ?>?filter_ernaehrung_id=<?= htmlspecialchars($ernaehrung['id']) ?>">
                                            <?= htmlspecialchars($ernaehrung['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./sign-in.php">Anmelden</a>
                        </li>
                    </ul>
                    <!-- This search form should probably point to search1.php -->
                    <form class="d-flex" role="search" action="search1.php" method="get">
                        <input class="form-control me-2" name="gericht" type="search" placeholder="Rezept suchen..." aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Suchen</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>