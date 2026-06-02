<?php
/**
 * DISBURSE ACTION
 * Final insert after confirmation from payroll_process.php
 * Kept as fallback direct-link handler (GET method)
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/IMS/includes/shared_config.php';

if (isset($_GET['guard_id']) && isset($_GET['amount'])) {
    $guard_id = (int)$_GET['guard_id'];
    $amount   = (float)$_GET['amount'];
    $month    = date('Y-m');

    try {
        $stmt = $pdo->prepare("INSERT INTO salary_records (guard_id, net_payable, month_year) VALUES (?, ?, ?)");
        $stmt->execute([$guard_id, $amount, $month]);

        header("Location: payroll_dashboard.php?msg=success");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid Request - Missing Parameters");
}
?>