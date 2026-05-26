<?php
/**
 * HUMAN RESOURCE PORTAL - ONBOARDING HUB
 * Specialized corporate selection matrix for personnel management.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding Hub | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="DarkMode">

    <div id="MainLayoutWrapper">
        
        <!-- SIDEBAR NAVIGATION PANEL -->
        <aside id="LeftSidebarPanel">
            <div class="SidebarBrandingArea">
                <div class="LogoText">Fast Security IMS</div>
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

            <!-- DASHBOARD WIDGET COMPONENT REPAIR -->
            <section class="HudMetricsRow">
                <div class="TelemetryCounterBox" id="FieldForceCard">
                    <span class="TelemetryCounterLabel">Field Force</span>
                    <span class="TelemetryCounterValue" id="MetricFieldForce">0</span>
                </div>
                <div class="TelemetryCounterBox" id="SupplyRiskCard">
                    <span class="TelemetryCounterLabel">Supply Risk</span>
                    <span class="TelemetryCounterValue" id="MetricSupplyRisk">0</span>
                </div>
                <div class="TelemetryCounterBox" id="BlacklistCard">
                    <span class="TelemetryCounterLabel">Blacklist</span>
                    <span class="TelemetryCounterValue" id="MetricBlacklist">0</span>
                </div>
            </section>

            <div class="PortalIdentityBlock" style="margin-bottom: 40px;">
                <h1 class="HubTitleHeading">Human Resource Portal</h1>
                <p class="HubSubText">Personnel onboarding, roster auditing, and performance compliance control center.</p>
            </div>

            <!-- HUB GRID MATRIX -->
            <div class="HubControlMatrixGrid" id="HubControlMatrixGrid">
                
                <a href="registrationForm.php" class="GatewayOptionCard" id="PanelGuardRegistration">
                    <h2 class="CardTitleNode">Guard Registration Terminal</h2>
                    <p class="CardSummaryText">Access the profile enrollment input shield to register and onboard new tactical personnel (Bharti Form).</p>
                </a>

                <a href="viewRoster.php" class="GatewayOptionCard" id="PanelPersonnelRecords">
                    <h2 class="CardTitleNode">Personnel Records Directory</h2>
                    <p class="CardSummaryText">View, audit, and manage the master roster containing records and profiles of already enrolled guards.</p>
                </a>

                <a href="deployments.php" class="GatewayOptionCard" id="PanelDeploymentManagement">
                    <h2 class="CardTitleNode">Site Management & Deployment</h2>
                    <p class="CardSummaryText">Allocate security guard forces, assign active deployment locations, and track field postings.</p>
                </a>

                <a href="attendanceLog.php" class="GatewayOptionCard" id="PanelAttendanceMatrix">
                    <h2 class="CardTitleNode">Attendance Matrix Logging</h2>
                    <p class="CardSummaryText">Track real-time check-in and check-out records essential for active operational logs and automated payroll generation.</p>
                </a>

                <a href="performanceAudit.php" class="GatewayOptionCard" id="PanelPerformanceCompliance">
                    <h2 class="CardTitleNode">Performance Audit Control</h2>
                    <p class="CardSummaryText">Log operational rule violations, evaluate company rule checkboxes (sleeping on duty infractions), and apply fine metrics.</p>
                </a>

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
            } else {
                body.classList.remove('LightMode');
                body.classList.add('DarkMode');
            }
        });

        // LIVE METRIC SYNC ENGINE
        function updateHUD() {
            fetch('../api/api_router.php?action=dashboard_stats')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('MetricFieldForce').innerText = data.field_force || '0';
                    document.getElementById('MetricSupplyRisk').innerText = data.supply_risk || '0';
                    document.getElementById('MetricBlacklist').innerText = data.blacklist || '0';
                })
                .catch(err => console.error('HUD_SYNC_ERROR:', err));
        }
        setInterval(updateHUD, 30000);
        document.addEventListener('DOMContentLoaded', updateHUD);
    </script>
</body>
</html>
