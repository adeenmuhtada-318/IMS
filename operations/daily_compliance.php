<?php
require_once __DIR__ . '/includes/db.php'; // Fixed path security token
try {
    $guards = $pdo->query("SELECT Guard_ID, Full_Name, CNIC FROM Fast_Guards_HR WHERE Is_Archived = 0")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Halt: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fast Security - Daily Attendance Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b0c10; color: #c5a059; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); border-radius: 16px; padding: 35px; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        .form-control, .form-select { background-color: #1f2833; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .form-control:focus, .form-select:focus { background-color: #1f2833; border-color: #ff9800; color: #fff; box-shadow: none; }
        .toggle-container { background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .form-check-input:checked { background-color: #ff9800; border-color: #ff9800; }
        .commit-btn { background: linear-gradient(45deg, #ff9800, #e65100); color: #000; font-weight: bold; border: none; letter-spacing: 1px; padding: 14px; border-radius: 8px; transition: 0.3s; width: 100%; }
        .commit-btn:hover { transform: translateY(-2px); opacity: 0.9; color: #000; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-4 text-center text-white"><i class="fa-solid fa-shield-halved text-warning me-2"></i> Daily Guard Compliance Gate</h3>
        <hr class="border-secondary mb-4">
        <form id="complianceForm">
            <div class="mb-3">
                <label class="form-label text-light fw-semibold">Select Operational Guard</label>
                <select id="guard_id" class="form-select" required>
                    <option value="">-- Choose Active Profile --</option>
                    <?php foreach ($guards as $g): ?>
                        <option value="<?= $g['Guard_ID'] ?>"><?= htmlspecialchars($g['Full_Name'] . " (" . $g['CNIC'] . ")") ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light fw-semibold">Log Date</label>
                    <input type="date" id="log_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light fw-semibold">Attendance State</label>
                    <select id="attendance_status" class="form-select">
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Leave_Approved">Approved Leave</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-light fw-semibold">Shift Duty Type</label>
                <select id="overtime_shift" class="form-select">
                    <option value="No">Standard Shift (12 Hours)</option>
                    <option value="Yes">Double Shift (Overtime Premium Enabled)</option>
                </select>
            </div>
            
            <h5 class="text-white mb-3"><i class="fa-solid fa-list-check text-warning me-2"></i> Field Rule Infractions (Yes/No)</h5>
            
            <div class="toggle-container">
                <span class="text-light"><i class="fa-solid fa-user-tie me-2 text-secondary"></i> Incomplete Uniform / Kit?</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="v_uniform"></div>
            </div>
            <div class="toggle-container">
                <span class="text-light"><i class="fa-solid fa-gun me-2 text-secondary"></i> Weapon Management Negligence?</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="v_weapon"></div>
            </div>
            <div class="toggle-container">
                <span class="text-light"><i class="fa-solid fa-clock me-2 text-secondary"></i> Late Arrival Logged?</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="v_late"></div>
            </div>
            <div class="toggle-container">
                <span class="text-light"><i class="fa-solid fa-triangle-exclamation me-2 text-secondary"></i> Unprofessional Behavior Protocol?</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="v_behavior"></div>
            </div>

            <button type="button" onclick="commitDailyLogs()" class="commit-btn mt-3" id="actionBtn">COMMIT FIELD LEDGER</button>
        </form>
    </div>
</div>
<script>
document.getElementById('log_date').value = new Date().toISOString().split('T')[0];

async function commitDailyLogs() {
    const guard = document.getElementById('guard_id').value;
    if(!guard) { alert("Please select an active guard."); return; }
    
    const btn = document.getElementById('actionBtn');
    btn.innerText = "SYNCHRONIZING ATOMIC TRANSACTION...";
    btn.disabled = true;

    const payload = {
        guard_id: guard,
        log_date: document.getElementById('log_date').value,
        attendance_status: document.getElementById('attendance_status').value,
        overtime_shift: document.getElementById('overtime_shift').value,
        violation_uniform: document.getElementById('v_uniform').checked,
        violation_weapon: document.getElementById('v_weapon').checked,
        violation_late: document.getElementById('v_late').checked,
        violation_behavior: document.getElementById('v_behavior').checked
    };

    try {
        const response = await fetch('api/submit_daily_compliance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const out = await response.json();
        alert(out.message);
    } catch(e) {
        alert("API Core validation route broke.");
    } finally {
        btn.innerText = "COMMIT FIELD LEDGER";
        btn.disabled = false;
    }
}
</script>
</body>
</html>