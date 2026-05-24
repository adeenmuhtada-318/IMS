<?php
header('Content-Type: text/plain');
require_once 'includes/connection.php';
try {
    $stmt = $pdo->query("DESCRIBE Vehicle_Assets");
    print_r($stmt->fetchAll());
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
