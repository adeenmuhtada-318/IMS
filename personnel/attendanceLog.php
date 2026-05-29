<?php
/**
 * ATTENDANCE MATRIX LOGGING - TACTICAL IMS
 * Division: Human Resource Portal
 * Refactored V5.4: Production-Ready Color Sync & Global Theme Harmonization
 */
session_start();

// Security Boundary: Ensure operator session is valid and authorized
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// --- STAGE 1: BACKEND EXECUTION ENGINE (100% UNTOUCHED) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guard_id         = (int)($_POST['guard_id'] ?? 0);
    $site_id          = (int)($_POST['site_id'] ?? 0);
    $attendance_date  = $_POST['attendance_date'] ?? date('Y-m-d');
    $shift_type       = $_POST['shift_type'] ?? 'Day';
    $status           = $_POST['attendance_status'] ?? 'Present';
    $reason           = trim($_POST['change_reason'] ?? 'Manual Log');

    if ($guard_id && $site_id && $attendance_date) {
        try {
            $sql = "INSERT INTO attendance (guard_id, site_id, attendance_date, shift_type, attendance_status, change_reason) 
                    VALUES (:gid, :sid, :date, :shift, :status, :reason)
                    ON DUPLICATE KEY UPDATE 
                    attendance_status = VALUES(attendance_status),
                    change_reason = VALUES(change_reason),
                    site_id = VALUES(site_id),
                    shift_type = VALUES(shift_type)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':gid' => $guard_id, 
                ':sid' => $site_id, 
                ':date' => $attendance_date, 
                ':shift' => $shift_type, 
                ':status' => $status, 
                ':reason' => $reason
            ]);
            $success_msg = "ATTENDANCE_LOG_SYNCHRONIZED";
        } catch (PDOException $e) {
            $error_msg = "LOG_FAILED: " . $e->getMessage();
        }
    } else {
        $error_msg = "MANDATORY_FIELDS_MISSING";
    }
}

// --- DATA FETCHING (PRESERVING BACKEND QUERIES) ---
$guards = $pdo->query("SELECT guard_id, full_name, guard_no FROM guards_personnel WHERE is_deleted = 0 AND duty_status = 'Active Duty' ORDER BY full_name ASC")->fetchAll();
$sites = $pdo->query("SELECT s.*, c.client_name FROM client_sites s JOIN clients c ON s.client_id = c.client_id WHERE s.is_deleted = 0 ORDER BY s.site_name ASC")->fetchAll();

$history_sql = "SELECT a.*, g.full_name, g.guard_no, s.site_name 
                FROM attendance a 
                JOIN guards_personnel g ON a.guard_id = g.guard_id 
                JOIN client_sites s ON a.site_id = s.site_id 
                ORDER BY a.attendance_date DESC, a.created_at DESC 
                LIMIT 50";
$history = $pdo->query($history_sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Matrix Logging | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .StatusBadge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .StatusPresent { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .StatusAbsent { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .StatusReliever { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    </style>
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
                <h1 class="HubTitleHeading">Attendance Matrix Logging</h1>
                <p class="HubSubText">Track real-time clock-in and check-out records essential for active operational logs.</p>
            </div>

            <!-- DYNAMIC NOTIFICATIONS -->
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

            <!-- ATTENDANCE INPUT FORM -->
            <section class="FormClusterCard">
                <h3 class="SectionTitle">Register Operational Attendance</h3>
                <form method="POST">
                    <div class="FormGrid">
                        <div class="InputGroup">
                            <label class="InputLabel">Select Tactical Personnel</label>
                            <select name="guard_id" class="ModernInput" required>
                                <option value="">--- CHOOSE_GUARD ---</option>
                                <?php foreach($guards as $g): ?>
                                    <option value="<?php echo $g['guard_id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> [<?php echo $g['guard_no']; ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Operational Site</label>
                            <select name="site_id" class="ModernInput" required>
                                <option value="">--- SELECT_SITE ---</option>
                                <?php foreach($sites as $s): ?>
                                    <option value="<?php echo $s['site_id']; ?>"><?php echo htmlspecialchars($s['site_name']); ?> (<?php echo $s['client_name']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Log Date</label>
                            <input type="date" name="attendance_date" class="ModernInput" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Attendance State</label>
                            <select name="attendance_status" class="ModernInput" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Reliever">Reliever</option>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Operational Shift</label>
                            <select name="shift_type" class="ModernInput" required>
                                <option value="Day">Day Shift</option>
                                <option value="Night">Night Shift</option>
                            </select>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Action Authority</label>
                            <button type="submit" class="PrimaryActionButton" style="width: 100%;">AUTHORIZE_LOG_ENTRY</button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- HISTORICAL LOGS DIRECTORY -->
            <div class="PortalIdentityBlock" style="margin-top: 48px; margin-bottom: 24px;">
                <h3 class="SectionTitle" style="font-size: 1.5rem;">Historical Attendance Logs</h3>
            </div>

            <div class="DataMatrixContainer">
                <table class="AttendanceLogsTable">
                    <thead>
                        <tr>
                            <th>Log Date</th>
                            <th>Guard Operator</th>
                            <th>Deployed Site</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--TextDim);">NO_ATTENDANCE_RECORDS_DETECTED</td></tr>
                        <?php else: ?>
                            <?php foreach ($history as $log): ?>
                                <tr class="RosterRow" onclick="window.location.href='view_guard.php?id=<?php echo $log['guard_id']; ?>'">
                                    <td style="font-family: 'Courier New', monospace; font-weight: 700; color: var(--VoltCyan);"><?php echo date('d-M-Y', strtotime($log['attendance_date'])); ?></td>
                                    <td>
                                        <div style="font-weight: 700;"><?php echo htmlspecialchars($log['full_name']); ?></div>
                                        <div style="font-size: 0.7rem; color: var(--TextDim);"><?php echo htmlspecialchars($log['guard_no']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['site_name']); ?></td>
                                    <td><?php echo $log['shift_type']; ?></td>
                                    <td>
                                        <?php 
                                            $badge_class = 'StatusPresent';
                                            if($log['attendance_status'] === 'Absent') $badge_class = 'StatusAbsent';
                                            if($log['attendance_status'] === 'Reliever') $badge_class = 'StatusReliever';
                                        ?>
                                        <span class="StatusBadge <?php echo $badge_class; ?>">
                                            <?php echo $log['attendance_status']; ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: var(--TextDim);"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></td>
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
