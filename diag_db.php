<?php
require_once 'includes/connection.php';
try {
    echo "TABLES:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);

    if (in_array('guards_personnel', $tables)) {
        echo "\nCOLUMNS FOR guards_personnel:\n";
        $columns = $pdo->query("DESCRIBE guards_personnel")->fetchAll();
        print_r($columns);
    }
    
    if (in_array('guard_initial_kit', $tables)) {
        echo "\nCOLUMNS FOR guard_initial_kit:\n";
        $columns = $pdo->query("DESCRIBE guard_initial_kit")->fetchAll();
        print_r($columns);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
