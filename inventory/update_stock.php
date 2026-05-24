<?php
/**
 * BULK STOCK UPDATE CONTROLLER - PRODUCTION V5
 * Securely manages supply replenishment and stock adjustments.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_SESSION']);
    exit;
}

require_once '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. DATA INGESTION & SANITIZATION
    $item_id  = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
    $add_qty  = filter_var($_POST['add_qty'], FILTER_VALIDATE_INT);
    $notes    = htmlspecialchars($_POST['notes'] ?? 'Standard Restocking');

    if (!$item_id || !$add_qty || $add_qty <= 0) {
        header("Location: manage_bulk.php?status=error&message=INVALID_STOCK_DATA");
        exit;
    }

    try {
        // 2. EXECUTE ATOMIC INCREMENT
        $sql = "UPDATE Bulk_Inventory SET Quantity_On_Hand = Quantity_On_Hand + ? WHERE Item_ID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$add_qty, $item_id]);

        // 3. LOG PROCUREMENT TRANSACTION (Optional but recommended)
        // $sql_log = "INSERT INTO stock_logs (item_id, change_qty, type, notes) VALUES (?, ?, 'IN', ?)";
        // $pdo->prepare($sql_log)->execute([$item_id, $add_qty, $notes]);

        // 4. SUCCESS REDIRECT
        header("Location: manage_bulk.php?status=restock_success");
        exit;

    } catch (PDOException $e) {
        error_log("BULK_STOCK_UPDATE_FAILURE: " . $e->getMessage());
        header("Location: manage_bulk.php?status=error&message=DB_FAILURE");
        exit;
    }
} else {
    header("Location: manage_bulk.php");
    exit;
}
