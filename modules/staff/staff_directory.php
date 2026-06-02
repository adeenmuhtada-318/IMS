<?php
/**
 * STAFF DIRECTORY - Optimized Module
 * Location: C:/xampp/htdocs/IMS/modules/staff/staff_directory.php
 */
require_once '../../includes/shared_config.php';
session_start();

// 1. CAPTURE SIDEBAR
ob_start();
include ROOT_DIR . '/includes/sidebar.php';
$sidebarBuffer = ob_get_clean();

// 2. FETCH ACTIVE PERSONNEL
try {
    $sql = "SELECT guard_id, badge_number, full_name, deployment_status, shift_type 
            FROM security_guards 
            WHERE is_deleted = 0 
            ORDER BY full_name ASC";
    $stmt = $pdo->query($sql);
    $staff = $stmt->fetchAll();
} catch (PDOException $e) {
    die("DATABASE_ERROR: " . $e->getMessage());
}

$successMsg = $_SESSION['flash_success'] ?? '';
$errorMsg   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Directory | Fast Security</title>
    <style>
        :root { --DeepDenim: #0B0F19; --SlateCard: #151C28; --VibrantOrange: #F97316; --Emerald: #10B981; --Crimson: #EF4444; }
        body { margin: 0; background: var(--DeepDenim); color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .MasterViewport { display: flex; width: 100vw; height: 100vh; }
        .Workspace { flex: 1; padding: 40px; overflow-y: auto; }
        
        .Header { margin-bottom: 40px; }
        .Header h1 { font-size: 2.2rem; font-weight: 900; letter-spacing: -1px; margin: 0; }
        
        .TacticalTable { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .TacticalTable th { text-align: left; padding: 12px 20px; font-size: 0.7rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 2px; }
        .TacticalTable td { background: var(--SlateCard); padding: 18px 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .TacticalTable td:first-child { border-radius: 10px 0 0 10px; }
        .TacticalTable td:last-child { border-radius: 0 10px 10px 0; }
        
        .Badge { color: var(--VibrantOrange); font-weight: 800; font-family: monospace; }
        .BtnDismiss { color: var(--Crimson); font-weight: 800; font-size: 0.75rem; text-decoration: none; border: 1px solid var(--Crimson); padding: 5px 12px; border-radius: 4px; transition: 0.2s; }
        .BtnDismiss:hover { background: var(--Crimson); color: #fff; }
        
        .Alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; font-weight: 700; font-size: 0.9rem; }
        .AlertSuccess { background: rgba(16, 185, 129, 0.1); color: var(--Emerald); border: 1px solid rgba(16, 185, 129, 0.2); }
    </style>
</head>
<body>

<div class="MasterViewport">
    <?= $sidebarBuffer ?>

    <main class="Workspace">
        <header class="Header">
            <h1>STAFF DIRECTORY</h1>
            <p style="color:#94A3B8; margin-top:5px;">Real-time personnel registry and status monitoring.</p>
        </header>

        <?php if($successMsg): ?>
            <div class="Alert AlertSuccess">✅ <?= strtoupper($successMsg) ?></div>
        <?php endif; ?>

        <table class="TacticalTable">
            <thead>
                <tr>
                    <th>Badge ID</th>
                    <th>Full Name</th>
                    <th>Duty Shift</th>
                    <th>Deployment</th>
                    <th style="text-align:right;">Action Center</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($staff as $s): ?>
                <tr>
                    <td><span class="Badge"><?= htmlspecialchars($s['badge_number'] ?? 'N/A') ?></span></td>
                    <td style="font-weight:700;"><?= strtoupper(htmlspecialchars($s['full_name'] ?? 'UNKNOWN')) ?></td>
                    <td><?= strtoupper(htmlspecialchars($s['shift_type'] ?? 'NONE')) ?></td>
                    <td><span style="color:var(--Emerald); font-weight:800; font-size:0.75rem;">● <?= strtoupper(htmlspecialchars($s['deployment_status'] ?? 'FREE')) ?></span></td>
                    <td style="text-align:right;">
                        <a href="process_action.php?action=dismiss&id=<?= $s['guard_id'] ?>" class="BtnDismiss" onclick="return confirm('Are you sure you want to dismiss this personnel?')">DISMISS</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>
