<?php
// Detect base path relative to current file location
$current = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
$base    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$depth   = substr_count(str_replace($base, '', $current), '/') - 1;
$prefix  = str_repeat('../', $depth);
if ($depth <= 0) $prefix = '';

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

function nav_active($pages, $current_page, $current_dir = '') {
    if (is_array($pages)) {
        foreach ($pages as $p) {
            if (strpos($p, '/') !== false) {
                $parts = explode('/', $p);
                if ($current_dir === $parts[0] && $current_page === $parts[1]) return 'nav-active';
            } elseif ($current_page === $p) return 'nav-active';
        }
        return '';
    }
    return $current_page === $pages ? 'nav-active' : '';
}
?>
<aside class="tactical-sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-shield-halved" style="color: var(--accent-cyan); font-size: 1.5rem;"></i>
        <span class="brand-name">Fast Security</span>
        <button class="sidebar-toggle" id="sidebar-toggle-btn" onclick="document.getElementById('sidebar').classList.toggle('collapsed')">&#9776;</button>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $prefix ?>dashboard.php" class="nav-item <?= nav_active('dashboard.php', $current_page) ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Main Dashboard</span>
        </a>

        <a href="<?= $prefix ?>personnel/manage_guards.php" class="nav-item <?= nav_active(['manage_guards.php', 'bharti_form.php'], $current_page) ?>">
            <i class="fa-solid fa-user-shield"></i>
            <span>Guard Roster</span>
        </a>

        <a href="<?= $prefix ?>clients/manage_clients.php" class="nav-item <?= nav_active('manage_clients.php', $current_page) ?>">
            <i class="fa-solid fa-building-shield"></i>
            <span>Clients &amp; Sites</span>
        </a>

        <a href="<?= $prefix ?>operations/attendance_grid.php" class="nav-item <?= nav_active('attendance_grid.php', $current_page) ?>">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Attendance Matrix</span>
        </a>

        <a href="<?= $prefix ?>inventory/manage_weapons.php" class="nav-item <?= nav_active(['manage_weapons.php', 'advanced_inventory.php', 'add_inventory.php', 'armory.php'], $current_page) ?>">
            <i class="fa-solid fa-gun"></i>
            <span>Weapon Armory</span>
        </a>

        <a href="<?= $prefix ?>inventory/manage_bulk.php" class="nav-item <?= nav_active(['manage_bulk.php', 'manage_inventory.php', 'category.php'], $current_page) ?>">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Bulk Logistics</span>
        </a>

        <a href="<?= $prefix ?>payroll/payroll_generate.php" class="nav-item <?= nav_active(['payroll_generate.php', 'payroll.php', 'monthly_progress.php', 'daily_compliance.php'], $current_page) ?>">
            <i class="fa-solid fa-wallet"></i>
            <span>Payroll Engine</span>
        </a>

        <a href="<?= $prefix ?>admin/admin_management.php" class="nav-item <?= nav_active('admin_management.php', $current_page) ?>">
            <i class="fa-solid fa-gear"></i>
            <span>System Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <button onclick="document.body.classList.toggle('light-theme')" class="theme-btn">
            <i class="fa-solid fa-circle-half-stroke"></i>
            <span>Light / Dark Mode</span>
        </button>
        <a href="<?= $prefix ?>auth/logout.php" class="nav-item nav-logout">
            <i class="fa-solid fa-power-off"></i>
            <span>End Session</span>
        </a>
    </div>
</aside>