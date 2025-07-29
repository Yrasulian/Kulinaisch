<?php

define('APP_ROOT', __DIR__);

include __DIR__ . '/../admin/include/db.php';
// include ('./../admin/include/db.php');


$ernaehrungsweisen = [];
try {
    $ernaehrung_query = "SELECT id, title FROM ernaehrungsweise ORDER BY title";
    $ernaehrung_stmt = $conn->prepare($ernaehrung_query);
    $ernaehrung_stmt->execute();
    $ernaehrungsweisen = $ernaehrung_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Fehler beim Laden der Ernährungsweisen: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulinarisch</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/all.min.css">
    <link rel="stylesheet" href="./css/bootrtrap-grid.css">
    <link rel="stylesheet" href="./css/sign-in.css">    
    <link rel="stylesheet" href=".css/navbars-offcanvas.css">
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

    </style>
    
    
</head>
<body>
    <div class="container">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="./index.php">Kulinarisch</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="./index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./search1.php">rezeptsuche</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " aria-current="page" href="./import.php">Rezept Hinzufügen</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Ernaehrungsweise</a>
                        <ul class="dropdown-menu from-select" id="ernaehrungsweise" name="ernaehrungsweise" >
                            
                                <?php foreach ($ernaehrungsweisen as $ernaehrung): ?>
                                    <option value="<?php echo $ernaehrung['id']; ?>" 
                                            <?php echo (isset($_POST['ernaehrungsweise']) && $_POST['ernaehrungsweise'] == $ernaehrung['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ernaehrung['title']); ?>
                                   
                                <?php endforeach; ?>

                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./sign-in.php">Anmelden</a>
                    </li>
                </ul>
                <form class="d-flex" role="search" action="../headersearch.php" method="get">
                    <input class="form-control me-2" name = "search" type="text" placeholder="Search" aria-label="Search"/>
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    </div> 