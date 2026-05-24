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

require_once __DIR__ . '/../includes/connection.php';

$json_input = file_get_contents('php://input');
$json_data  = json_decode($json_input, true);

$action  = $_GET['action'] ?? $_POST['action'] ?? $json_data['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

switch ($action) {
    case 'get_users':
        if ($_SESSION['user_role'] !== 'Admin/CEO' && $_SESSION['user_role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'ACCESS_DENIED']);
            exit;
        }
        echo json_encode($pdo->query("SELECT user_id, username, user_role, is_active, last_login FROM users")->fetchAll());
        break;

    case 'create_user':
        if ($_SESSION['user_role'] !== 'Admin/CEO' && $_SESSION['user_role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'ACCESS_DENIED']);
            exit;
        }
        $username = trim($json_data['username'] ?? '');
        $password = $json_data['password'] ?? '';
        $role     = $json_data['role'] ?? 'Accountant';

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'CREDENTIALS_REQUIRED']);
            exit;
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, user_role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
            echo json_encode(['status' => 'success', 'message' => 'USER_CREATED']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'USER_EXISTS_OR_ERROR']);
        }
        break;

    case 'dashboard_stats':
        $stats = [
            'total_guards'    => $pdo->query("SELECT COUNT(*) FROM guards_personnel WHERE is_deleted = 0")->fetchColumn(),
            'field_force'     => $pdo->query("SELECT COUNT(*) FROM guards_personnel WHERE duty_status = 'Active Duty' AND is_deleted = 0")->fetchColumn(),
            'pending_checks'  => 0,
            'vault_weapons'   => $pdo->query("SELECT COUNT(*) FROM Individual_Weapons WHERE is_deleted = 0")->fetchColumn() ?? 0,
            'stock_alerts'    => $pdo->query("SELECT COUNT(*) FROM Bulk_Inventory WHERE Quantity_On_Hand <= reorder_level AND is_deleted = 0")->fetchColumn(),
            'supply_risk'     => $pdo->query("SELECT COUNT(*) FROM Bulk_Inventory WHERE Quantity_On_Hand <= reorder_level AND is_deleted = 0")->fetchColumn(),
            'blacklist'       => $pdo->query("SELECT COUNT(*) FROM guard_blacklist")->fetchColumn() ?: 0
        ];
        echo json_encode($stats);
        break;

    case 'get_recent_guards':
        echo json_encode($pdo->query("SELECT full_name, guard_no, home_district, joining_date FROM guards_personnel WHERE is_deleted = 0 ORDER BY joining_date DESC LIMIT 10")->fetchAll());
        break;

    case 'get_inventory':
        echo json_encode($pdo->query("SELECT * FROM Individual_Weapons WHERE is_deleted = 0")->fetchAll());
        break;

    case 'add_asset':
        $master = $json_data['master_record'] ?? [];
        $units  = $json_data['unit_manifest'] ?? [];
        
        if (empty($units)) {
            echo json_encode(['status' => 'error', 'message' => 'EMPTY_MANIFEST']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO Individual_Weapons (weapon_serial, weapon_type, weapon_model, expiry_date) VALUES (?, ?, ?, ?)");
            foreach ($units as $unit) {
                $stmt->execute([
                    $unit['serial_number'],
                    $master['caliber'] ?? 'N/A',
                    $master['model_name'] ?? 'Unknown',
                    !empty($unit['license_expiry']) ? $unit['license_expiry'] : null
                ]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'ASSET_BATCH_REGISTERED']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'onboard_guard':
        $profile = $json_data['profile'] ?? [];
        $witnesses = $json_data['witnesses'] ?? [];
        $kit = $json_data['kit'] ?? [];

        if (empty($profile['cnic']) || empty($profile['full_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'MANDATORY_FIELDS_MISSING']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            
            // 1. Insert Profile into guards_personnel
            $sql = "INSERT INTO guards_personnel (
                guard_no, full_name, parentage, cnic, dob, guard_phone, caste, education, religion, 
                home_district, permanent_address, temporary_address, heir_name, heir_phone, 
                heir_relation, heir_address, prev_experience_ref, gov_relative_details, 
                is_ex_army, army_enroll_date, army_discharge_date, 
                witness1_name, witness1_phone, witness1_cnic, witness1_address, 
                witness2_name, witness2_phone, witness2_cnic, witness2_address, 
                joining_date, base_salary_per_day
            ) VALUES (
                :guard_no, :full_name, :parentage, :cnic, :dob, :phone, :caste, :education, :religion, 
                :district, :p_addr, :t_addr, :h_name, :h_phone, 
                :h_rel, :h_addr, :prev_exp, :gov_rel, 
                :ex_army, :army_en, :army_dis, 
                :w1_name, :w1_phone, :w1_cnic, :w1_addr, 
                :w2_name, :w2_phone, :w2_cnic, :w2_addr, 
                :join_date, :salary
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':guard_no' => $profile['guard_no'],
                ':full_name' => $profile['full_name'],
                ':parentage' => $profile['father_name'] ?? 'N/A',
                ':cnic' => $profile['cnic'],
                ':dob' => $profile['dob'],
                ':phone' => $profile['phone_number'],
                ':caste' => $profile['caste'],
                ':education' => $profile['education'],
                ':religion' => $profile['religion'],
                ':district' => $profile['district'],
                ':p_addr' => $profile['permanent_address'],
                ':t_addr' => $profile['temporary_address'],
                ':h_name' => $profile['heir_name'] ?? $witnesses[0]['name'] ?? 'N/A', // Mapping to schema
                ':h_phone' => $profile['next_of_kin_mobile'] ?? $witnesses[0]['phone'] ?? 'N/A',
                ':h_rel' => 'Relative',
                ':h_addr' => $profile['next_of_kin_name_address'] ?? $witnesses[0]['address'] ?? 'N/A',
                ':prev_exp' => $profile['previous_experience_ref'],
                ':gov_rel' => $profile['govt_relative_name'] . " " . $profile['govt_relative_department'],
                ':ex_army' => $profile['is_ex_army'],
                ':army_en' => !empty($profile['army_joining_date']) ? $profile['army_joining_date'] : null,
                ':army_dis' => !empty($profile['army_discharge_date']) ? $profile['army_discharge_date'] : null,
                ':w1_name' => $witnesses[0]['name'] ?? 'N/A',
                ':w1_phone' => $witnesses[0]['phone'] ?? 'N/A',
                ':w1_cnic' => 'N/A',
                ':w1_addr' => $witnesses[0]['address'] ?? 'N/A',
                ':w2_name' => $witnesses[1]['name'] ?? 'N/A',
                ':w2_phone' => $witnesses[1]['phone'] ?? 'N/A',
                ':w2_cnic' => 'N/A',
                ':w2_addr' => $witnesses[1]['address'] ?? 'N/A',
                ':join_date' => $profile['joining_date'],
                ':salary' => $profile['base_salary']
            ]);
            
            $guard_id = $pdo->lastInsertId();

            // 2. Insert Kit if exists
            if (!empty($kit)) {
                $sql_kit = "INSERT INTO guard_initial_kit (guard_id, shirt_trousers, cap, belt, boots, jersey) VALUES (?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql_kit)->execute([
                    $guard_id,
                    $kit['shirt_trousers'] ?? 0,
                    $kit['cap'] ?? 0,
                    $kit['belt'] ?? 0,
                    $kit['boots'] ?? 0,
                    $kit['jersey'] ?? 0
                ]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'PERSONNEL_ONBOARDED']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'release_payroll':
        echo json_encode(['status' => 'success', 'message' => 'PAYROLL_RELEASED']);
        break;

    case 'get_issuances':
        echo json_encode([]);
        break;

    case 'get_categories':
        echo json_encode($pdo->query("SELECT * FROM categories")->fetchAll());
        break;

    case 'get_audit_logs':
        echo json_encode($pdo->query("SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT 50")->fetchAll());
        break;

    case 'deploy':
        echo json_encode(['status' => 'success', 'message' => 'DEPLOYMENT_AUTHORIZED']);
        break;

    case 'log_compliance':
        $guard_id = $json_data['guard_id'] ?? 0;
        $log_date = $json_data['log_date'] ?? date('Y-m-d');
        $status = $json_data['attendance_status'] ?? 'Present';
        
        try {
            $sql = "INSERT INTO attendance (guard_id, site_id, attendance_date, shift_type, attendance_status, change_reason) 
                    VALUES (:gid, :sid, :date, :shift, :status, :reason)
                    ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':gid' => $guard_id,
                ':sid' => $json_data['site_id'] ?? 0,
                ':date' => $log_date,
                ':shift' => $json_data['shift_type'] ?? 'Day',
                ':status' => $status,
                ':reason' => 'Compliance Log'
            ]);
            echo json_encode(['status' => 'success', 'message' => 'COMPLIANCE_LOGGED']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'get_monthly_progress':
        $guard_id = $_GET['guard_id'] ?? $json_data['guard_id'] ?? 0;
        $month = $_GET['month'] ?? $json_data['month'] ?? date('m');
        $year = $_GET['year'] ?? $json_data['year'] ?? date('Y');
        
        try {
            // Check if stored procedure exists or fallback to query
            $stmt = $pdo->prepare("SELECT COUNT(*) as presents FROM attendance WHERE guard_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ? AND attendance_status = 'Present'");
            $stmt->execute([$guard_id, $month, $year]);
            $stats = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'log_progress':
        echo json_encode(['status' => 'success', 'message' => 'PROGRESS_LOGGED']);
        break;

    case 'save_attendance':
        // Integration with api_save_attendance.php logic
        $guard_id         = (int)($json_data['guard_id'] ?? 0);
        $site_id          = (int)($json_data['site_id'] ?? 0);
        $attendance_date  = $json_data['attendance_date'] ?? '';
        $shift_type       = $json_data['shift_type'] ?? 'Day';
        $status           = $json_data['attendance_status'] ?? '';
        $reliever_id      = isset($json_data['reliever_assigned_to']) ? (int)$json_data['reliever_assigned_to'] : null;
        $reason           = trim($json_data['change_reason'] ?? '');

        if (!$guard_id || !$site_id || !$attendance_date) {
            echo json_encode(['status' => 'error', 'message' => 'MISSING_MANDATORY_FIELDS']);
            exit;
        }

        try {
            if (empty($status)) {
                $stmt = $pdo->prepare("DELETE FROM attendance WHERE guard_id = ? AND attendance_date = ? AND shift_type = ?");
                $stmt->execute([$guard_id, $attendance_date, $shift_type]);
                echo json_encode(['status' => 'success', 'message' => 'ATTENDANCE_CLEARED']);
                exit;
            }

            $stmt_check = $pdo->prepare("SELECT site_id FROM attendance WHERE guard_id = ? AND attendance_date = ? AND shift_type = ?");
            $stmt_check->execute([$guard_id, $attendance_date, $shift_type]);
            $existing = $stmt_check->fetch();

            if ($existing && $existing['site_id'] != $site_id) {
                echo json_encode(['status' => 'error', 'message' => 'DOUBLE_BOOKING: Guard already assigned elsewhere this shift.']);
                exit;
            }

            $sql = "INSERT INTO attendance (guard_id, site_id, attendance_date, shift_type, attendance_status, reliever_assigned_to, change_reason) 
                    VALUES (:gid, :sid, :date, :shift, :status, :reliever, :reason)
                    ON DUPLICATE KEY UPDATE 
                    attendance_status = VALUES(attendance_status),
                    reliever_assigned_to = VALUES(reliever_assigned_to),
                    change_reason = VALUES(change_reason),
                    site_id = VALUES(site_id)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':gid' => $guard_id, ':sid' => $site_id, ':date' => $attendance_date, ':shift' => $shift_type, ':status' => $status, ':reliever' => $reliever_id ?: null, ':reason' => $reason]);
            echo json_encode(['status' => 'success', 'message' => 'ATTENDANCE_SYNCHRONIZED']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'get_relievers':
        $date  = $_GET['date'] ?? '';
        $shift = $_GET['shift'] ?? 'Day';
        try {
            $sql = "SELECT guard_id, full_name, guard_no FROM guards_personnel WHERE is_deleted = 0 AND duty_status = 'Active Duty' AND guard_id NOT IN (SELECT guard_id FROM attendance WHERE attendance_date = ? AND shift_type = ?) ORDER BY full_name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, $shift]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'toggle_duty':
        $guard_id = (int)($json_data['guard_id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT duty_status FROM guards_personnel WHERE guard_id = ? FOR UPDATE");
            $stmt->execute([$guard_id]);
            $current = $stmt->fetchColumn();
            $next = ($current === 'Active Duty') ? 'Off Duty' : 'Active Duty';
            $pdo->prepare("UPDATE guards_personnel SET duty_status = ? WHERE guard_id = ?")->execute([$next, $guard_id]);
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'add_client':
        $client_name = trim($json_data['client_name'] ?? '');
        $site_name   = trim($json_data['site_name'] ?? '');
        if (empty($client_name) || empty($site_name)) {
            echo json_encode(['status' => 'error', 'message' => 'CRITICAL: Client and Site name are required.']);
            exit;
        }
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");
            $stmt->execute([$client_name]);
            $client = $stmt->fetch();
            if ($client) {
                $client_id = $client['client_id'];
            } else {
                $ins_client = $pdo->prepare("INSERT INTO clients (client_name) VALUES (?)");
                $ins_client->execute([$client_name]);
                $client_id = $pdo->lastInsertId();
            }
            $stmt_site = $pdo->prepare("SELECT site_id FROM client_sites WHERE client_id = ? AND site_name = ?");
            $stmt_site->execute([$client_id, $site_name]);
            if ($stmt_site->fetch()) {
                throw new Exception("DUPLICATE_SITE: This site is already registered for this client.");
            }
            $ins_site = $pdo->prepare("INSERT INTO client_sites (client_id, site_name) VALUES (?, ?)");
            $ins_site->execute([$client_id, $site_name]);
            $site_id = $pdo->lastInsertId();
            $pdo->commit();
            echo json_encode(['status' => 'success', 'client_id' => $client_id, 'site_id' => $site_id, 'client_name' => $client_name, 'site_name' => $site_name]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'dismiss_alert':
        $item_type = $json_data['item_type'] ?? '';
        $item_name = $json_data['item_name'] ?? '';
        $expiry    = $json_data['expiry'] ?? '';

        try {
            $sql = "INSERT IGNORE INTO dismissed_alerts (user_id, alert_key) VALUES (?, ?)";
            $alert_key = $item_type . "_" . $item_name . "_" . $expiry;
            $pdo->prepare($sql)->execute([$user_id, $alert_key]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'UNKNOWN_COMMAND: ' . $action]);
        break;
}
