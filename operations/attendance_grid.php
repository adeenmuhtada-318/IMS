<?php
/**
 * ATTENDANCE MATRIX - TACTICAL IMS
 * Features: Grid-based logging, cycling states, keyboard shortcuts, and reliever logic.
 */

session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';
include '../includes/header.php';

// 1. PARAMETERS
$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year'] ?? date('Y'));
$site_id = (int)($_GET['site_id'] ?? 0);
$shift = $_GET['shift'] ?? 'Day';

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// 2. FETCH DATA
// Get Sites
$sites = $pdo->query("SELECT site_id, site_name FROM client_sites ORDER BY site_name ASC")->fetchAll();

// Get Active Guards
$guards = $pdo->query("SELECT guard_id, full_name, guard_no FROM guards_personnel WHERE is_deleted = 0 AND duty_status = 'Active Duty' ORDER BY full_name ASC")->fetchAll();

// Get Existing Attendance for this month/site/shift
$attendance_data = [];
if ($site_id) {
    $sql_att = "SELECT guard_id, DAY(attendance_date) as day, attendance_status, reliever_assigned_to 
                FROM attendance 
                WHERE site_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ? AND shift_type = ?";
    $stmt_att = $pdo->prepare($sql_att);
    $stmt_att->execute([$site_id, $month, $year, $shift]);
    while ($row = $stmt_att->fetch()) {
        $attendance_data[$row['guard_id']][$row['day']] = $row['attendance_status'];
    }
}
?>

<style>
    .attendance-grid-container {
        overflow-x: auto;
        margin-top: 20px;
        max-height: 70vh;
    }
    .att-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.8rem;
    }
    .att-table th, .att-table td {
        border: 1px solid var(--border-frost);
        padding: 5px;
        text-align: center;
        min-width: 35px;
    }
    .att-table th:first-child, .att-table td:first-child {
        position: sticky;
        left: 0;
        background: var(--bg-panel);
        z-index: 10;
        min-width: 200px;
        text-align: left;
        padding-left: 15px;
    }
    .att-cell {
        cursor: pointer;
        width: 100%;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 800;
        border-radius: 4px;
        transition: all 0.2s;
        outline: none;
    }
    .att-cell:focus {
        box-shadow: 0 0 0 2px var(--accent-cyan);
    }
    .status-P { background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid #22c55e; }
    .status-A { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
    .status-R { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .status-empty { background: rgba(255, 255, 255, 0.03); color: transparent; border: 1px solid transparent; }

    .grid-controls {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .legend {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        font-size: 0.8rem;
    }
    .legend-item { display: flex; align-items: center; gap: 8px; }
    .legend-box { width: 15px; height: 15px; border-radius: 3px; }

    /* Reliever Modal */
    #reliever-modal {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }
</style>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1 style="font-weight: 800; letter-spacing: 1px;">Attendance Operations Grid</h1>
            <p class="sub-text">IMS Phase-V | Force Logistics Synchronization</p>
        </div>
    </header>

    <section class="glass-panel grid-controls">
        <form method="GET" style="display: flex; gap: 15px; width: 100%; align-items: flex-end;">
            <div class="input-group" style="margin-bottom: 0;">
                <label>Target Site</label>
                <select name="site_id" class="glass-input" required onchange="this.form.submit()">
                    <option value="">Select Site</option>
                    <?php foreach ($sites as $s): ?>
                        <option value="<?php echo $s['site_id']; ?>" <?php echo $site_id === (int)$s['site_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['site_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group" style="margin-bottom: 0;">
                <label>Month</label>
                <select name="month" class="glass-input" onchange="this.form.submit()">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $month === $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="input-group" style="margin-bottom: 0;">
                <label>Year</label>
                <select name="year" class="glass-input" onchange="this.form.submit()">
                    <?php for($y=2025; $y<=2030; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="input-group" style="margin-bottom: 0;">
                <label>Shift</label>
                <select name="shift" class="glass-input" onchange="this.form.submit()">
                    <option value="Day" <?php echo $shift === 'Day' ? 'selected' : ''; ?>>Day Shift</option>
                    <option value="Night" <?php echo $shift === 'Night' ? 'selected' : ''; ?>>Night Shift</option>
                </select>
            </div>
        </form>
    </section>

    <?php if ($site_id): ?>
    <section class="glass-panel attendance-grid-container">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Guard Operator</th>
                    <?php for($d=1; $d<=$days_in_month; $d++): ?>
                        <th><?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guards as $g): ?>
                <tr>
                    <td>
                        <div style="font-weight: 700;"><?php echo htmlspecialchars($g['full_name']); ?></div>
                        <div style="font-size: 0.65rem; color: var(--text-dim);"><?php echo htmlspecialchars($g['guard_no']); ?></div>
                    </td>
                    <?php for($d=1; $d<=$days_in_month; $d++): ?>
                        <?php 
                            $status = $attendance_data[$g['guard_id']][$d] ?? ''; 
                            $class = $status ? "status-$status" : "status-empty";
                        ?>
                        <td>
                            <div class="att-cell <?php echo $class; ?>" 
                                 tabindex="0"
                                 data-guard="<?php echo $g['guard_id']; ?>" 
                                 data-day="<?php echo $d; ?>"
                                 data-status="<?php echo $status; ?>"
                                 onclick="cycle_status(this)"
                                 onkeydown="handle_key(event, this)">
                                <?php echo $status ?: ''; ?>
                            </div>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <div class="legend">
        <div class="legend-item"><div class="legend-box status-P"></div> Present (P)</div>
        <div class="legend-item"><div class="legend-box status-A"></div> Absent (A)</div>
        <div class="legend-item"><div class="legend-box status-R"></div> Reliever (R)</div>
        <div style="margin-left: auto; color: var(--text-dim); font-size: 0.75rem;">
            * CLICK cell to cycle | FOCUS & PRESS P/A/R keys for fast logging
        </div>
    </div>
    <?php else: ?>
    <div class="glass-panel" style="padding: 100px; text-align: center; color: var(--text-dim);">
        <i class="fa-solid fa-hand-pointer" style="font-size: 2rem; margin-bottom: 20px;"></i>
        <h3>Select Site to Initialize Attendance Grid</h3>
    </div>
    <?php endif; ?>
</div>

<!-- RELIEVER SELECTION MODAL -->
<div id="reliever-modal" onclick="close_reliever_modal()">
    <div class="glass-panel" style="width: 400px; padding: 30px;" onclick="event.stopPropagation()">
        <h3 style="color: var(--accent-cyan); margin-bottom: 20px;">Assign Reliever</h3>
        <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 15px;" id="reliever-info"></p>
        
        <div class="input-group">
            <label>Available Relievers</label>
            <select id="reliever-select" class="glass-input">
                <!-- Loaded via AJAX -->
            </select>
        </div>
        <div class="input-group">
            <label>Reason for Replacement</label>
            <textarea id="reliever-reason" class="glass-input" style="height: 80px;"></textarea>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button class="btn-authorize" onclick="save_reliever()" style="flex: 1;">Authorize</button>
            <button class="btn-authorize" onclick="close_reliever_modal()" style="flex: 1; border-color: var(--text-dim); color: var(--text-dim);">Cancel</button>
        </div>
    </div>
</div>

<script>
const CONFIG = {
    site_id: <?php echo $site_id; ?>,
    month: <?php echo $month; ?>,
    year: <?php echo $year; ?>,
    shift: '<?php echo $shift; ?>'
};

let activeCell = null;

function cycle_status(el) {
    const states = ['', 'P', 'A', 'R'];
    let current = el.getAttribute('data-status');
    let nextIndex = (states.indexOf(current) + 1) % states.length;
    let next = states[nextIndex];
    
    update_attendance(el, next);
}

function handle_key(e, el) {
    const key = e.key.toUpperCase();
    if (['P', 'A', 'R'].includes(key)) {
        update_attendance(el, key);
    } else if (e.key === 'Backspace' || e.key === 'Delete') {
        update_attendance(el, '');
    }
}

function update_attendance(el, status) {
    if (status === 'R') {
        open_reliever_modal(el);
        return;
    }
    
    save_to_db(el, status);
}

function save_to_db(el, status, relieverId = null, reason = '') {
    const guardId = el.getAttribute('data-guard');
    const day = el.getAttribute('data-day');
    const dateStr = `${CONFIG.year}-${String(CONFIG.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    const dataObj = {
        guard_id: guardId,
        site_id: CONFIG.site_id,
        attendance_date: dateStr,
        shift_type: CONFIG.shift,
        attendance_status: status,
        reliever_assigned_to: relieverId,
        change_reason: reason
    };

    fetch('../api/api_router.php?action=save_attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataObj)
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.status === 'success') {
            el.setAttribute('data-status', status);
            el.innerText = status;
            el.className = 'att-cell ' + (status ? 'status-' + status : 'status-empty');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('SYSTEM_ERROR: Sync Failed');
    });
}

function open_reliever_modal(el) {
    activeCell = el;
    const guardName = el.closest('tr').querySelector('div').innerText;
    const day = el.getAttribute('data-day');
    const dateStr = `${CONFIG.year}-${String(CONFIG.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    document.getElementById('reliever-info').innerText = `Relieving ${guardName} on ${dateStr}`;
    document.getElementById('reliever-modal').style.display = 'flex';
    
    // Load Available Relievers
    fetch(`../api/api_router.php?action=get_relievers&date=${dateStr}&shift=${CONFIG.shift}`)
    .then(resp => resp.json())
    .then(data => {
        const select = document.getElementById('reliever-select');
        select.innerHTML = '<option value="">Select Reliever</option>';
        data.forEach(g => {
            select.innerHTML += `<option value="${g.guard_id}">${g.full_name} (${g.guard_no})</option>`;
        });
    });
}

function close_reliever_modal() {
    document.getElementById('reliever-modal').style.display = 'none';
    activeCell = null;
}

function save_reliever() {
    const relieverId = document.getElementById('reliever-select').value;
    const reason = document.getElementById('reliever-reason').value;
    
    if (!relieverId) {
        alert('Please select a reliever.');
        return;
    }
    
    save_to_db(activeCell, 'R', relieverId, reason);
    close_reliever_modal();
}
</script>

<?php include '../includes/footer.php'; ?>
