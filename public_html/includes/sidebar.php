<?php
/**
 * TACTICAL NAVIGATION DRAWER - Professional Component
 * Features: Expand/Collapse states, Human-friendly labels, Icon-only mode.
 */

// Determine current active page for visual highlighting
$active_script = basename($_SERVER['PHP_SELF']);

/**
 * Utility function to append active CSS class
 */
function check_active($page_name, $current_script) {
    return ($page_name === $current_script) ? 'active active-nav-node' : '';
}
?>

<!-- Link FontAwesome 6 for tactical icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<aside id="tactical-sidebar" class="sidebar-drawer glass-panel">
    
    <!-- 1. BRANDING & LOGO CONTAINER -->
    <div id="agency-logo-container" class="sidebar-header">
        <div class="logo-emblem">
            <!-- LOGO SVG INSERTION POINT -->
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <span class="brand-text">FAST SECURITY</span>
    </div>

    <!-- 2. DYNAMIC COLLAPSE BUTTON -->
    <button class="sidebar-toggle-btn" onclick="this_toggle_sidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- 3. SIMPLIFIED NAVIGATION NODES -->
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="nav-item <?php echo check_active('dashboard.php', $active_script); ?>" title="Main Dashboard">
                    <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                    <span class="nav-label">Main Dashboard</span>
                </a>
            </li>
            <li>
                <a href="bharti_form.php" class="nav-item <?php echo check_active('bharti_form.php', $active_script); ?>" title="Guard Registration Form">
                    <span class="nav-icon"><i class="fa-solid fa-file-invoice"></i></span>
                    <span class="nav-label">Guard Registration Form</span>
                </a>
            </li>
            <li>
                <a href="guard_performance.php" class="nav-item <?php echo check_active('guard_performance.php', $active_script); ?>" title="Performance Review">
                    <span class="nav-icon"><i class="fa-solid fa-star-half-stroke"></i></span>
                    <span class="nav-label">Performance Review</span>
                </a>
            </li>
            <li>
                <a href="add_inventory.php" class="nav-item <?php echo check_active('add_inventory.php', $active_script); ?>" title="Add New Weapons or Kit">
                    <span class="nav-icon"><i class="fa-solid fa-plus"></i></span>
                    <span class="nav-label">Add New Weapons or Kit</span>
                </a>
            </li>
            <li>
                <a href="manage_inventory.php" class="nav-item <?php echo check_active('manage_inventory.php', $active_script); ?>" title="Manage Current Stock">
                    <span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                    <span class="nav-label">Manage Current Stock</span>
                </a>
            </li>
            <li>
                <a href="deployment.php" class="nav-item <?php echo check_active('deployment.php', $active_script); ?>" title="Guard Duty Assignments">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <span class="nav-label">Guard Duty Assignments</span>
                </a>
            </li>
            <li>
                <a href="issuance_history.php" class="nav-item <?php echo check_active('issuance_history.php', $active_script); ?>" title="Gear History Log">
                    <span class="nav-icon"><i class="fa-solid fa-clock-history"></i></span>
                    <span class="nav-label">Gear History Log</span>
                </a>
            </li>
            <li>
                <a href="payroll.php" class="nav-item <?php echo check_active('payroll.php', $active_script); ?>" title="Payroll Tracking">
                    <span class="nav-icon"><i class="fa-solid fa-wallet"></i></span>
                    <span class="nav-label">Payroll Tracking</span>
                </a>
            </li>
            <li>
                <a href="admin_management.php" class="nav-item <?php echo check_active('admin_management.php', $active_script); ?>" title="System Settings">
                    <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
                    <span class="nav-label">System Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- 4. MODE TOGGLE & FOOTER -->
    <div class="sidebar-footer">
        <button class="mode-toggle-btn" onclick="this_toggle_system_theme()">
            <span class="nav-icon"><i class="fa-solid fa-circle-half-stroke"></i></span>
            <span class="nav-label">☀️ Light / 🌙 Dark Mode</span>
        </button>
        
        <a href="../auth/logout.php" class="nav-item logout-link" title="Terminate Session">
            <span class="nav-icon"><i class="fa-solid fa-power-off"></i></span>
            <span class="nav-label">End Session</span>
        </a>
    </div>
</aside>

<script src="assets/js/main.js"></script>

<style>
/* SIDEBAR ARCHITECTURE */
#tactical-sidebar {
    width: 280px;
    height: 100vh;
    display: flex;
    flex-direction: column;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1000;
}

/* COLLAPSED STATE */
#tactical-sidebar.collapsed {
    width: 80px;
}

#tactical-sidebar.collapsed .brand-text,
#tactical-sidebar.collapsed .nav-label {
    display: none;
}

#tactical-sidebar.collapsed .sidebar-header {
    padding: 20px 0;
    justify-content: center;
}

#tactical-sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 15px 0;
}

#tactical-sidebar.collapsed .nav-icon {
    margin: 0;
}

/* HEADER & BRANDING */
.sidebar-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 30px 25px;
    transition: all 0.3s;
}

.logo-emblem {
    width: 35px;
    height: 35px;
    background: var(--accent-cyan);
    border-radius: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #020406;
    font-size: 1.2rem;
    box-shadow: 0 0 15px var(--accent-glow);
}

.brand-text {
    font-weight: 800;
    letter-spacing: 2px;
    color: var(--accent-cyan);
    font-size: 1.1rem;
    white-space: nowrap;
}

/* TOGGLE BUTTON */
.sidebar-toggle-btn {
    position: absolute;
    top: 35px;
    right: -15px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--accent-cyan);
    border: none;
    color: #020406;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    z-index: 10;
}

/* NAVIGATION */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 14px 25px;
    color: var(--text-dim);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.25s ease;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
}

.active-nav-node {
    background: linear-gradient(90deg, var(--accent-glow), transparent) !important;
    color: var(--accent-cyan) !important;
    border-left: 3px solid var(--accent-cyan) !important;
    font-weight: 600;
}

.nav-icon {
    width: 30px;
    font-size: 1.1rem;
    display: flex;
    justify-content: center;
    margin-right: 15px;
}

/* FOOTER */
.sidebar-footer {
    padding: 20px 0;
    border-top: 1px solid var(--border-frost);
}

.mode-toggle-btn {
    width: 100%;
    background: transparent;
    border: none;
    padding: 15px 25px;
    color: var(--text-dim);
    cursor: pointer;
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s;
}

.mode-toggle-btn:hover {
    color: var(--accent-cyan);
}

.logout-link {
    color: var(--alert-orange);
}

.logout-link:hover {
    background: rgba(249, 115, 22, 0.05);
}

/* BODY GRID ADJUSTMENT */
body.sidebar-minimized .dashboard-wrapper {
    grid-template-columns: 80px 1fr;
}
</style>
