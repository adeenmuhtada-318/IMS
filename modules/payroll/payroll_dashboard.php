<?php
/**
 * PAYROLL DASHBOARD - Auto-Audit Engine
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/IMS/includes/shared_config.php';
session_start();

// 1. SIDEBAR
ob_start();
include ROOT_DIR . '/includes/sidebar.php';
$sidebarBuffer = ob_get_clean();

// Flash message
$flashMsg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $flashMsg = 'Salary disbursed successfully.';
}

// 2. FETCH DATA WITH AUTO-AUDIT
try {
    $month = date('Y-m');
    $sql = "SELECT g.*,
                   SUM(CASE WHEN al.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absents,
                   SUM(CASE WHEN al.attendance_status = 'Late'   THEN 1 ELSE 0 END) as lates
            FROM security_guards g
            LEFT JOIN attendance_logs al
                ON g.badge_number = al.badge_number
               AND DATE_FORMAT(al.attendance_date, '%Y-%m') = :month
            WHERE g.is_deleted = 0
            GROUP BY g.guard_id
            ORDER BY g.full_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':month' => $month]);
    $guards = $stmt->fetchAll();
} catch (PDOException $e) {
    die("PAYROLL_ERROR: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Engine | Fast Security</title>
    <style>
        :root { --DeepDenim: #0B0F19; --SlateCard: #151C28; --Orange: #F97316; --Emerald: #10B981; --Crimson: #EF4444; }
        body { margin: 0; background: var(--DeepDenim); color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .Master { display: flex; width: 100vw; height: 100vh; }
        .Content { flex: 1; padding: 40px; overflow-y: auto; }
        .TacticalTable { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .TacticalTable td { background: var(--SlateCard); padding: 18px 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .Metric { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 0.65rem; font-weight: 900; margin-right: 5px; }
        .BtnProcess { background: var(--Orange); color: #fff; border: none; padding: 8px 16px; border-radius: 5px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-block; }
        .FlashSuccess { background: rgba(16,185,129,0.12); border: 1px solid #10B981; color: #10B981; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 700; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="Master">
    <?= $sidebarBuffer ?>
    <main class="Content">
        <header style="margin-bottom:30px;">
            <h1>PAYROLL ENGINE</h1>
            <p style="color:#94A3B8;">Automatic operational audit for <?= date('F Y') ?>.</p>
        </header>

        <?php if ($flashMsg): ?>
            <div class="FlashSuccess">✓ <?= htmlspecialchars($flashMsg) ?></div>
        <?php endif; ?>

        <table class="TacticalTable">
            <thead>
                <tr style="text-align:left; color:#94A3B8; font-size:0.7rem; text-transform:uppercase;">
                    <th>Personnel</th><th>Audit Summary</th><th>Base Salary</th><th style="text-align:right;">Disbursement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($guards as $g):
                    $absentFine = ($g['absents'] ?? 0) * 1000;
                    $lateFine   = ($g['lates']   ?? 0) * 500;
                    $net        = ($g['basic_salary'] ?? 0) - ($absentFine + $lateFine);
                ?>
                <tr>
                    <td>
                        <strong><?= strtoupper(htmlspecialchars($g['full_name'] ?? 'UNKNOWN')) ?></strong><br>
                        <span style="font-size:0.7rem; color:var(--Orange);"><?= htmlspecialchars($g['badge_number'] ?? 'N/A') ?></span>
                    </td>
                    <td>
                        <span class="Metric" style="background:rgba(239,68,68,0.1); color:var(--Crimson);">A: <?= $g['absents'] ?? 0 ?></span>
                        <span class="Metric" style="background:rgba(249,115,22,0.1); color:var(--Orange);">L: <?= $g['lates'] ?? 0 ?></span>
                    </td>
                    <td>PKR <?= number_format($g['basic_salary'] ?? 0) ?></td>
                    <td style="text-align:right;">
                        <span style="font-weight:900; color:var(--Emerald); margin-right:15px;">PKR <?= number_format($net) ?></span>
                        <a href="payroll_process.php?guard_id=<?= (int)$g['guard_id'] ?>" class="BtnProcess">DISBURSE</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>