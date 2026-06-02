<?php
/**
 * PAYROLL PROCESS PAGE
 * Opened via GET (show calculation) → submitted via POST (confirm & save)
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/IMS/includes/shared_config.php';
session_start();

// ── POST: Confirm and insert ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guard_id   = (int)$_POST['guard_id'];
    $net_salary = (float)$_POST['net_salary'];

    try {
        $stmt = $pdo->prepare("INSERT INTO salary_records (guard_id, net_payable, month_year) VALUES (?, ?, ?)");
        $stmt->execute([$guard_id, $net_salary, date('Y-m')]);
        $_SESSION['flash'] = "Payroll processed successfully!";
        header("Location: payroll_dashboard.php?msg=success");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// ── GET: Show salary breakdown for this guard ─────────────────────────────────
if (!isset($_GET['guard_id'])) {
    header("Location: payroll_dashboard.php");
    exit;
}

$guard_id = (int)$_GET['guard_id'];
$month    = date('Y-m');

try {
    // Fetch guard + attendance counts for current month
    $sql = "SELECT g.*,
                   SUM(CASE WHEN al.attendance_status = 'Absent' THEN 1 ELSE 0 END) as absents,
                   SUM(CASE WHEN al.attendance_status = 'Late'   THEN 1 ELSE 0 END) as lates,
                   COUNT(CASE WHEN al.attendance_status = 'Present' THEN 1 END)     as presents
            FROM security_guards g
            LEFT JOIN attendance_logs al
                ON g.badge_number = al.badge_number
               AND DATE_FORMAT(al.attendance_date, '%Y-%m') = :month
            WHERE g.guard_id = :guard_id AND g.is_deleted = 0
            GROUP BY g.guard_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':month' => $month, ':guard_id' => $guard_id]);
    $g = $stmt->fetch();

    if (!$g) {
        die("Guard not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Salary calculation
$basic_salary = $g['basic_salary'] ?? 0;
$absents      = (int)($g['absents']  ?? 0);
$lates        = (int)($g['lates']    ?? 0);
$presents     = (int)($g['presents'] ?? 0);
$absent_fine  = $absents * 1000;
$late_fine    = $lates   * 500;
$total_fines  = $absent_fine + $late_fine;
$net_payable  = $basic_salary - $total_fines;

// Sidebar
ob_start();
include ROOT_DIR . '/includes/sidebar.php';
$sidebarBuffer = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Salary | <?= htmlspecialchars(strtoupper($g['full_name'])) ?></title>
    <style>
        :root {
            --DeepDenim: #0B0F19;
            --SlateCard: #151C28;
            --Orange:    #F97316;
            --Emerald:   #10B981;
            --Crimson:   #EF4444;
            --Border:    #1E293B;
            --Muted:     #94A3B8;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--DeepDenim); color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .Master  { display: flex; width: 100vw; height: 100vh; }
        .Content { flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; align-items: center; }

        /* Back link */
        .BackLink { align-self: flex-start; color: var(--Muted); text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-bottom: 32px; display: flex; align-items: center; gap: 6px; }
        .BackLink:hover { color: #fff; }

        /* Card */
        .Card {
            background: var(--SlateCard);
            border: 1px solid var(--Border);
            border-radius: 14px;
            width: 100%;
            max-width: 560px;
            padding: 36px 40px;
        }

        .GuardHeader { margin-bottom: 28px; }
        .GuardHeader h2 { margin: 0 0 4px 0; font-size: 1.3rem; letter-spacing: 0.5px; }
        .GuardHeader p  { margin: 0; color: var(--Orange); font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; }

        /* Breakdown rows */
        .BreakdownTable { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .BreakdownTable tr td { padding: 12px 0; font-size: 0.9rem; border-bottom: 1px solid var(--Border); }
        .BreakdownTable tr:last-child td { border-bottom: none; }
        .BreakdownTable td:last-child { text-align: right; font-weight: 700; }
        .LabelMuted { color: var(--Muted); }
        .DeductionVal { color: var(--Crimson); }
        .NeutralVal   { color: #fff; }

        /* Net payable total */
        .NetRow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.25);
            border-radius: 8px;
            padding: 18px 20px;
            margin: 20px 0 28px 0;
        }
        .NetRow span:first-child { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--Muted); }
        .NetRow span:last-child  { font-size: 1.6rem; font-weight: 900; color: var(--Emerald); }

        /* Attendance pills */
        .PillRow { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
        .Pill { padding: 5px 14px; border-radius: 20px; font-size: 0.7rem; font-weight: 900; letter-spacing: 0.5px; }

        /* Buttons */
        .BtnRow { display: flex; gap: 12px; }
        .BtnConfirm { flex: 1; background: var(--Orange); color: #fff; border: none; padding: 14px; border-radius: 7px; font-weight: 800; font-size: 0.95rem; cursor: pointer; }
        .BtnConfirm:hover { background: #ea6c0a; }
        .BtnCancel  { flex: 1; background: transparent; color: var(--Muted); border: 1px solid var(--Border); padding: 14px; border-radius: 7px; font-weight: 700; font-size: 0.95rem; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
        .BtnCancel:hover { background: var(--Border); color: #fff; }

        .MonthBadge { display: inline-block; background: rgba(249,115,22,0.1); color: var(--Orange); font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 4px; margin-bottom: 20px; letter-spacing: 0.5px; }
    </style>
</head>
<body>
<div class="Master">
    <?= $sidebarBuffer ?>
    <main class="Content">
        <a href="payroll_dashboard.php" class="BackLink">&#8592; Back to Payroll Engine</a>

        <div class="Card">
            <div class="GuardHeader">
                <h2><?= htmlspecialchars(strtoupper($g['full_name'])) ?></h2>
                <p><?= htmlspecialchars($g['badge_number']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($g['designation'] ?? 'Security Guard') ?></p>
            </div>

            <div class="MonthBadge">SALARY PERIOD: <?= date('F Y') ?></div>

            <!-- Attendance Summary Pills -->
            <div class="PillRow">
                <span class="Pill" style="background:rgba(16,185,129,0.1); color:var(--Emerald);">&#10003; Present: <?= $presents ?></span>
                <span class="Pill" style="background:rgba(239,68,68,0.1);  color:var(--Crimson);">&#10007; Absent: <?= $absents ?></span>
                <span class="Pill" style="background:rgba(249,115,22,0.1); color:var(--Orange);">&#9679; Late: <?= $lates ?></span>
            </div>

            <!-- Salary Breakdown -->
            <table class="BreakdownTable">
                <tr>
                    <td class="LabelMuted">Basic Salary</td>
                    <td class="NeutralVal">PKR <?= number_format($basic_salary) ?></td>
                </tr>
                <tr>
                    <td class="LabelMuted">Absent Deduction &nbsp;<small style="color:#475569;">(<?= $absents ?> × PKR 1,000)</small></td>
                    <td class="DeductionVal">− PKR <?= number_format($absent_fine) ?></td>
                </tr>
                <tr>
                    <td class="LabelMuted">Late Deduction &nbsp;<small style="color:#475569;">(<?= $lates ?> × PKR 500)</small></td>
                    <td class="DeductionVal">− PKR <?= number_format($late_fine) ?></td>
                </tr>
                <tr>
                    <td class="LabelMuted">Total Deductions</td>
                    <td class="DeductionVal">− PKR <?= number_format($total_fines) ?></td>
                </tr>
            </table>

            <!-- Net Payable -->
            <div class="NetRow">
                <span>Net Payable</span>
                <span>PKR <?= number_format($net_payable) ?></span>
            </div>

            <!-- Confirm Form -->
            <form method="POST" action="payroll_process.php">
                <input type="hidden" name="guard_id"   value="<?= $guard_id ?>">
                <input type="hidden" name="net_salary" value="<?= $net_payable ?>">
                <div class="BtnRow">
                    <a href="payroll_dashboard.php" class="BtnCancel">CANCEL</a>
                    <button type="submit" class="BtnConfirm">CONFIRM &amp; DISBURSE</button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>