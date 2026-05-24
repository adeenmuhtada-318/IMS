<?php
/**
 * ASSET RETURN GATEWAY - TACTICAL IMS
 * Division: Logistics Control
 */

header('Content-Type: application/json');
session_start();

// 1. SESSION LOCK
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_ACCESS']);
    exit;
}

// 2. DATABASE BRIDGE
require_once '../includes/connection.php';

// 3. INGESTION
$assignment_id = (int)($_POST['assignment_id'] ?? 0);

if (!$assignment_id) {
    echo json_encode(['status' => 'error', 'message' => 'MISSING_ASSIGNMENT_ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch Assignment Details
    $stmt = $pdo->prepare("SELECT * FROM Inventory_Assignments WHERE Assignment_ID = ? FOR UPDATE");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();

    if (!$assignment) {
        throw new Exception("ASSIGNMENT_NOT_FOUND");
    }

    if ($assignment['Assignment_Status'] === 'Returned') {
        throw new Exception("ASSET_ALREADY_RETURNED");
    }

    // A. Update Assignment Status
    $upd_assign = $pdo->prepare("UPDATE Inventory_Assignments SET Assignment_Status = 'Returned', return_date = NOW() WHERE Assignment_ID = ?");
    $upd_assign->execute([$assignment_id]);

    // B. Update Asset Status / Inventory Levels
    switch ($assignment['Asset_Category']) {
        case 'Weapon':
            $upd_asset = $pdo->prepare("UPDATE Individual_Weapons SET Status = 'Available' WHERE Weapon_ID = ?");
            $upd_asset->execute([$assignment['Asset_ID']]);
            break;

        case 'Vehicle':
            $upd_asset = $pdo->prepare("UPDATE Vehicle_Assets SET Status = 'Available' WHERE Vehicle_ID = ?");
            $upd_asset->execute([$assignment['Asset_ID']]);
            break;

        case 'Bulk_Item':
            $upd_inv = $pdo->prepare("UPDATE Bulk_Inventory SET Quantity_On_Hand = Quantity_On_Hand + ? WHERE Item_ID = ?");
            $upd_inv->execute([$assignment['Quantity_Issued'], $assignment['Asset_ID']]);
            break;

        default:
            throw new Exception("INVALID_ASSET_CATEGORY");
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Asset successfully returned to inventory.',
        'assignment_id' => $assignment_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'TRANSACTION_FAILURE: ' . $e->getMessage()]);
}
