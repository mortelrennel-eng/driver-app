<?php
$host = 'localhost';
$db = 'u747826271_eurotaxi';
$user = 'u747826271_eurotaxi';
$pass = 'Admineuro2026';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'support_messages'");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(count($tables) > 0) {
        echo "Table exists!\n";
        
        $stmt = $pdo->query("DESCRIBE support_messages");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($cols);
    } else {
        echo "Table does NOT exist!\n";
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
