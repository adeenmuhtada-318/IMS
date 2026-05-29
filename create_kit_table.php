<?php
require_once 'includes/connection.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS guard_initial_kit (
        kit_id INT AUTO_INCREMENT PRIMARY KEY,
        guard_id INT NOT NULL,
        shirt_trousers TINYINT DEFAULT 0,
        cap TINYINT DEFAULT 0,
        belt TINYINT DEFAULT 0,
        boots TINYINT DEFAULT 0,
        jersey TINYINT DEFAULT 0,
        FOREIGN KEY (guard_id) REFERENCES guards_personnel(guard_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $pdo->exec($sql);
    echo "Table guard_initial_kit created successfully.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
