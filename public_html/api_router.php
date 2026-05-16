<?php
/**
 * ARMORY API ROUTER - Enterprise Dispatcher
 */
session_start();
header('Content-Type: application/json');

// Authorization Check
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_ACCESS']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../core/InventoryManager.php';
require_once __DIR__ . '/../core/GuardManager.php';

$json_input = file_get_contents('php://input');
$json_data  = json_decode($json_input, true);

$action  = $_GET['action'] ?? $_POST['action'] ?? $json_data['action'] ?? '';
$user_id = $_SESSION['user_id'];
$inventory = new InventoryManager($pdo);
$guards    = new GuardManager($pdo);

switch ($action) {
    case 'dashboard_stats':
        $stats = [
            'total_guards'    => $pdo->query("SELECT COUNT(*) FROM guards WHERE is_deleted = 0")->fetchColumn(),
            'pending_checks'  => $pdo->query("SELECT COUNT(*) FROM guards WHERE is_red_flagged = 1 AND is_deleted = 0")->fetchColumn(), 
            'vault_weapons'   => $pdo->query("SELECT SUM(current_stock) FROM assets WHERE category_type = 'operational' AND is_deleted = 0")->fetchColumn() ?? 0,
            'stock_alerts'    => $pdo->query("SELECT COUNT(*) FROM assets WHERE current_stock <= min_threshold AND is_deleted = 0")->fetchColumn()
        ];
        echo json_encode($stats);
        break;

    case 'get_recent_guards':
        echo json_encode($pdo->query("SELECT full_name, guard_no, district, joining_date FROM guards WHERE is_deleted = 0 ORDER BY joining_date DESC LIMIT 10")->fetchAll());
        break;

    case 'get_inventory':
        echo json_encode($inventory->get_inventory_list());
        break;

    case 'add_asset':
        $data = [
            'asset_name'            => $_POST['asset_name'] ?? '',
            'sku'                   => $_POST['sku'] ?? 'SKU-' . time(),
            'category_id'           => $_POST['category_id'] ?? 1,
            'category_type'         => $_POST['category_type'] ?? 'operational',
            'tracking_type'         => $_POST['tracking_type'] ?? 'bulk',
            'current_stock'         => $_POST['quantity'] ?? $_POST['current_stock'] ?? 0,
            'min_threshold'         => $_POST['min_threshold'] ?? 5,
            'purchase_cost'         => $_POST['purchase_cost'] ?? 0,
            'serial_number'         => $_POST['serial_number'] ?? null,
            'bore_caliber'          => $_POST['bore_caliber'] ?? null,
            'license_number'        => $_POST['license_number'] ?? null,
            'last_calibration_date' => $_POST['last_calibration_date'] ?? null,
            'item_size'             => $_POST['item_size'] ?? null,
            'material_type'         => $_POST['material_type'] ?? null,
            'registration_number'   => $_POST['registration_number'] ?? null,
            'chassis_number'        => $_POST['chassis_number'] ?? null,
            'next_service_date'     => $_POST['next_service_date'] ?? null,
            'asset_type_detail'     => $_POST['asset_type_detail'] ?? null,
            'location_room'         => $_POST['location_room'] ?? null,
            'depreciation_rate_annual' => $_POST['depreciation_rate_annual'] ?? 0
        ];
        echo json_encode($inventory->register_new_asset($data, $user_id));
        break;

    case 'release_payroll':
        $guard_id = $_POST['guard_id'] ?? null;
        $month = date('F Y');
        echo json_encode($guards->release_payroll($guard_id, $month));
        break;

    case 'collect':
        $issuance_id   = $_POST['issuance_id'] ?? null;
        $return_status = $_POST['return_status'] ?? 'returned_intact';
        $remarks       = $_POST['remarks'] ?? '';
        echo json_encode($inventory->process_return($issuance_id, $return_status, $remarks, $user_id));
        break;

    case 'decommission':
        $asset_id = $_POST['item_id'] ?? $_POST['asset_id'] ?? null;
        echo json_encode(['status' => $inventory->decommission_asset($asset_id) ? 'success' : 'error']);
        break;

    case 'get_users':
        echo json_encode($pdo->query("SELECT user_id, username, user_role, is_active FROM users")->fetchAll());
        break;

    case 'onboard_guard':
        echo json_encode($guards->onboard_guard($json_data));
        break;

    case 'get_guards':
        echo json_encode($guards->get_active_guards());
        break;

    case 'deploy':
        $asset_id = $_POST['asset_id'] ?? null;
        $guard_id = $_POST['guard_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 0;
        $expected_return = $_POST['expected_return_date'] ?? null;
        $location = $_POST['deployment_location'] ?? 'UNASSIGNED';
        $notes    = $_POST['dispatch_notes'] ?? '';
        echo json_encode($inventory->issue_asset($asset_id, $guard_id, $quantity, $user_id, $expected_return, $location, $notes));
        break;

    case 'get_issuances':
        $sql = "SELECT ai.*, a.asset_name, g.full_name as guard_name 
                FROM asset_issuances ai 
                JOIN assets a ON ai.asset_id = a.asset_id 
                JOIN guards g ON ai.guard_id = g.guard_id 
                ORDER BY ai.issued_at DESC";
        echo json_encode($pdo->query($sql)->fetchAll());
        break;

    case 'get_categories':
        echo json_encode($pdo->query("SELECT * FROM categories")->fetchAll());
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'UNKNOWN_COMMAND: ' . $action]);
        break;
}
