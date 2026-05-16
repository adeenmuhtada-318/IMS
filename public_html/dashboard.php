<?php
/**
 * OPERATIONAL COMMAND DASHBOARD - Professional Interface
 */
session_start();

require_once __DIR__ . '/../includes/functions.php';

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
    <title>FAST Security | Operational Command</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome for Quick Action Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <!-- UNIFIED TACTICAL SIDEBAR (Expandable/Collapsible) -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN COMMAND VIEWPORT -->
        <main class="main-workspace-window">
            
            <header style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h1 style="letter-spacing: 2px; font-weight: 800; color: var(--text-primary);">OPERATIONAL COMMAND CENTER</h1>
                    <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Real-time Readiness and Personnel Metrics</p>
                </div>
                <div class="glass-panel" style="padding: 10px 20px; display: flex; align-items: center; gap: 15px;">
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 0.65rem; color: var(--text-dim);">ACTIVE_OPERATOR:</span>
                        <span style="font-weight: 700; color: var(--text-primary);"><?php echo e(strtoupper($_SESSION['username'])); ?></span>
                    </div>
                    <div style="width: 35px; height: 35px; background: var(--accent-cyan); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #020406; font-weight: 900;">
                        <?php echo e(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- MOBILE TOGGLE -->
            <button class="btn-fast btn-secondary" id="mobile-nav-toggle" style="display: none; margin-bottom: 20px;">
                <i class="fa-solid fa-bars"></i> COMMAND_MENU
            </button>

            <!-- 1. ANALYTICS OVERVIEW ROW (Responsive KPI Grid) -->
            <div class="kpi-grid">
                
                <!-- Card 1: Total Registered Guards (Cyan) -->
                <div class="glass-panel" style="border-left: 4px solid var(--accent-cyan);">
                    <label style="display: block; margin-bottom: 10px; font-size: 0.7rem; letter-spacing: 1px;">Total Registered Guards</label>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 id="kpi-total-guards" style="color: var(--accent-cyan); font-size: 2.2rem; font-weight: 300;">0</h2>
                        <i class="fa-solid fa-users" style="color: var(--accent-cyan); opacity: 0.2; font-size: 1.5rem;"></i>
                    </div>
                </div>

                <!-- Card 2: Pending Police Checks (Orange) -->
                <div class="glass-panel" style="border-left: 4px solid var(--alert-orange);">
                    <label style="display: block; margin-bottom: 10px; font-size: 0.7rem; letter-spacing: 1px;">Pending Police Checks</label>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 id="kpi-pending-checks" style="color: var(--alert-orange); font-size: 2.2rem; font-weight: 300;">0</h2>
                        <i class="fa-solid fa-file-shield" style="color: var(--alert-orange); opacity: 0.2; font-size: 1.5rem;"></i>
                    </div>
                </div>

                <!-- Card 3: Weapons in Vault (Cyan) -->
                <div class="glass-panel" style="border-left: 4px solid var(--accent-cyan);">
                    <label style="display: block; margin-bottom: 10px; font-size: 0.7rem; letter-spacing: 1px;">Weapons in Vault</label>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 id="kpi-vault-weapons" style="color: var(--accent-cyan); font-size: 2.2rem; font-weight: 300;">0</h2>
                        <i class="fa-solid fa-gun" style="color: var(--accent-cyan); opacity: 0.2; font-size: 1.5rem;"></i>
                    </div>
                </div>

                <!-- Card 4: Low Stock Alerts (Orange) -->
                <div class="glass-panel" style="border-left: 4px solid var(--alert-orange);">
                    <label style="display: block; margin-bottom: 10px; font-size: 0.7rem; letter-spacing: 1px;">Low Stock Alerts</label>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 id="kpi-stock-alerts" style="color: var(--alert-orange); font-size: 2.2rem; font-weight: 300;">0</h2>
                        <i class="fa-solid fa-triangle-exclamation" style="color: var(--alert-orange); opacity: 0.2; font-size: 1.5rem;"></i>
                    </div>
                </div>

            </div>

            <!-- 2. RECENT ACTIVITY & QUICK ACTIONS GRID (Responsive Panel Grid) -->
            <div class="panel-grid">
                
                <!-- Left Panel: Newly Joined Personnel -->
                <section class="glass-panel">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 20px; font-size: 1rem; border-bottom: 1px solid var(--border-frost); padding-bottom: 15px;">Newly Joined Personnel</h3>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Guard Number</th>
                                    <th>Home District</th>
                                    <th>Date of Joining</th>
                                </tr>
                            </thead>
                            <tbody id="recent-guards-table">
                                <!-- Data populated via JS -->
                                <tr><td colspan="4" style="text-align: center; color: var(--text-dim);">INITIALIZING_PERSONNEL_AUDIT...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Right Panel: Quick Action Matrix -->
                <section>
                    <h3 style="color: var(--text-primary); margin-bottom: 20px; font-size: 1rem;">Direct Access Portals</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        
                        <!-- Action 1: Registration -->
                        <a href="bharti_form.php" class="glass-panel" style="padding: 25px; text-decoration: none; display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; border: 1px solid var(--border-frost);">
                            <div style="width: 50px; height: 50px; background: rgba(0, 251, 255, 0.05); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--accent-cyan); font-size: 1.2rem;">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <div>
                                <span style="display: block; font-weight: 700; color: var(--text-primary);">Guard Registration Form</span>
                                <span style="font-size: 0.65rem; color: var(--text-dim);">Enroll new security recruits</span>
                            </div>
                        </a>

                        <!-- Action 2: Add Inventory -->
                        <a href="add_inventory.php" class="glass-panel" style="padding: 25px; text-decoration: none; display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; border: 1px solid var(--border-frost);">
                            <div style="width: 50px; height: 50px; background: rgba(0, 251, 255, 0.05); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--accent-cyan); font-size: 1.2rem;">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div>
                                <span style="display: block; font-weight: 700; color: var(--text-primary);">Add New Weapons or Kit</span>
                                <span style="font-size: 0.65rem; color: var(--text-dim);">Procure gear and equipment</span>
                            </div>
                        </a>

                        <!-- Action 3: Assignments -->
                        <a href="deployment.php" class="glass-panel" style="padding: 25px; text-decoration: none; display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; border: 1px solid var(--border-frost);">
                            <div style="width: 50px; height: 50px; background: rgba(0, 251, 255, 0.05); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--accent-cyan); font-size: 1.2rem;">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <div>
                                <span style="display: block; font-weight: 700; color: var(--text-primary);">Guard Duty Assignments</span>
                                <span style="font-size: 0.65rem; color: var(--text-dim);">Dispatch personnel to sites</span>
                            </div>
                        </a>

                    </div>
                </section>

            </div>

        </main>
    </div>

    <!-- THEME TOGGLE -->
    <div id="theme-toggle">🌓</div>

    <!-- SYSTEM SCRIPTS -->
    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard_controller.js"></script>
</body>
</html>
