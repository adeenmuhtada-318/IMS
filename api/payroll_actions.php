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
if ($action === 'get_data') {
    echo json_encode($guards->get_payroll_data(date('F Y')));
}
