<?php
/**
 * ATOMIC ASSIGNMENT ROUTER - PRODUCTION V5
 * Handles secure asset checkouts with full transactional integrity.
 */
session_start();

// Authorization Guard
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_SESSION']);
    exit;
}

require_once '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. DATA INGESTION & SANITIZATION
    $guard_id        = filter_var($_POST['guard_id'], FILTER_VALIDATE_INT);
    $asset_category  = $_POST['asset_category'] ?? ''; // ENUM: 'Weapon', 'Gear', 'Device', 'Vehicle', 'Bulk_Item'
    $asset_id        = $_POST['target_asset_id'] ?? '';
    $quantity        = filter_var($_POST['bulk_quantity_issued'] ?? 1, FILTER_VALIDATE_INT);
    $condition       = htmlspecialchars($_POST['condition_on_issue'] ?? 'Optimal');
    $admin_id        = $_SESSION['user_id']; // Overriding with session ID for security audit

    // Validation Check
    if (!$guard_id || !$asset_category || !$asset_id) {
        echo json_encode(['status' => 'error', 'message' => 'INVALID_INPUT_PARAMETERS']);
        exit;
    }

    try {
        // 2. INITIALIZE ATOMIC TRANSACTION
        $pdo->beginTransaction();

        // 3. CATEGORY-SPECIFIC BUSINESS LOGIC
        switch ($asset_category) {
            
            case 'Weapon':
                // Check availability
                $stmt = $pdo->prepare("SELECT Status FROM Individual_Weapons WHERE Weapon_ID = ? FOR UPDATE");
                $stmt->execute([$asset_id]);
                $status = $stmt->fetchColumn();

                if ($status !== 'Available') {
                    throw new Exception("WEAPON_NOT_AVAILABLE_FOR_ASSIGNMENT");
                }

                // Update Weapon Status
                $upd = $pdo->prepare("UPDATE Individual_Weapons SET Status = 'Assigned' WHERE Weapon_ID = ?");
                $upd->execute([$asset_id]);
                break;

            case 'Bulk_Item':
                // Check stock levels
                $stmt = $pdo->prepare("SELECT Quantity_On_Hand FROM Bulk_Inventory WHERE Item_ID = ? FOR UPDATE");
                $stmt->execute([$asset_id]);
                $current_stock = $stmt->fetchColumn();

                if ($current_stock < $quantity) {
                    throw new Exception("INSUFFICIENT_BULK_STOCK_LEVELS");
                }

                // Decrement Stock
                $upd = $pdo->prepare("UPDATE Bulk_Inventory SET Quantity_On_Hand = Quantity_On_Hand - ? WHERE Item_ID = ?");
                $upd->execute([$quantity, $asset_id]);
                break;

            case 'Gear':
            case 'Device':
            case 'Vehicle':
                // For serialized gear/devices/vehicles, we log the assignment without complex state triggers for now
                // but can be extended here if 'Status' columns exist in those tables.
                break;

            default:
                throw new Exception("INVALID_ASSET_CATEGORY_ROUTING");
        }

        // 4. INSERT ASSIGNMENT LOG
        $sql_log = "INSERT INTO Inventory_Assignments 
                    (Guard_ID, Asset_ID, Asset_Category, Quantity_Issued, Condition_On_Issue, Attending_Admin_ID, Assignment_Status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Deployed')";
        
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([$guard_id, $asset_id, $asset_category, $quantity, $condition, $admin_id]);

        // 5. COMMIT TRANSACTION
        $pdo->commit();

        // Success Redirect
        header("Location: ../dashboard.php?status=assignment_success");
        exit;

    } catch (Exception $e) {
        // 6. ROLLBACK ON FAILURE
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("ASSIGNMENT_ROUTER_FAILURE: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'INVALID_REQUEST_METHOD']);
    exit;
}
