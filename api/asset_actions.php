<?php
/**
 * ASSET ACTIONS GATEWAY - Backend API
 * Handles asynchronous communication between UI and Inventory Engine.
 */

header('Content-Type: application/json');
session_start();

// 1. Dependency Injections
require_once '../includes/db_config.php';
require_once '../core/InventoryManager.php';

// Security Check: Only authenticated operators can trigger asset actions
if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_ACCESS_DENIED']);
    exit;
}

// 2. Initialize Engine
$manager = new InventoryManager($pdo_conn);
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        
        case 'procure':
            /**
             * Handles new asset ingestion into 3NF tables.
             */
            $data_payload = $_POST;
            $result = $manager->record_internal_purchase($data_payload);
            
            if ($result['status'] === 'success') {
                echo json_encode(['status' => 'success', 'message' => 'Procurement transaction committed safely', 'asset_id' => $result['asset_id']]);
            } else {
                throw new Exception($result['message']);
            }
            break;

        case 'deploy':
            /**
             * Issues gear to guards with stock validation and location tracking.
             */
            $asset_id = $_POST['asset_id'] ?? null;
            $guard_id = $_POST['guard_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 0;
            $expected_return = $_POST['expected_return_date'] ?? null;
            $location = $_POST['deployment_location'] ?? 'UNASSIGNED';
            $notes    = $_POST['dispatch_notes'] ?? '';

            $result = $manager->issue_asset_to_guard($asset_id, $guard_id, $quantity, $expected_return, $location, $notes);
            
            if ($result['status'] === 'success') {
                echo json_encode(['status' => 'success', 'message' => 'Deployment authorized and logged']);
            } else {
                throw new Exception($result['message']);
            }
            break;

        case 'collect':
            /**
             * Processes equipment returns and handles stock restoration.
             */
            $issuance_id   = $_POST['issuance_id'] ?? null;
            $return_status = $_POST['return_status'] ?? 'returned_intact';
            $remarks       = $_POST['remarks'] ?? '';

            $result = $manager->process_asset_return($issuance_id, $return_status, $remarks);
            
            if ($result['status'] === 'success') {
                echo json_encode(['status' => 'success', 'message' => 'Collection finalized. Stock levels updated.']);
            } else {
                throw new Exception($result['message']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'INVALID_GATEWAY_ACTION']);
            break;
    }

} catch (Exception $e) {
    /**
     * EXCEPTION LOCALIZATION:
     * Intercepts logic errors (like negative stock) and database constraints.
     */
    echo json_encode([
        'status' => 'error', 
        'message' => 'Action failed: ' . $e->getMessage()
    ]);
}
?>
