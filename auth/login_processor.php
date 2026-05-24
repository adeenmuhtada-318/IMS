<?php
/**
 * LOGIN PROCESSOR - Secure Authentication V5.1
 * Features: CSRF, Rate Limiting, Audit Logging, & Session Role Persistence
 */

header('Content-Type: application/json');
session_start();

// Load Core Connection
require_once __DIR__ . '/../includes/connection.php';

// Fallback response for non-POST or empty requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    echo json_encode(['status' => 'error', 'message' => 'INVALID_REQUEST_VECTOR']);
    exit;
}

$username   = trim($_POST['username'] ?? '');
$password   = $_POST['password'] ?? ''; // NOTE: No trim() on password to allow leading/trailing special characters
$csrf_token = $_POST['csrf_token'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'];

try {
    // 1. CSRF VALIDATION
    if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'SECURITY_VIOLATION: Session Expired. Please refresh.']);
        exit;
    }

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'CREDENTIALS_REQUIRED']);
        exit;
    }

    // 2. RATE LIMITING (5 attempts in 15 minutes)
    $stmt_limit = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts 
        WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND is_successful = 0
    ");
    $stmt_limit->execute([$ip_address]);
    if ($stmt_limit->fetchColumn() >= 5) {
        echo json_encode(['status' => 'error', 'message' => 'ACCOUNT_LOCKED: Security cooldown active.']);
        exit;
    }

    // 3. CREDENTIAL VERIFICATION
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, user_role FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // SUCCESS ROUTINE
        $pdo->prepare("INSERT INTO login_attempts (ip_address, username, is_successful) VALUES (?, ?, 1)")->execute([$ip_address, $username]);
        
        // Audit Log Entry
        $pdo->prepare("INSERT INTO audit_log (user_id, action_performed, ip_address, details) VALUES (?, 'LOGIN_SUCCESS', ?, 'Operator Session Established')")
            ->execute([$user['user_id'], $ip_address]);

        // Session Hardening
        session_regenerate_id(true);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $user['user_id'];
        $_SESSION['username']       = $user['username'];
        $_SESSION['user_role']      = $user['user_role']; // PERSIST ROLE FOR RBAC

        echo json_encode(['status' => 'success', 'message' => 'ACCESS_GRANTED']);
    } else {
        // FAILURE ROUTINE
        $pdo->prepare("INSERT INTO login_attempts (ip_address, username, is_successful) VALUES (?, ?, 0)")->execute([$ip_address, $username]);
        
        // Audit Log Entry for Failure (Security Tracking)
        $pdo->prepare("INSERT INTO audit_log (action_performed, ip_address, details) VALUES ('LOGIN_FAILURE', ?, ?)")
            ->execute([$ip_address, "Attempted Username: $username"]);

        echo json_encode(['status' => 'error', 'message' => 'ACCESS_DENIED: Verification Failed.']);
    }

} catch (PDOException $e) {
    // SILENT ERROR LOGGING
    error_log("AUTH_ENGINE_CRITICAL: " . $e->getMessage());
    
    // OPAQUE RESPONSE TO PREVENT INFO LEAKAGE
    echo json_encode(['status' => 'error', 'message' => 'ACCESS_DENIED: System Unavailable.']);
}
