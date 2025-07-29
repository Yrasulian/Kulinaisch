<?php

// include_once APP_ROOT . '/../admin/include/config.php';
include_once __DIR__ . '/config.php';
$conn = new PDO(DSN, DB_USER, DB_PASSWORD);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure the database connection is established

?>

