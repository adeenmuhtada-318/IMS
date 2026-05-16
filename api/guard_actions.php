<?php
/**
 * GUARD ACTIONS GATEWAY - Recruitment API
 * Handles the multi-phase deployment of new security personnel.
 */

header('Content-Type: application/json');
session_start();

// 1. Dependency Injections
require_once '../includes/db_config.php';
require_once '../core/GuardManager.php';

// Security Check: Authorized Operator verification
if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'AUTH_REQUIRED: Access Denied']);
    exit;
}

// 2. Intercept Data Payload
/**
 * RATIONALE:
 * We accept JSON input to handle nested multi-dimensional structures 
 * like 'witnesses' and 'certifications' reliably.
 */
$raw_input = file_get_contents('php://input');
$data_payload = json_decode($raw_input, true);

if (!$data_payload) {
    echo json_encode(['status' => 'error', 'message' => 'MALFORMED_DATA_PACKET']);
    exit;
}

// 3. Initialize Management Engine
$guard_manager = new GuardManager($pdo_conn);

try {
    // Extract multi-dimensional components
    $profile       = $data_payload['profile'] ?? [];
    $witnesses     = $data_payload['witnesses'] ?? [];
    $certifications = $data_payload['certifications'] ?? [];

    // Validation: Profile is mandatory for deployment
    if (empty($profile)) {
        throw new Exception("PRIMARY_PROFILE_MISSING");
    }

    // 4. Execute Onboarding Transaction
    $result = $guard_manager->onboard_new_guard($profile, $witnesses, $certifications); // certs still supported but likely empty in current form

    // Handle Kit Handover separately if needed or update GuardManager
    if ($result['status'] === 'success' && isset($data_payload['kit'])) {
        $kit_data = $data_payload['kit'];
        $sql_kit = "INSERT INTO guard_initial_kit 
                    (guard_id, shirt_trousers, cap, belt, boots, jersey) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $pdo_conn->prepare($sql_kit)->execute([
            $result['id'],
            $kit_data['shirt_trousers'],
            $kit_data['cap'],
            $kit_data['belt'],
            $kit_data['boots'],
            $kit_data['jersey']
        ]);
    }

    if ($result['status'] === 'success') {
        echo json_encode([
            'status'  => 'success', 
            'message' => 'Personnel deployment synchronized successfully',
            'id'      => $result['guard_id']
        ]);
    } else {
        // Intercept Logic/Database Failures
        throw new Exception($result['message']);
    }

} catch (Exception $e) {
    /**
     * ERROR TRANSLATION ENGINE:
     * We map cryptic database exceptions to human-readable security protocols.
     */
    $system_trace = $e->getMessage();
    $human_message = "Recruitment failure: Data validation error.";

    // Check for Duplicate Entry (e.g., CNIC already exists in blacklisted or active tables)
    if (strpos($system_trace, 'Duplicate entry') !== false || strpos($system_trace, '23000') !== false) {
        $human_message = "Duplicate CNIC verification checkpoint triggered. Personnel record locked.";
    } elseif (strpos($system_trace, 'PRIMARY_PROFILE_MISSING') !== false) {
        $human_message = "Operational Error: Primary guard data is required for onboarding.";
    }

    echo json_encode([
        'status'  => 'error', 
        'message' => $human_message,
        'debug'   => $system_trace // Useful for technical audit logs
    ]);
}
?>
