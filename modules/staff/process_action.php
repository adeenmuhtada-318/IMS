<?php
/**
 * DATA MUTATOR - Unified Action Handler
 * Location: C:/xampp/htdocs/IMS/modules/staff/process_action.php
 */
require_once '../../includes/shared_config.php';
session_start();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    $_SESSION['flash_error'] = "INVALID_ID";
    header("Location: manage_guards.php");
    exit();
}

try {
    switch ($action) {
        case 'assign':
            $shift = $_GET['shift'] ?? 'Morning';
            $stmt = $pdo->prepare("UPDATE security_guards SET shift_type = ?, deployment_status = 'Active' WHERE guard_id = ?");
            $stmt->execute([$shift, $id]);
            $_SESSION['flash_success'] = "SHIFT_ASSIGNED";
            break;

        case 'free':
            $stmt = $pdo->prepare("UPDATE security_guards SET shift_type = 'None', deployment_status = 'Free' WHERE guard_id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = "PERSONNEL_FREED";
            break;

        case 'dismiss':
            $stmt = $pdo->prepare("UPDATE security_guards SET is_deleted = 1, deployment_status = 'Free', shift_type = 'None' WHERE guard_id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = "GUARD_DISMISSED";
            break;

        case 'reinstate':
            $stmt = $pdo->prepare("UPDATE security_guards SET is_deleted = 0 WHERE guard_id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = "GUARD_REINSTATED";
            break;
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "MUTATION_FAILURE: " . $e->getMessage();
}

header("Location: manage_guards.php");
exit();
