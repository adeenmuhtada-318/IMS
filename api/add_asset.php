<?php
// api/add_asset.php

header('Content-Type: application/json');
require_once '../includes/db_config.php';
require_once '../core/InventoryManager.php';

// Check karein ke data POST request se aaya hai ya nahi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Frontend form se aane wala data payload received
    $data_payload = $_POST; 
    
    // InventoryManager Class ka object banayein aur use db connection pass karein
    $manager = new InventoryManager($pdo_conn);
    
    // Method run karein aur check karein ke data dono tables mein successfully gaya ya nahi
    $is_saved = $manager->register_new_asset($data_payload);
    
    if ($is_saved) {
        echo json_encode(["status" => "success", "message" => "Asset Registered Successfully in 3NF Tables!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save asset. Transaction rolled back."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>