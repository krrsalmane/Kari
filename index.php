<?php
require 'config/Database.php';

// Get the PDO connection using static method
$pdo = Database::connect();

// Test query
try {
    $stmt = $pdo->query("SELECT NOW() AS `current_time`");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Connection successful! Current DB time: " . $row['current_time'];
} catch (PDOException $e) {
    echo "Query error: " . $e->getMessage();
}
?>
