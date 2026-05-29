<?php
/**
 * SITE MANAGEMENT & DEPLOYMENTS - MANAGER TERMINAL
 * Specialized workflow for site enrollment and force allocation.
 * Refactored V5.3: Strict PascalCase Shell & Independent Viewport
 */
session_start();

// Security Boundary: Ensure operator session is valid and authorized
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// --- STAGE 1: MANAGER ADMINISTRATIVE HANDLERS (DATABASE INTEGRITY PRESERVED) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Register New Operational Site
    if ($action === 'register_site') {
        $client_name = trim($_POST['client_name'] ?? '');
        $site_name   = trim($_POST['site_name'] ?? '');
        $strength    = intval($_POST['deployment_strength'] ?? 0);
        $commence_date = $_POST['commence_date'] ?? date('Y-m-d');

        if (!empty($client_name) && !empty($site_name)) {
            try {
                $pdo->beginTransaction();
                // Ensure client exists or create new
                $stmt = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                $stmt->execute([$client_name]);
                $client = $stmt->fetch();
                if ($client) {
                    $client_id = $client['client_id'];
                } else {
                    $ins_client = $pdo->prepare("INSERT INTO clients (client_name) VALUES (?)");
                    $ins_client->execute([$client_name]);
                    $client_id = $pdo->lastInsertId();
                }
                // Register Site Enrollment
                $ins_site = $pdo->prepare("INSERT INTO client_sites (client_id, site_name, required_day_guards, created_at) VALUES (?, ?, ?, ?)");
                $ins_site->execute([$client_id, $site_name, $strength, $commence_date]);
                $pdo->commit();
                $success_msg = "SITE_ENROLLED_SUCCESSFULLY";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error_msg = "ENROLLMENT_FAILED: " . $e->getMessage();
            }
        }
    }

    // Action 2: Force Assignment Matrix (Deploy Guard)
    if ($action === 'assign_force') {
        $guard_id = intval($_POST['guard_id'] ?? 0);
        $site_id  = intval($_POST['site_id'] ?? 0);
        $shift    = $_POST['shift_type'] ?? 'Day';
        
        if ($guard_id && $site_id) {
            try {
                $ins_assign = $pdo->prepare("INSERT INTO attendance (guard_id, site_id, attendance_date, shift_type, attendance_status, change_reason) 
                                            VALUES (?, ?, CURRENT_DATE, ?, 'Present', 'Manager Allocation')
                                            ON DUPLICATE KEY UPDATE site_id = VALUES(site_id), shift_type = VALUES(shift_type)");
                $ins_assign->execute([$guard_id, $site_id, $shift]);
                $success_msg = "FORCE_DEPLOYED_SUCCESSFULLY";
            } catch (Exception $e) {
                $error_msg = "DEPLOYMENT_FAILED: " . $e->getMessage();
            }
        }
    }
}

// --- DATA FETCHING FOR DROPDOWNS ---
// Available Personnel (Active Duty)
$guards = $pdo->query("SELECT guard_id, full_name, guard_no FROM guards_personnel WHERE is_deleted = 0 AND duty_status = 'Active Duty' ORDER BY full_name ASC")->fetchAll();
// Registered Sites
$sites = $pdo->query("SELECT s.*, c.client_name FROM client_sites s JOIN clients c ON s.client_id = c.client_id WHERE s.is_deleted = 0 ORDER BY s.site_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Matrix | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="DarkMode">

    <!-- MASTER SHELL CONTAINER -->
    <div id="MainLayoutWrapper">
        
        <!-- SIDEBAR NAVIGATION PANEL -->
        <aside id="LeftSidebarPanel">
            <div class="SidebarBrandingArea">
                <div class="BrandingTitle">FAST SECURITY IMS</div>
                <button id="SidebarToggleAction">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <nav class="NavigationLinkList">
                <div class="NavigationItem">
                    <a href="../dashboard.php" class="NavigationAnchor">
                        <span class="MenuIconNode">📊</span>
                        <span class="MenuTextLabel">Dashboard</span>
                    </a>
                </div>
                <div class="NavigationItem">
                    <a href="onboarding.php" class="NavigationAnchor ActiveMenuItem">
                        <span class="MenuIconNode">👥</span>
                        <span class="MenuTextLabel">Human Resource Portal</span>
                    </a>
                </div>
            </nav>

            <div class="UserStatusComponent">
                <div class="OperatorAccountHeader">Operator Account</div>
                <span class="SystemActiveFlag">SYSTEM ACTIVE</span>
            </div>
        </aside>

        <!-- MAIN WORKSPACE VIEWPORT -->
        <main id="RightSideViewport">
            
            <div class="ThemeModeToggle" id="ThemeToggleBtn">
                <i class="fa-solid fa-circle-half-stroke"></i>
                <span>Switch Theme</span>
            </div>

            <div class="PortalIdentityBlock">
                <h1 class="HubTitleHeading">Site Management & Deployment</h1>
                <p class="HubSubText">Manager administrative terminal for site onboarding and force allocation.</p>
            </div>

            <!-- SUCCESS/ERROR NOTIFICATIONS -->
            <?php if($success_msg): ?>
                <div style="padding: 16px; background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #22c55e; border-radius: 12px; margin-bottom: 24px; font-weight: 700;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 10px;"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div style="padding: 16px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; border-radius: 12px; margin-bottom: 24px; font-weight: 700;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 10px;"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- STAGE 1: MANAGER ADMINISTRATIVE PANEL -->
            <div class="FormGrid" style="grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: flex-start;">
                
                <!-- NEW SITE ENROLLMENT CONTROL -->
                <section class="FormClusterCard">
                    <h3 class="SectionTitle">Register New Operational Site</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="register_site">
                        <div class="InputGroup">
                            <label class="InputLabel">Operational Site Name</label>
                            <input type="text" name="site_name" class="ModernInput" placeholder="e.g. Allied Bank - Gulberg Branch" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Client Corporate Title</label>
                            <input type="text" name="client_name" class="ModernInput" placeholder="e.g. Allied Bank Limited" required>
                        </div>
                        <div class="FormGrid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="InputGroup">
                                <label class="InputLabel">Commencement Date</label>
                                <input type="date" name="commence_date" class="ModernInput" required>
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Guard Deployment Strength</label>
                                <input type="number" name="deployment_strength" class="ModernInput" value="1" min="1" required>
                            </div>
                        </div>
                        <button type="submit" class="PrimaryActionButton" style="width: 100%; margin-top: 12px;">AUTHORIZE_NEW_SITE</button>
                    </form>
                </section>

                <!-- ACTIVE DEPLOYMENT FORCE ALLOCATION -->
                <section class="FormClusterCard">
                    <h3 class="SectionTitle">Force Assignment Matrix</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign_force">
                        <div class="InputGroup">
                            <label class="InputLabel">Select Available Personnel</label>
                            <select name="guard_id" class="ModernInput" required>
                                <option value="">--- CHOOSE_GUARD ---</option>
                                <?php foreach($guards as $g): ?>
                                    <option value="<?php echo $g['guard_id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> [<?php echo $g['guard_no']; ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Select Operational Site</label>
                            <select name="site_id" class="ModernInput" required>
                                <option value="">--- SELECT_SITE ---</option>
                                <?php foreach($sites as $s): ?>
                                    <option value="<?php echo $s['site_id']; ?>"><?php echo htmlspecialchars($s['site_name']); ?> (<?php echo $s['client_name']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Operational Shift</label>
                            <select name="shift_type" class="ModernInput" required>
                                <option value="Day">Day Shift (08:00 - 20:00)</option>
                                <option value="Night">Night Shift (20:00 - 08:00)</option>
                            </select>
                        </div>
                        <button type="submit" class="PrimaryActionButton" style="width: 100%; margin-top: 12px;">DEPLOY_OPERATOR</button>
                    </form>
                </section>
            </div>

            <!-- STAGE 2: DYNAMIC LIVE SITES MONITORING TABLE -->
            <div class="PortalIdentityBlock" style="margin-top: 48px; margin-bottom: 24px;">
                <h3 class="SectionTitle" style="font-size: 1.5rem;">Registered Site Roster</h3>
            </div>

            <div class="DataMatrixContainer">
                <table class="PremiumDataTable">
                    <thead>
                        <tr>
                            <th>Site ID</th>
                            <th>Corporate Client</th>
                            <th>Operational Location</th>
                            <th>Guard Strength</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sites)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 60px; color: var(--TextDim);">NO_ACTIVE_SITES_DETECTED_IN_SYSTEM</td></tr>
                        <?php else: ?>
                            <?php foreach ($sites as $s): ?>
                                <tr class="PremiumDataRow">
                                    <td style="font-family: 'Courier New', monospace; font-weight: 700; color: var(--VoltCyan);">SITE-<?php echo str_pad($s['site_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td style="font-weight: 700;"><?php echo htmlspecialchars($s['client_name']); ?></td>
                                    <td><?php echo htmlspecialchars($s['site_name']); ?></td>
                                    <td>
                                        <span style="color: var(--VoltCyan); font-weight: 800;"><?php echo $s['required_day_guards']; ?></span> Operators Required
                                    </td>
                                    <td>
                                        <span style="padding: 6px 12px; background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px;">OPERATIONAL</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script>
        // SIDEBAR TOGGLE MECHANISM
        const toggleBtn = document.getElementById('SidebarToggleAction');
        const mainWrapper = document.getElementById('MainLayoutWrapper');

        toggleBtn.addEventListener('click', () => {
            mainWrapper.classList.toggle('SidebarCollapsed');
        });

        // THEME ENGINE
        const themeBtn = document.getElementById('ThemeToggleBtn');
        const body = document.body;

        themeBtn.addEventListener('click', () => {
            if (body.classList.contains('DarkMode')) {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
                localStorage.setItem('ThemePreference', 'LightMode');
            } else {
                body.classList.remove('LightMode');
                body.classList.add('DarkMode');
                localStorage.setItem('ThemePreference', 'DarkMode');
            }
        });

        // INITIALIZE THEME
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('ThemePreference');
            if (savedTheme === 'LightMode') {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
            }
        });
    </script>
</body>
</html>
