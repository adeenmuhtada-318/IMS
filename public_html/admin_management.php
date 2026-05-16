<?php
/**
 * ADMIN MANAGEMENT - FAST Security Interface
 */
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Admin Management</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px;">
                <h1 style="letter-spacing: 4px; color: var(--text-primary);">ADMINISTRATIVE_CONTROL_CENTER</h1>
                <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Authorized Operator Oversight</p>
            </header>

            <div class="panel-grid">
                
                <section class="glass-panel" style="padding: 0;">
                    <h3 style="padding: 20px; border-bottom: 1px solid var(--border-frost); color: var(--accent-cyan);">OPERATOR_ACCOUNTS</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>USERNAME</th>
                                    <th>ROLE</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="user-list-body">
                                <!-- Populated via fetch -->
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="glass-panel">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">SYSTEM_INTEGRITY_STATUS</h3>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-frost); padding-bottom: 10px;">
                            <span style="color: var(--text-dim); font-size: 0.8rem;">DATABASE_ENGINE</span>
                            <span style="color: #22c55e; font-weight: 700; font-size: 0.75rem;">ONLINE_SYNC</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-frost); padding-bottom: 10px;">
                            <span style="color: var(--text-dim); font-size: 0.8rem;">API_GATEWAY_V5</span>
                            <span style="color: #22c55e; font-weight: 700; font-size: 0.75rem;">ACTIVE_SECURE</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-dim); font-size: 0.8rem;">ENCRYPTION_LAYER</span>
                            <span style="color: #22c55e; font-weight: 700; font-size: 0.75rem;">AES_256_ACTIVE</span>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <script src="assets/js/admin_controller.js"></script>
</body>
</html>
