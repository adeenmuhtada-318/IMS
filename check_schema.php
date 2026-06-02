<?php
require_once 'includes/connection.php';
try {
    // Ensure shift_type column exists
    $pdo->exec("ALTER TABLE security_guards ADD COLUMN IF NOT EXISTS shift_type ENUM('Morning', 'Evening', 'None') DEFAULT 'None'");
    
    $stmt = $pdo->query("DESCRIBE security_guards");
    print_r($stmt->fetchAll());
} catch (Exception $e) {
    echo $e->getMessage();
}
