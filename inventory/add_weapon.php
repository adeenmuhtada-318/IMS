<?php
/**
 * WEAPON ONBOARDING CONTROLLER - PRODUCTION V5
 * Securely registers new serialized weaponry into the arsenal.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED_SESSION']);
    exit;
}

require_once '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. DATA INGESTION & VALIDATION
    $serial_number   = trim($_POST['serial_number'] ?? '');
    $model_name      = trim($_POST['model_name'] ?? '');
    $bore_caliber    = trim($_POST['bore_caliber'] ?? '');
    $license_number  = trim($_POST['license_number'] ?? '');
    $license_expiry  = $_POST['license_expiry'] ?? '';
    $condition       = $_POST['condition'] ?? 'Good';

    if (empty($serial_number) || empty($model_name) || empty($license_number) || empty($license_expiry)) {
        header("Location: manage_weapons.php?status=error&message=MISSING_REQUIRED_FIELDS");
        exit;
    }

    try {
        // 2. CHECK FOR DUPLICATE SERIALS
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Individual_Weapons WHERE Serial_Number = ?");
        $stmt_check->execute([$serial_number]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("DUPLICATE_SERIAL_NUMBER_DETECTED");
        }

        // 3. EXECUTE REGISTRATION
        $sql = "INSERT INTO Individual_Weapons 
                (Serial_Number, Model_Name, Bore_Caliber, License_Number, License_Expiry_Date, Status, Condition_On_Onboarding) 
                VALUES (?, ?, ?, ?, ?, 'Available', ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $serial_number,
            $model_name,
            $bore_caliber,
            $license_number,
            $license_expiry,
            $condition
        ]);

        // 4. SUCCESS REDIRECT
        header("Location: manage_weapons.php?status=onboard_success");
        exit;

    } catch (Exception $e) {
        error_log("WEAPON_ONBOARD_FAILURE: " . $e->getMessage());
        header("Location: manage_weapons.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: manage_weapons.php");
    exit;
}
