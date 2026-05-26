<?php
/**
 * FAST SECURITY IMS | COMMAND INTERFACE
 * CORE DASHBOARD VIEW
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
    <title>Dashboard | Fast Security IMS</title>
    <link rel="stylesheet" href="assets/style.css">
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
                    <a href="dashboard.php" class="NavigationAnchor ActiveMenuItem">
                        <span class="MenuIconNode">📊</span>
                        <span class="MenuTextLabel">Dashboard</span>
                    </a>
                </div>
                <div class="NavigationItem">
                    <a href="personnel/onboarding.php" class="NavigationAnchor">
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

        <!-- RIGHT WORKSPACE VIEWPORT -->
        <main id="RightSideViewport">
            
            <div class="ThemeModeToggle" id="ThemeToggleBtn">
                <i class="fa-solid fa-circle-half-stroke"></i>
                <span>Switch Theme</span>
            </div>

            <div class="PortalIdentityBlock" style="margin-bottom: 40px;">
                <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px;">Operational Dashboard</h1>
                <p style="color: var(--TextSecondary); font-size: 1.1rem;">Welcome to the Fast Security tactical command center.</p>
            </div>

            <!-- DASHBOARD WIDGET COMPONENT REPAIR -->
            <section class="HudMetricsRow">
                <div class="DashboardWidgetCard">
                    <span class="TelemetryCounterLabel">Active Guards</span>
                    <span class="TelemetryCounterValue">--</span>
                </div>
                <div class="DashboardWidgetCard">
                    <span class="TelemetryCounterLabel">Active Sites</span>
                    <span class="TelemetryCounterValue">--</span>
                </div>
                <div class="DashboardWidgetCard">
                    <span class="TelemetryCounterLabel">Open Alerts</span>
                    <span class="TelemetryCounterValue">--</span>
                </div>
            </section>
        </main>
    </div>

    <script>
        // SIDEBAR TOGGLE ENGINE
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
    </script>
</body>
</html>
