<?php

require_once __DIR__ . '/admin/include/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('Invalid image id.');
}

$recipe_id = (int)$_GET['id'];

try {
    $conn = new PDO(DSN, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT datei FROM foto WHERE gericht_id = :id LIMIT 1");
    $stmt->bindParam(':id', $recipe_id, PDO::PARAM_INT);
    $stmt->execute();
    $foto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($foto && !empty($foto['datei'])) {
        header('Content-Type: image/jpeg');
        echo $foto['datei'];
    } else {
        http_response_code(404);
        exit('Image not found.');
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error.');
}
?>