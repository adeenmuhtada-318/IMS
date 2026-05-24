<?php
/**
 * VIEW SELLING / ISSUANCE HISTORY - FAST Security Interface
 */
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'includes/connection.php';
include 'includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <h1 class="page-title">DEPLOYMENT_&_ISSUANCE_HISTORY</h1>
    </header>

    <div class="glass-panel" style="padding: 20px;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>TRANSACTION_ID</th>
                        <th>ASSET_NAME</th>
                        <th>GUARD_NAME</th>
                        <th>QTY</th>
                        <th>STATUS</th>
                        <th>EXP_RETURN</th>
                        <th>ISSUED_AT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="history-list-body">
                    <!-- Populated via fetch -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/history_controller.js"></script>
<?php include 'includes/footer.php'; ?>
