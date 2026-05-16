<?php
/**
 * PAYROLL TRACKING DASHBOARD - Financial UI Module
 * Optimized for local accounting staff and high scannability.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Payroll Management</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div class="dashboard-wrapper">
        <!-- UNIFIED TACTICAL SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN PAYROLL VIEW -->
        <main class="main-viewport">
            <header style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="letter-spacing: 3px; color: var(--text-primary);">MONTHLY_PAYROLL_TRACKER</h1>
                    <p style="color: var(--text-dim); font-size: 0.85rem;">Financial disbursement and penalty auditing panel.</p>
                </div>
                <div class="glass-panel" style="padding: 15px 25px; border-color: var(--accent-cyan);">
                    <span style="font-size: 0.7rem; color: var(--text-dim);">PAY_CYCLE:</span>
                    <span style="font-weight: 700; color: var(--accent-cyan); margin-left: 10px;"><?php echo strtoupper(date('F Y')); ?></span>
                </div>
            </header>

            <!-- FINANCIAL METRICS SUMMARY -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
                <div class="glass-panel" style="padding: 25px;">
                    <label>Total Disbursed</label>
                    <h2 id="summary-total-net" style="color: var(--accent-cyan); font-weight: 300;">$0.00</h2>
                </div>
                <div class="glass-panel" style="padding: 25px; border-left: 4px solid var(--alert-orange);">
                    <label>Total Deductions</label>
                    <h2 id="summary-total-fines" style="color: var(--alert-orange); font-weight: 300;">$0.00</h2>
                </div>
                <div class="glass-panel" style="padding: 25px;">
                    <label>Active Guards</label>
                    <h2 id="summary-guard-count" style="color: var(--text-primary); font-weight: 300;">0</h2>
                </div>
                <div class="glass-panel" style="padding: 25px;">
                    <button class="btn-fast btn-primary" style="width: 100%; height: 100%;" onclick="this_generate_payslips()">EXCEL_REPORT_GEN</button>
                </div>
            </div>

            <!-- PAYROLL DATA GRID -->
            <div class="table-container">
                <table id="payroll-table">
                    <thead>
                        <tr>
                            <th>Guard ID</th>
                            <th>Guard Name</th>
                            <th>Monthly Base Salary</th>
                            <th style="color: var(--alert-orange);">Uniform Fees Deducted</th>
                            <th style="color: var(--alert-orange);">ID Card Fines</th>
                            <th style="color: var(--alert-orange);">Duty Negligence Penalties</th>
                            <th style="color: var(--accent-cyan);">Net Payable Cash</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payroll-list-body">
                        <!-- Data populated via controller -->
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 40px; color: var(--text-dim);">INITIALIZING_FINANCIAL_HANDSHAKE...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- THEME TOGGLE -->
    <div id="theme-toggle">🌓</div>

    <!-- SYSTEM SCRIPTS -->
    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/payroll_controller.js"></script>
</body>
</html>
