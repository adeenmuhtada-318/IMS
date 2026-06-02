<?php
/**
 * PERSONNEL REGISTRY - Ultimate Module
 * Location: C:/xampp/htdocs/IMS/modules/staff/manage_guards.php
 */
require_once 'C:/xampp/htdocs/IMS/includes/shared_config.php';
session_start();

// 1. SIDEBAR BUFFER
ob_start();
include ROOT_DIR . '/includes/sidebar.php';
$sidebarBuffer = ob_get_clean();

// 2. FETCH ALL PERSONNEL
try {
    $guards = $pdo->query("SELECT * FROM security_guards ORDER BY enrollment_date DESC")->fetchAll();
} catch (PDOException $e) {
    die("DATABASE_ERROR: " . $e->getMessage());
}

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Personnel Registry | Fast Security</title>
    <style>
        :root { --DeepDenim: #0B0F19; --SlateCard: #151C28; --Orange: #F97316; --Emerald: #10B981; --Crimson: #EF4444; }
        body { margin: 0; background: var(--DeepDenim); color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .MasterLayout { display: flex; width: 100vw; height: 100vh; }
        .Workspace { flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; }
        .TacticalTable { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .TacticalTable td { background: var(--SlateCard); padding: 18px 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .Badge { color: var(--Orange); font-weight: 800; font-family: monospace; }
        .Btn { padding: 6px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-decoration: none; transition: 0.2s; border: 1px solid transparent; }
        .BtnAssign { background: rgba(16,185,129,0.1); color: var(--Emerald); border-color: rgba(16,185,129,0.2); }
        .BtnDismiss { background: rgba(239,68,68,0.1); color: var(--Crimson); border-color: rgba(239,68,68,0.2); }
        .Alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 700; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="MasterLayout">
    <?= $sidebarBuffer ?>
    <main class="Workspace">
        <header style="margin-bottom:30px;">
            <h1 style="margin:0; font-size: 2.2rem; font-weight: 900;">PERSONNEL REGISTRY</h1>
            <p style="color:#94A3B8;">Strategic Field force management and deployment.</p>
        </header>

        <?php if($success): ?><div class="Alert" style="background:rgba(16,185,129,0.1); color:var(--Emerald);">✅ <?= strtoupper($success) ?></div><?php endif; ?>

        <table class="TacticalTable">
            <thead>
                <tr style="text-align:left; color:#94A3B8; font-size:0.7rem; text-transform:uppercase;">
                    <th>ID</th><th>Guard Name</th><th>Duty Shift</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($guards as $g): 
                    $isDel = (int)($g['is_deleted'] ?? 0);
                    $gid = $g['guard_id'];
                ?>
                <tr style="<?= $isDel ? 'opacity:0.5;' : '' ?>">
                    <td><span class="Badge"><?= $g['badge_number'] ?? 'N/A' ?></span></td>
                    <td><strong><?= strtoupper($g['full_name'] ?? 'UNKNOWN') ?></strong></td>
                    <td>
                        <?php if(!$isDel): ?>
                            <?php if(($g['shift_type'] ?? 'None') === 'None'): ?>
                                <a href="process_action.php?action=assign&id=<?= $gid ?>&shift=Morning" class="Btn BtnAssign">MORNING</a>
                                <a href="process_action.php?action=assign&id=<?= $gid ?>&shift=Evening" class="Btn BtnAssign">EVENING</a>
                            <?php else: ?>
                                <span style="font-weight:800; font-size:0.8rem;"><?= strtoupper($g['shift_type']) ?></span>
                                <a href="process_action.php?action=free&id=<?= $gid ?>" style="color:#94A3B8; font-size:0.6rem; margin-left:5px;">[FREE]</a>
                            <?php endif; ?>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td><span style="color:<?= $isDel ? 'var(--Crimson)' : 'var(--Emerald)' ?>; font-weight:900; font-size:0.75rem;">● <?= $isDel ? 'DISMISSED' : strtoupper($g['deployment_status'] ?? 'FREE') ?></span></td>
                    <td>
                        <?php if(!$isDel): ?>
                            <a href="process_action.php?action=dismiss&id=<?= $gid ?>" class="Btn BtnDismiss">DISMISS</a>
                        <?php else: ?>
                            <a href="process_action.php?action=reinstate&id=<?= $gid ?>" class="Btn BtnAssign">REINSTATE</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
