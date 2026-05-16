<?php
require_once 'public_html/includes/db_connect.php';
try {
    $pdo_conn->exec(file_get_contents('database/deployment_patch.sql'));
    echo "DEPLOYMENT_PATCH_APPLIED\n";
} catch (PDOException $e) {
    echo "PATCH_ERROR: " . $e->getMessage() . "\n";
}
