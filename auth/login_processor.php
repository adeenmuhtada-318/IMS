<?php
/**
 * LOGIN PROCESSOR - Secure Authentication
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'CREDENTIALS_REQUIRED']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, user_role FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id']        = $user['user_id'];
            $_SESSION['username']       = $user['username'];
            $_SESSION['user_role']      = $user['user_role'];

            echo json_encode(['status' => 'success', 'message' => 'ACCESS_GRANTED']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'INVALID_CREDENTIALS']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'SYSTEM_BUSY']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'METHOD_NOT_ALLOWED']);
}
