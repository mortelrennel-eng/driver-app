<?php
$pdo = new PDO("mysql:host=localhost;dbname=eurotaxi_system", "root", ""); 
$stmt = $pdo->query("SELECT latitude, longitude FROM units WHERE plate_number = 'CAV 2607'"); 
print_r($stmt->fetch(PDO::FETCH_ASSOC));
