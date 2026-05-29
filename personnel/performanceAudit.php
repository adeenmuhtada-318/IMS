<?php
/**
 * PERFORMANCE AUDIT CONTROL TERMINAL - TACTICAL IMS
 * Division: Human Resource Portal | Performance & Compliance
 * Refactored V5.5: Modern Navy UI Sync & Independent Viewport Framework
 */
session_start();

// Security Boundary: Ensure operator session is valid and authorized
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// --- STAGE 1: DATA ACQUISITION (ZERO-DESTRUCTION BACKEND) ---
try {
    // Fetch Active Guards for Selector
    $guards = $pdo->query("SELECT guard_id, full_name, guard_no FROM guards_personnel WHERE is_deleted = 0 AND duty_status = 'Active Duty' ORDER BY full_name ASC")->fetchAll();
    
    // Fetch Historical Audits (Mocking history logic based on payroll/compliance schemas)
    $history_sql = "SELECT p.*, g.full_name, g.guard_no 
                    FROM payroll p 
                    JOIN guards_personnel g ON p.guard_id = g.guard_id 
                    ORDER BY p.created_at DESC LIMIT 15";
    $audit_history = $pdo->query($history_sql)->fetchAll();

} catch (PDOException $e) {
    die("SYSTEM_FAILURE: Unable to synchronize audit data. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Audit Control | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* INLINE OVERRIDES FOR AUDIT SPECIFIC COMPONENTS */
        .AuditContainer {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        .CheckboxEntryRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid var(--BorderDeep);
            transition: var(--TransitionStandard);
        }

        .CheckboxEntryRow:hover { background: var(--VoltGlow); }
    </style>
</head>
<body class="DarkMode">

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
                <h1 class="HubTitleHeading">Performance Audit Control</h1>
                <p class="HubSubText">Log operational rule violations, evaluate company rule checkboxes, and apply standard automated fine metrics.</p>
            </div>

            <!-- PERFORMANCE AUDIT GRID -->
            <form id="PerformanceAuditForm" class="AuditContainer">
                
                <!-- LEFT COLUMN: RULE EVALUATION -->
                <div class="AuditInputZone">
                    
                    <!-- GUARD SELECTOR COMPONENT -->
                    <div class="FormClusterCard">
                        <h3 class="SectionTitle">Target Personnel Sync</h3>
                        <div class="InputGroup">
                            <label class="InputLabel">Select Guard for Audit</label>
                            <select name="guard_id" class="ModernInput" required>
                                <option value="">--- SEARCH_ACTIVE_ROSTER ---</option>
                                <?php foreach($guards as $g): ?>
                                    <option value="<?php echo $g['guard_id']; ?>"><?php echo htmlspecialchars($g['full_name']); ?> [<?php echo $g['guard_no']; ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- VIOLATION DIRECTORY -->
                    <div class="FormGrid" style="grid-template-columns: 1fr 1fr; gap: 24px;">
                        
                        <div class="FormClusterCard" style="margin-bottom: 0;">
                            <h3 class="SectionTitle">Uniform Breaches</h3>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Incomplete Tactical Kit</label>
                                <input type="checkbox" name="violation_uniform" class="CustomCheckboxNode">
                            </div>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Unpolished Footwear</label>
                                <input type="checkbox" name="violation_boots" class="CustomCheckboxNode">
                            </div>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Grooming Violation</label>
                                <input type="checkbox" name="violation_grooming" class="CustomCheckboxNode">
                            </div>
                        </div>

                        <div class="FormClusterCard" style="margin-bottom: 0;">
                            <h3 class="SectionTitle">Duty Infractions</h3>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Late Arrival Logged</label>
                                <input type="checkbox" name="violation_late" class="CustomCheckboxNode">
                            </div>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Sleeping on Duty</label>
                                <input type="checkbox" name="violation_sleeping" class="CustomCheckboxNode">
                            </div>
                            <div class="CheckboxEntryRow">
                                <label class="InputLabel">Unauthorized Absence</label>
                                <input type="checkbox" name="violation_absence" class="CustomCheckboxNode">
                            </div>
                        </div>

                    </div>

                </div>

                <!-- RIGHT COLUMN: FINANCIAL PENALTIES -->
                <div class="FineMetricZone">
                    <div class="FormClusterCard" style="height: 100%; box-sizing: border-box;">
                        <h3 class="SectionTitle">Fine Metrics (PKR)</h3>
                        <div class="InputGroup">
                            <label class="InputLabel">LOST ID CARD FINE</label>
                            <input type="number" name="lost_id_card_fines" class="ModernInput" placeholder="0.00" step="0.01">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">SHIFT MISCONDUCT FINE</label>
                            <input type="number" name="shift_misconduct_fines" class="ModernInput" placeholder="0.00" step="0.01">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">CUSTOM CLIENT PENALTY</label>
                            <input type="number" name="custom_client_penalties" class="ModernInput" placeholder="0.00" step="0.01">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">AUDIT REMARKS</label>
                            <textarea name="audit_remarks" class="ModernInput" style="height: 100px; resize: none;" placeholder="Provide technical justification..."></textarea>
                        </div>
                        <button type="submit" class="PrimaryActionButton" style="width: 100%; margin-top: 24px;">Commit Performance Audit</button>
                    </div>
                </div>

            </form>

            <!-- HISTORICAL DATA AUDIT TRAIL -->
            <div class="PortalIdentityBlock" style="margin-top: 48px; margin-bottom: 24px;">
                <h2 class="HubTitleHeading" style="font-size: 1.5rem;">Historical Violation Audit</h2>
            </div>

            <div class="DataMatrixContainer">
                <table class="AttendanceLogsTable">
                    <thead>
                        <tr>
                            <th>Audit Date</th>
                            <th>Guard Operator</th>
                            <th>Guard No</th>
                            <th>Lost ID Fine</th>
                            <th>Sleeping Fine</th>
                            <th>Other Penalty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($audit_history)): ?>
                            <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--TextDim);">NO_AUDIT_HISTORY_FOUND</td></tr>
                        <?php else: ?>
                            <?php foreach ($audit_history as $audit): ?>
                                <tr class="RosterRow" onclick="window.location.href='view_guard.php?id=<?php echo $audit['guard_id']; ?>'">
                                    <td style="color: var(--VoltCyan); font-weight: 700;"><?php echo date('d-M-Y', strtotime($audit['created_at'])); ?></td>
                                    <td style="font-weight: 700;"><?php echo htmlspecialchars($audit['full_name']); ?></td>
                                    <td style="font-family: monospace;"><?php echo $audit['guard_no']; ?></td>
                                    <td><?php echo number_format($audit['id_loss_fine'], 2); ?></td>
                                    <td><?php echo number_format($audit['sleeping_fine'], 2); ?></td>
                                    <td><?php echo number_format($audit['disciplinary_deduction'], 2); ?></td>
                                    <td>
                                        <span class="StatusBadge StatusPresent">LOGGED</span>
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
