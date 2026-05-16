<?php
/**
 * VIEW SELLING / ISSUANCE HISTORY - FAST Security Interface
 */
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Deployment History</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- UNIFIED TACTICAL SIDEBAR -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-layout">
        <header class="top-header">
            <div class="profile-widget">
                <div class="profile-name"><?php echo strtoupper($_SESSION['username']); ?></div>
                <div class="profile-avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
            </div>
        </header>

        <main class="container">
            <h1 class="page-title">DEPLOYMENT_&_ISSUANCE_HISTORY</h1>

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
        </main>
    </div>

    <script src="assets/js/history_controller.js"></script>
</body>
</html>
