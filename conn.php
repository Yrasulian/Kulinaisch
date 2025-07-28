<?php

$con = new  PDO('mysql:host=localhost;dbname=mmdba_sose_25_8;charset=utf8mb4', 'root', '&$)2n6D708MkDVAp' ); 
// $con = new  PDO('mysql:host=http://fm-cms-training.htwk-leipzig.de/phpmyadmin/index.php; dbname=mmdba_sose_25_8;charset=utf8mb4', 'MMDBA_SOSE_25_8', 'f4436cmf28' );
$query = "SELECT zubereitung FROM gericht WHERE name LIKE :name";
$stmt = $con->prepare($query);

$stmt->bindValue(':name',  '%' . $_POST['name'] . '%', PDO::PARAM_STR);
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    
    echo $row['zubereitung'] . "<br>";
}
?>