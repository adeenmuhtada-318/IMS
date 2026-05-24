<?php
/**
 * STRATEGIC PAYROLL GENERATION ENGINE - TACTICAL IMS
 * Division: Finance & Compliance
 */

session_start();
require_once '../includes/connection.php';

// 1. RBAC SECURITY LOCK
$allowed_roles = ['Admin/CEO', 'Accountant', 'ADMIN'];
$current_role = $_SESSION['user_role'] ?? '';

if (!in_array($current_role, $allowed_roles) || $current_role === 'Operations Supervisor') {
    header("Location: ../dashboard.php?error=UNAUTHORIZED_PAYROLL_ACCESS");
    exit();
}

// RBAC ENFORCEMENT: Financial Data Shield
if ($current_role === 'Operations Supervisor') {
    $show_financials = false;
} else {
    $show_financials = true;
}

$is_ceo = ($current_role === 'Admin/CEO' || $current_role === 'ADMIN');

// 2. PARAMETERS & DATE HANDLER
$target_month = $_GET['month_year'] ?? date('Y-m');
$month_start = $target_month . "-01";
$month_end   = date("Y-m-t", strtotime($month_start));

// 3. CALCULATION ENGINE FUNCTIONS
function get_total_presents($pdo, $guard_id, $start, $end) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE guard_id = ? AND attendance_date BETWEEN ? AND ? AND attendance_status = 'P'");
    $stmt->execute([$guard_id, $start, $end]);
    return (int)$stmt->fetchColumn();
}

function get_overtime_days($pdo, $guard_id, $start, $end) {
    // Logic: Count days where guard has both Day and Night shift (Double Shift)
    $sql = "SELECT COUNT(*) FROM (
                SELECT attendance_date 
                FROM attendance 
                WHERE guard_id = ? AND attendance_date BETWEEN ? AND ? 
                GROUP BY attendance_date 
                HAVING COUNT(DISTINCT shift_type) = 2
            ) as double_shifts";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guard_id, $start, $end]);
    return (int)$stmt->fetchColumn();
}

function get_absence_penalty($pdo, $guard_id, $start, $end, $base_rate) {
    // Logic: If status='A' (Absent) and change_reason IS NULL, penalty = base_rate * 3
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE guard_id = ? AND attendance_date BETWEEN ? AND ? AND attendance_status = 'A' AND (change_reason IS NULL OR change_reason = '')");
    $stmt->execute([$guard_id, $start, $end]);
    $unnotified_absences = (int)$stmt->fetchColumn();
    return $unnotified_absences * ($base_rate * 3);
}

function get_uniform_deduction($joining_date) {
    $onboarding = new DateTime($joining_date);
    $today = new DateTime();
    $interval = $onboarding->diff($today);
    $days = $interval->days;
    return ($days < 180) ? 1500.00 : 0.00;
}

// 4. ACTION HANDLERS
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'save_draft') {
        try {
            $data = $_POST['payroll_data'];
            foreach ($data as $guard_id => $values) {
                $sql = "INSERT INTO payroll (
                            guard_id, month_year, base_salary_per_day, total_presents, overtime_days, 
                            overtime_bonus_amount, alertness_bonus, disciplinary_deduction, 
                            uniform_deduction, id_loss_fine, sleeping_fine, absence_forfeiture_amount, 
                            net_salary, payout_status
                        ) VALUES (
                            :gid, :my, :base, :pres, :ot_d, :ot_b, :alert, :disc, :unif, :id_f, :sleep, :abs_f, :net, 'Draft'
                        ) ON DUPLICATE KEY UPDATE 
                            alertness_bonus = VALUES(alertness_bonus),
                            disciplinary_deduction = VALUES(disciplinary_deduction),
                            id_loss_fine = VALUES(id_loss_fine),
                            sleeping_fine = VALUES(sleeping_fine),
                            uniform_deduction = VALUES(uniform_deduction),
                            net_salary = VALUES(net_salary),
                            payout_status = VALUES(payout_status)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':gid'   => $guard_id,
                    ':my'    => $target_month,
                    ':base'  => $values['base_rate'],
                    ':pres'  => $values['presents'],
                    ':ot_d'  => $values['ot_days'],
                    ':ot_b'  => $values['ot_bonus'],
                    ':alert' => $values['alertness_bonus'],
                    ':disc'  => $values['disciplinary_deduction'],
                    ':unif'  => $values['uniform_deduction'],
                    ':id_f'  => $values['id_loss_fine'],
                    ':sleep' => $values['sleeping_fine'],
                    ':abs_f' => $values['absence_penalty'],
                    ':net'   => $values['net_salary']
                ]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Payroll Draft Saved.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'approve_all') {
        $stmt = $pdo->prepare("UPDATE payroll SET payout_status = 'Approved' WHERE month_year = ? AND payout_status = 'Draft'");
        $stmt->execute([$target_month]);
        echo json_encode(['status' => 'success', 'message' => 'All Drafts Approved for ' . $target_month]);
        exit;
    }
}

// 5. FETCH ROSTER
$sql_roster = "SELECT g.*, p.alertness_bonus, p.disciplinary_deduction, p.id_loss_fine, p.sleeping_fine, p.payout_status
               FROM guards_personnel g 
               LEFT JOIN payroll p ON g.guard_id = p.guard_id AND p.month_year = ?
               WHERE g.is_deleted = 0 AND g.duty_status = 'Active Duty'";
$stmt_roster = $pdo->prepare($sql_roster);
$stmt_roster->execute([$target_month]);
$roster = $stmt_roster->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1 style="font-weight: 800; letter-spacing: 1px;">Payroll Generation</h1>
            <p class="sub-text">IMS Phase-V | Financial Calculation Matrix</p>
        </div>
        <div class="actions">
            <button class="btn-tactical" onclick="calculate_all()" style="background: var(--accent-cyan); color: #000;">Auto-Calculate All</button>
            <button class="btn-tactical btn-primary" onclick="approve_all()">Approve All Drafts</button>
        </div>
    </header>

    <!-- DATE SELECTOR -->
    <section class="glass-panel" style="padding: 20px; margin-bottom: 25px;">
        <form method="GET" id="month-selector">
            <div style="display: flex; gap: 20px; align-items: center;">
                <label style="color: var(--text-dim); font-size: 0.8rem; font-weight: 600;">Target Period:</label>
                <input type="month" name="month_year" value="<?php echo $target_month; ?>" class="glass-input" style="width: 250px;" onchange="this.form.submit()">
            </div>
        </form>
    </section>

    <!-- PAYROLL MATRIX -->
    <section class="glass-panel data-panel" style="overflow-x: auto;">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Guard Operator</th>
                    <th>Base Rate</th>
                    <th>Presents</th>
                    <th>OT Days</th>
                    <th>OT Bonus</th>
                    <th>Alert Bonus</th>
                    <th>Disc. Ded.</th>
                    <th>Uniform Ded.</th>
                    <th>Fine ID/Sleep</th>
                    <th>Absence Penalty</th>
                    <?php if($is_ceo): ?>
                        <th>Rate Site</th>
                        <th>Office Exp</th>
                    <?php endif; ?>
                    <th>Net Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roster as $g): ?>
                    <?php 
                        // Initial calculations for UI preview
                        $pres = get_total_presents($pdo, $g['guard_id'], $month_start, $month_end);
                        $ot_d = get_overtime_days($pdo, $g['guard_id'], $month_start, $month_end);
                        $base = $g['base_salary_per_day'] ?: 0;
                        $ot_b = $ot_d * $base * 0.25;
                        $unif = get_uniform_deduction($g['joining_date']);
                        $abs_p = get_absence_penalty($pdo, $g['guard_id'], $month_start, $month_end, $base);
                        
                        // Check for returnable uniform advance
                        $onboarding_days = (new DateTime($g['joining_date']))->diff(new DateTime())->days;
                        $is_returnable = ($onboarding_days >= 180 && $unif == 0); // Simplified logic
                    ?>
                    <tr data-guard-id="<?php echo $g['guard_id']; ?>" data-base="<?php echo $base; ?>" data-pres="<?php echo $pres; ?>" data-ot-d="<?php echo $ot_d; ?>" data-ot-b="<?php echo $ot_b; ?>" data-unif="<?php echo $unif; ?>" data-abs-p="<?php echo $abs_p; ?>">
                        <td>
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($g['full_name']); ?></div>
                            <div style="font-size: 0.65rem; color: var(--text-dim);"><?php echo $g['guard_no']; ?></div>
                            <?php if($onboarding_days >= 180): ?>
                                <span class="badge-uniform">Uniform Advance: Returnable</span>
                            <?php endif; ?>
                        </td>
                        <td class="monospaced"><?php echo number_format($base, 2); ?></td>
                        <td><?php echo $pres; ?></td>
                        <td><?php echo $ot_d; ?></td>
                        <td class="monospaced"><?php echo number_format($ot_b, 2); ?></td>
                        <td><input type="number" step="0.01" class="glass-input alert-bonus" value="<?php echo $g['alertness_bonus'] ?? 0; ?>" style="width: 80px;"></td>
                        <td><input type="number" step="0.01" class="glass-input disc-ded" value="<?php echo $g['disciplinary_deduction'] ?? 0; ?>" style="width: 80px;"></td>
                        <td><input type="number" step="0.01" class="glass-input unif-ded" value="<?php echo $unif; ?>" style="width: 80px;"></td>
                        <td>
                            <input type="number" step="0.01" class="glass-input id-fine" value="<?php echo $g['id_loss_fine'] ?? 0; ?>" style="width: 60px; margin-bottom: 5px;" placeholder="ID">
                            <input type="number" step="0.01" class="glass-input sleep-fine" value="<?php echo $g['sleeping_fine'] ?? 0; ?>" style="width: 60px;" placeholder="Sleep">
                        </td>
                        <td class="monospaced"><?php echo number_format($abs_p, 2); ?></td>
                        
                        <?php if($is_ceo): ?>
                            <td class="monospaced" style="color: var(--accent-cyan);">---</td> <!-- Placeholder for site rate -->
                            <td class="monospaced" style="color: var(--alert-orange);">---</td> <!-- Placeholder for office exp -->
                        <?php endif; ?>

                        <td class="monospaced net-salary" style="font-weight: 800; color: var(--accent-cyan);">0.00</td>
                        <td>
                            <span class="status-badge <?php echo strtolower($g['payout_status'] ?? 'Draft'); ?>">
                                <?php echo ucwords(strtolower($g['payout_status'] ?? 'Draft')); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
        <button class="btn-authorize" onclick="save_all_drafts()" style="width: 300px;">Commit Payroll Drafts</button>
    </div>
</div>

<style>
.payroll-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.payroll-table th, .payroll-table td { padding: 12px; text-align: center; border-bottom: 1px solid var(--border-frost); }
.payroll-table th { color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
.badge-uniform { font-size: 0.55rem; background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e; padding: 2px 5px; border-radius: 3px; margin-top: 5px; display: inline-block; }

.status-badge.draft { background: rgba(148, 163, 184, 0.1); color: #94a3b8; }
.status-badge.approved { background: rgba(0, 251, 255, 0.1); color: var(--accent-cyan); border: 1px solid var(--accent-cyan); }
.status-badge.paid { background: rgba(34, 197, 94, 0.2); color: #22c55e; }

input.glass-input { text-align: center; padding: 5px; font-size: 0.8rem; }
</style>

<script>
function calculate_all() {
    document.querySelectorAll('tbody tr').forEach(tr => {
        const base = parseFloat(tr.getAttribute('data-base'));
        const presents = parseInt(tr.getAttribute('data-pres'));
        const otBonus = parseFloat(tr.getAttribute('data-ot-b'));
        const alertBonus = parseFloat(tr.querySelector('.alert-bonus').value) || 0;
        const discDed = parseFloat(tr.querySelector('.disc-ded').value) || 0;
        const unifDed = parseFloat(tr.querySelector('.unif-ded').value) || 0;
        const idFine = parseFloat(tr.querySelector('.id-fine').value) || 0;
        const sleepFine = parseFloat(tr.querySelector('.sleep-fine').value) || 0;
        const absPenalty = parseFloat(tr.getAttribute('data-abs-p'));

        // Formula: (presents * base) + otBonus + alertBonus - discDed - unifDed - idFine - sleepFine - absPenalty
        const netSalary = (presents * base) + otBonus + alertBonus - discDed - unifDed - idFine - sleepFine - absPenalty;
        
        tr.querySelector('.net-salary').innerText = netSalary.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        tr.setAttribute('data-computed-net', netSalary);
    });
}

function save_all_drafts() {
    const data = {};
    document.querySelectorAll('tbody tr').forEach(tr => {
        const gid = tr.getAttribute('data-guard-id');
        data[gid] = {
            base_rate: tr.getAttribute('data-base'),
            presents: tr.getAttribute('data-pres'),
            ot_days: tr.getAttribute('data-ot-d'),
            ot_bonus: tr.getAttribute('data-ot-b'),
            alertness_bonus: tr.querySelector('.alert-bonus').value,
            disciplinary_deduction: tr.querySelector('.disc-ded').value,
            uniform_deduction: tr.querySelector('.unif-ded').value,
            id_loss_fine: tr.querySelector('.id-fine').value,
            sleeping_fine: tr.querySelector('.sleep-fine').value,
            absence_penalty: tr.getAttribute('data-abs-p'),
            net_salary: tr.getAttribute('data-computed-net') || 0
        };
    });

    const formData = new FormData();
    formData.append('action', 'save_draft');
    // Using a simple JSON string to pass complex data
    for (let gid in data) {
        for (let key in data[gid]) {
            formData.append(`payroll_data[${gid}][${key}]`, data[gid][key]);
        }
    }

    fetch('payroll_generate.php?month_year=<?php echo $target_month; ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if(res.status === 'success') location.reload();
    });
}

function approve_all() {
    if(!confirm("CONFIRM_COMMAND: Authorize ALL draft payrolls for this period?")) return;
    
    const formData = new FormData();
    formData.append('action', 'approve_all');

    fetch('payroll_generate.php?month_year=<?php echo $target_month; ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if(res.status === 'success') location.reload();
    });
}

// Initial calculation on load
window.onload = calculate_all;
</script>

<script>
    const userRole = '<?php echo $_SESSION['user_role']; ?>';
    if (userRole === 'Operations Supervisor') {
        document.querySelectorAll('.financial-col').forEach(el => el.style.display = 'none');
    }
</script>

<?php include '../includes/footer.php'; ?>
