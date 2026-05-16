<?php
/**
 * INVENTORY ENGINE - Backend Router
 * Handles CRUD and Logic via AJAX
 */

header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// Get the requested action from the query string or POST
$action_request = $_GET['action'] ?? $_POST['action'] ?? 'read_inventory';

switch ($action_request) {

    case 'read_inventory':
        // logic: Fetch items that are NOT soft-deleted
        $sql_query = "SELECT i.*, c.category_name 
                      FROM inventory i 
                      JOIN categories c ON i.category_id = c.category_id 
                      WHERE i.is_deleted = 0 
                      ORDER BY i.item_id DESC";
        $stmt_handle = $pdo_conn->query($sql_query);
        $inventory_list = $stmt_handle->fetchAll();
        
        echo json_encode($inventory_list);
        break;

    case 'get_stats':
        // logic: Calculate stats for dashboard cards
        $stats_data = [
            'total_items' => $pdo_conn->query("SELECT COUNT(*) FROM inventory WHERE is_deleted = 0")->fetchColumn(),
            'critical_stock' => $pdo_conn->query("SELECT COUNT(*) FROM inventory WHERE stock_quantity <= critical_threshold AND is_deleted = 0")->fetchColumn(),
            'total_categories' => $pdo_conn->query("SELECT COUNT(*) FROM categories")->fetchColumn()
        ];
        echo json_encode($stats_data);
        break;

    case 'get_categories':
        $stmt_handle = $pdo_conn->query("SELECT * FROM categories ORDER BY category_name ASC");
        echo json_encode($stmt_handle->fetchAll());
        break;

    case 'soft_delete':
        // logic: Instead of removing the row, we set is_deleted = 1 to preserve transaction history (Audit requirement)
        $item_id = $_POST['id'] ?? null;
        if ($item_id) {
            $stmt_handle = $pdo_conn->prepare("UPDATE inventory SET is_deleted = 1 WHERE item_id = ?");
            $stmt_handle->execute([$item_id]);
            echo json_encode(['status' => 'success', 'message' => 'Asset decommissioned.']);
        }
        break;

    case 'add_item':
        // logic: Add new weapon/gear to database
        $item_name = $_POST['item_name'] ?? '';
        $cat_id    = $_POST['category_id'] ?? '';
        $qty       = $_POST['quantity'] ?? 0;
        $serial    = $_POST['serial_number'] ?? null;
        $threshold = $_POST['critical_threshold'] ?? 5;

        if (empty($item_name) || empty($cat_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing critical data.']);
            break;
        }

        try {
            $sql_insert = "INSERT INTO inventory (item_name, category_id, stock_quantity, serial_number, critical_threshold) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt_handle = $pdo_conn->prepare($sql_insert);
            $stmt_handle->execute([$item_name, $cat_id, $qty, $serial, $threshold]);
            
            echo json_encode(['status' => 'success', 'message' => 'New asset logged in system.']);
        } catch (\PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Command. Access Denied.']);
        break;
}
?>
