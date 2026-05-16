<?php
/**
 * DATABASE MIGRATION RUNNER
 */

require_once __DIR__ . '/includes/db.php';

$sql = file_get_contents(__DIR__ . '/database/full_schema.sql');

try {
    $pdo->exec($sql);
    echo "SUCCESS: Database schema applied successfully.\n";
} catch (PDOException $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
