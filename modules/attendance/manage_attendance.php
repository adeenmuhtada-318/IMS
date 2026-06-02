<?php
/**
 * ATTENDANCE MODULE - Shift Synchronized
 * Location: C:/xampp/htdocs/IMS/modules/attendance/manage_attendance.php
 */
require_once 'C:/xampp/htdocs/IMS/includes/shared_config.php';
session_start();

// 1. SIDEBAR
ob_start();
include ROOT_DIR . '/includes/sidebar.php';
$sidebarBuffer = ob_get_clean();

// 2. SHIFT FILTER
$activeShift = $_GET['shift'] ?? 'Morning';
$today = date('Y-m-d');

// 3. FETCH PERSONNEL FOR SHIFT
try {
    $sql = "SELECT g.*, al.status as current_status, al.remarks 
            FROM security_guards g
            LEFT JOIN attendance_logs al ON g.badge_number = al.badge_number 
                 AND al.date = :today AND al.shift_type = g.shift_type
            WHERE g.is_deleted = 0 AND g.shift_type = :shift
            ORDER BY g.full_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':today' => $today, ':shift' => $activeShift]);
    $guards = $stmt->fetchAll();
} catch (PDOException $e) {
    die("ATTENDANCE_ERROR: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Logs | Fast Security</title>
    <style>
        :root { --DeepDenim: #0B0F19; --SlateCard: #151C28; --Orange: #F97316; --Emerald: #10B981; --Crimson: #EF4444; }
        body { margin: 0; background: var(--DeepDenim); color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .Master { display: flex; width: 100vw; height: 100vh; }
        .Content { flex: 1; padding: 40px; overflow-y: auto; }
        .ShiftBar { background: var(--SlateCard); padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; }
        .ShiftLink { text-decoration: none; padding: 8px 20px; border-radius: 5px; font-weight: 800; font-size: 0.75rem; color: #94A3B8; border: 1px solid #334155; }
        .ShiftLink.active { background: var(--Orange); color: #fff; border-color: var(--Orange); }
        .TacticalTable { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .TacticalTable td { background: var(--SlateCard); padding: 18px 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .BtnSave { background: var(--Orange); color: #fff; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 900; cursor: pointer; float: right; margin-top: 20px; }
    </style>
</head>
<body>
<div class="Master">
    <?= $sidebarBuffer ?>
    <main class="Content">
        <header style="margin-bottom:30px;">
            <h1>ATTENDANCE LOGS</h1>
            <p style="color:#94A3B8;">Roll-call synchronized with <?= strtoupper($activeShift) ?> shift.</p>
        </header>

        <div class="ShiftBar">
            <span style="font-size:0.7rem; color:#94A3B8; text-transform:uppercase; font-weight:800; margin-right:10px;">Select Shift:</span>
            <a href="?shift=Morning" class="ShiftLink <?= $activeShift === 'Morning' ? 'active' : '' ?>">MORNING</a>
            <a href="?shift=Evening" class="ShiftLink <?= $activeShift === 'Evening' ? 'active' : '' ?>">EVENING</a>
        </div>

        <form action="process_attendance.php" method="POST">
            <input type="hidden" name="shift_type" value="<?= $activeShift ?>">
            <table class="TacticalTable">
                <thead>
                    <tr style="text-align:left; color:#94A3B8; font-size:0.7rem; text-transform:uppercase;">
                        <th>Badge</th><th>Personnel</th><th>Status Control</th><th>Operational Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($guards as $g): $badge = $g['badge_number']; $cur = $g['current_status'] ?? 'Present'; ?>
                    <tr>
                        <td style="color:var(--Orange); font-weight:800;"><?= $badge ?></td>
                        <td><strong><?= strtoupper($g['full_name']) ?></strong></td>
                        <td>
                            <select name="attendance[<?= $badge ?>]" style="background:#000; color:#fff; border:1px solid #333; padding:5px; border-radius:4px;">
                                <option value="Present" <?= $cur === 'Present' ? 'selected' : '' ?>>PRESENT</option>
                                <option value="Absent" <?= $cur === 'Absent' ? 'selected' : '' ?>>ABSENT</option>
                                <option value="On Leave" <?= $cur === 'On Leave' ? 'selected' : '' ?>>ON LEAVE</option>
                                <option value="Late" <?= $cur === 'Late' ? 'selected' : '' ?>>LATE (LT)</option>
                            </select>
                        </td>
                        <td><input type="text" name="remarks[<?= $badge ?>]" value="<?= htmlspecialchars($g['remarks'] ?? '') ?>" placeholder="Entry notes..." style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05); color:#fff; padding:6px; border-radius:4px;"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="BtnSave">SYNC <?= strtoupper($activeShift) ?> REGISTRY</button>
        </form>
    </main>
</div>
</body>
</html>
