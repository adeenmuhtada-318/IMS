<?php
/**
 * GUARD PROFILE VIEW - PRODUCTION V5.2
 * Features: Full Record Audit & RBAC Data Protection
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';
include '../includes/header.php';

$guard_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
$is_admin = ($_SESSION['user_role'] === 'ADMIN');

try {
    $stmt = $pdo->prepare("SELECT * FROM guards_personnel WHERE guard_id = ? AND is_deleted = 0");
    $stmt->execute([$guard_id]);
    $g = $stmt->fetch();

    if (!$g) die("GUARD_RECORD_NOT_FOUND");

    // Fetch Assigned Assets
    $stmt_assets = $pdo->prepare("SELECT * FROM inventory_assignments WHERE guard_id = ? AND assignment_status = 'Deployed'");
    $stmt_assets->execute([$guard_id]);
    $assigned_assets = $stmt_assets->fetchAll();

} catch (PDOException $e) {
    die("SYSTEM_ERROR: Unable to retrieve profile.");
}
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1>Operator Profile: <?php echo htmlspecialchars($g['full_name']); ?></h1>
            <p class="sub-text">IMS Phase-V | Guard ID: <?php echo htmlspecialchars($g['guard_no']); ?></p>
        </div>
        <div class="actions">
            <a href="manage_guards.php" class="btn-tactical">Back to Roster</a>
        </div>
    </header>

    <div class="panel-grid" style="grid-template-columns: 1fr 2fr;">
        <!-- BIOMETRIC & PERSONAL DATA -->
        <section class="glass-panel profile-sidebar">
            <div class="profile-header">
                <div class="avatar-placeholder"><i class="fa-solid fa-user-shield"></i></div>
                <h3><?php echo ucwords(strtolower($g['duty_status'])); ?></h3>
            </div>
            <div class="data-group">
                <label>Designation</label>
                <span><?php echo htmlspecialchars($g['designation']); ?></span>
            </div>
            <div class="data-group">
                <label>CNIC Number</label>
                <span><?php echo $is_admin ? htmlspecialchars($g['cnic']) : 'MASKED_FOR_SECURITY'; ?></span>
            </div>
            <div class="data-group">
                <label>Phone</label>
                <span><?php echo htmlspecialchars($g['guard_phone']); ?></span>
            </div>
            <div class="data-group">
                <label>Joining Date</label>
                <span><?php echo date('d.M.Y', strtotime($g['joining_date'])); ?></span>
            </div>
        </section>

        <!-- ASSIGNED ASSETS & DUTY LOGS -->
        <section class="glass-panel">
            <div class="panel-header">Active Deployed Assets</div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Asset Type</th><th>Asset ID</th><th>Deployed Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($assigned_assets)): ?>
                            <tr><td colspan="3" style="text-align: center; color: var(--text-dim);">No assets currently assigned</td></tr>
                        <?php else: ?>
                            <?php foreach ($assigned_assets as $a): ?>
                                <tr>
                                    <td><?php echo $a['asset_category']; ?></td>
                                    <td class="monospaced"><?php echo $a['asset_id']; ?></td>
                                    <td><?php echo date('d.M.Y', strtotime($a['assignment_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<style>
.profile-sidebar { padding: 30px; text-align: center; }
.profile-header { margin-bottom: 30px; }
.avatar-placeholder { font-size: 4rem; color: var(--accent-cyan); margin-bottom: 15px; }
.data-group { text-align: left; margin-bottom: 20px; border-bottom: 1px solid var(--border-dim); padding-bottom: 10px; }
.data-group label { display: block; font-size: 0.6rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
.data-group span { font-weight: 600; font-size: 0.9rem; }
</style>

<?php include '../includes/footer.php'; ?>
