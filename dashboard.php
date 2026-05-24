<?php
/**
 * TACTICAL COMMAND DASHBOARD - HARDENED V5.1
 */
session_start();

// PREVENT UNAUTHORIZED ACCESS & CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// RBAC ENFORCEMENT: Financial Data Shield
if ($_SESSION['user_role'] === 'Operations Supervisor') {
    $show_financials = false;
} else {
    $show_financials = true;
}

require_once 'includes/connection.php';

// RBAC CHECK: Example - Only Admin/Operator allowed on dashboard
$allowed_roles = ['Admin/CEO', 'Accountant', 'Operations Supervisor', 'ADMIN'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: login.php");
    exit();
}

// INITIALIZE KPIS
$kpi = [
    'total_weapons' => 0,
    'active_patrols' => 0,
    'guards_on_duty' => 0,
    'stock_alerts' => 0
];

try {
    // FETCH CONFIGURABLE SETTING FOR ADMIN EXCLUSION
    $exclude_admin = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'dashboard_exclude_admin_duty'")->fetchColumn() ?: 1;

    // EXECUTE STORED PROCEDURE FOR CONSISTENCY
    $stmt = $pdo->prepare("CALL sp_GetDashboardKPIs(?)");
    $stmt->execute([$exclude_admin]);
    $kpi = $stmt->fetch();
    $stmt->closeCursor();

} catch (PDOException $e) {
    error_log("DASHBOARD_ENGINE_FAILURE: " . $e->getMessage());
    // Keep default 0s
}

include 'includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1>Command Center</h1>
            <p class="sub-text">IMS DASHBOARD | ROLE: <?php echo $_SESSION['user_role']; ?></p>
        </div>
        <div class="timestamp">
            <span class="refresh-tag">Last Updated: <?php echo date('H:i:s'); ?></span>
        </div>
    </header>

    <!-- ALERT TICKER PARTIAL -->
    <?php include 'includes/partials/expiry_ticker.php'; ?>

    <div class="kpi-grid">
        <a href="inventory/manage_weapons.php" class="kpi-link">
            <div class="kpi-card status-normal">
                <label>Available Weapons</label>
                <div class="value"><?php echo $kpi['total_weapons']; ?></div>
            </div>
        </a>
        
        <a href="inventory/manage_vehicles.php" class="kpi-link">
            <div class="kpi-card status-active">
                <label>Active Patrols</label>
                <div class="value"><?php echo $kpi['active_patrols']; ?></div>
            </div>
        </a>

        <a href="personnel/manage_guards.php" class="kpi-link">
            <div class="kpi-card status-normal">
                <label>Guards on Duty</label>
                <div class="value"><?php echo $kpi['guards_on_duty']; ?></div>
            </div>
        </a>

        <a href="inventory/manage_bulk.php" class="kpi-link">
            <div class="kpi-card <?php echo $kpi['stock_alerts'] > 0 ? 'status-critical' : 'status-normal'; ?>">
                <label>Stock Alerts</label>
                <div class="value"><?php echo $kpi['stock_alerts']; ?></div>
            </div>
        </a>
    </div>
</div>

<style>
.kpi-link { text-decoration: none; color: inherit; }
.kpi-card { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; }
.kpi-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-color: var(--accent-cyan); }
.refresh-tag { font-size: 0.65rem; color: var(--text-dim); letter-spacing: 1px; }

.alert-ticker-container { background: rgba(248, 81, 73, 0.05); border: 1px solid rgba(248, 81, 73, 0.2); padding: 10px 20px; border-radius: 4px; margin-bottom: 20px; }
.ticker-header { font-size: 0.65rem; font-weight: 900; color: var(--alert-red); margin-bottom: 8px; letter-spacing: 2px; }
.ticker-content { display: flex; flex-wrap: wrap; gap: 10px; }
.alert-node { font-size: 0.75rem; background: rgba(0,0,0,0.2); padding: 5px 12px; border-left: 2px solid var(--alert-red); display: flex; align-items: center; gap: 10px; }
.btn-dismiss { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1rem; line-height: 1; }
.btn-dismiss:hover { color: var(--alert-red); }
</style>

<script>
    const userRole = '<?php echo $_SESSION['user_role']; ?>';
    if (userRole === 'Operations Supervisor') {
        document.querySelectorAll('.financial-col').forEach(el => el.style.display = 'none');
    }
</script>

<?php include 'includes/footer.php'; ?>