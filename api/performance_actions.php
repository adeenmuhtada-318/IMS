<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'UNAUTHORIZED']);
    exit;
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../core/GuardManager.php';
$guards = new GuardManager($pdo);
$action = $_GET['action'] ?? '';
if ($action === 'save') {
    echo json_encode($guards->save_performance_audit($_POST));
} elseif ($action === 'history') {
    echo json_encode($guards->get_audit_history());
}
