<?php
/**
 * TACTICAL ASSET DEPLOYMENT ENGINE
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Tactical Dispatch</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px;">
                <h1 style="letter-spacing: 4px; color: var(--text-primary);">TACTICAL_ASSET_DEPLOYMENT_ENGINE</h1>
                <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Strategic Dispatch Unit : Active Duty Flow</p>
            </header>

            <div class="panel-grid" style="grid-template-columns: 450px 1fr;">
                
                <section class="glass-panel">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px; letter-spacing: 2px;">DISPATCH_CONFIGURATION</h3>
                    <form id="deployment-form">
                        <div class="input-group" style="margin-bottom: 20px;">
                            <label>SELECT GUARD PERSONNEL</label>
                            <select name="guard_id" id="guard-select" class="glass-input" required>
                                <option value="">SCANNING_PERSONNEL_RECORDS...</option>
                            </select>
                        </div>

                        <div class="input-group" style="margin-bottom: 20px;">
                            <label>SELECT ASSET TO DISPATCH</label>
                            <select name="asset_id" id="asset-select" class="glass-input" required>
                                <option value="">SCANNING_ARMORY_STOCK...</option>
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="input-group">
                                <label>DEPLOYMENT QUANTITY</label>
                                <input type="number" name="quantity" class="glass-input" value="1" min="1" required>
                            </div>
                            <div class="input-group">
                                <label>EXPECTED RETURN CLOCK</label>
                                <input type="date" name="expected_return_date" class="glass-input" required>
                            </div>
                        </div>

                        <div class="input-group" style="margin-bottom: 20px;">
                            <label>DEPLOYMENT_LOCATION / SITE</label>
                            <input type="text" name="deployment_location" class="glass-input" placeholder="e.g. Corporate Bank HQ" required>
                        </div>

                        <div class="input-group" style="margin-bottom: 20px;">
                            <label>OPERATOR DISPATCH NOTES</label>
                            <textarea name="dispatch_notes" class="glass-input" style="height: 80px; resize: none;" placeholder="Enter shift guidelines..."></textarea>
                        </div>

                        <button type="submit" class="btn-fast btn-primary" style="width: 100%; margin-top: 10px;">
                            AUTHORIZE_TACTICAL_DISPATCH
                        </button>
                    </form>
                </section>

                <section class="glass-panel">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px; letter-spacing: 2px;">CURRENT_ON_DUTY_ASSIGNMENTS</h3>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>G_ID</th>
                                    <th>GUARD_NAME</th>
                                    <th>ASSET_NAME</th>
                                    <th>LOCATION</th>
                                    <th>DEADLINE</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="active-duty-list">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/deployment_controller.js"></script>
</body>
</html>
