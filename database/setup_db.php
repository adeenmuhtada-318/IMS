<?php
/**
 * DATABASE INITIALIZATION UTILITY - TACTICAL IMS
 * Division: System Operations
 */

header('Content-Type: text/plain');

require_once 'includes/connection.php';

try {
    echo "--- INITIALIZING DATABASE: SecurityFirm_Inventory ---\n";
    
    // Read schema file
    $sqlPath = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("CRITICAL_FAILURE: schema.sql file not found at $sqlPath");
    }
    
    $sql = file_get_contents($sqlPath);
    
    // We need to execute the script. Since schema.sql contains multiple statements 
    // and potentially DELIMITER changes, a simple exec() might not handle procedures.
    // However, for standard CREATE TABLEs it works. 
    // PDO::exec() handles multiple statements separated by ; in many MySQL configurations.
    
    // Split by ; (note: this is a naive split and might break on procedures, but schema.sql is mostly tables)
    // Actually, PDO::exec can handle multiple queries if the driver supports it.
    
    $pdo->exec($sql);
    
    echo "SUCCESS: Master schema synchronized successfully.\n";
    echo "New tables (including 'clients') are now active.\n";
    
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
