<?php
require_once __DIR__ . '/includes/db.php';
try {
    $guards = $pdo->query("SELECT Guard_ID, Full_Name, CNIC FROM Fast_Guards_HR WHERE Is_Archived = 0")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Halt: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fast Security - Monthly Advisory Engine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b0c10; color: #fff; font-family: sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); border-radius: 16px; padding: 30px; box-shadow: 0 4px 24px rgba(0,0,0,0.5); }
        .form-select { background-color: #1f2833; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .metric-badge { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 15px; text-center: center; }
        .advise-box { border-left: 5px solid; padding: 15px; border-radius: 4px; margin-top: 20px; background: rgba(255,255,255,0.01); }
        .advise-yes { border-color: #4caf50; color: #81c784; }
        .advise-no { border-color: #f44336; color: #e57373; }
        .trigger-btn { background: #ff9800; color: #000; font-weight: bold; border: none; padding: 12px 25px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="glass-card mb-4">
        <h4><i class="fa-solid fa-chart-line text-warning me-2"></i> Monthly Progress & Salary Evaluation Controller</h4>
        <hr class="border-secondary">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-secondary">Select Employee Profile</label>
                <select id="guard_id" class="form-select">
                    <option value="">-- Choose Profile --</option>
                    <?php foreach ($guards as $g): ?>
                        <option value="<?= $g['Guard_ID'] ?>"><?= htmlspecialchars($g['Full_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">Target Month</label>
                <select id="month" class="form-select">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= date('n')==$m ? 'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">Year</label>
                <select id="year" class="form-select">
                    <option value="2026" selected>2026</option>
                    <option value="2027">2027</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button onclick="runMonthlyAdvisory()" class="trigger-btn w-100"><i class="fa-solid fa-gears me-1"></i> ANALYZE</button>
            </div>
        </div>
    </div>

    <div id="dashboardView" class="glass-card d-none">
        <h5 class="text-white mb-4"><i class="fa-solid fa-square-poll-vertical text-warning me-2"></i> Compiled Performance Metrics</h5>
        <div class="row g-3 text-center">
            <div class="col-md-3"><div class="metric-badge"><p class="text-secondary small mb-1">Days Present</p><h3 id="m_days" class="text-white mb-0">0</h3></div></div>
            <div class="col-md-3"><div class="metric-badge"><p class="text-secondary small mb-1">Accrued Fines Added</p><h3 id="m_fines" class="text-danger mb-0">Rs. 0</h3></div></div>
            <div class="col-md-6">
                <div class="metric-badge text-start">
                    <p class="text-secondary small mb-2">Detailed Rule Breach Infractions Tracker</p>
                    <span class="badge bg-danger me-1">Uniform: <span id="f_unif">0</span></span>
                    <span class="badge bg-danger me-1">Weapon: <span id="f_weap">0</span></span>
                    <span class="badge bg-danger me-1">Late: <span id="f_late">0</span></span>
                    <span class="badge bg-danger">Behavior: <span id="f_behav">0</span></span>
                </div>
            </div>
        </div>

        <div id="advisoryBox" class="advise-box">
            <h6 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Automated System Increment Suggestion:</h6>
            <p id="advisoryText" class="mb-0 fw-semibold"></p>
        </div>
    </div>
</div>

<script>
async function runMonthlyAdvisory() {
    const id = document.getElementById('guard_id').value;
    if(!id) { alert("Please select a profile."); return; }

    const payload = {
        guard_id: id,
        month: document.getElementById('month').value,
        year: document.getElementById('year').value
    };

    try {
        const response = await fetch('api/get_monthly_progress.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await response.json();
        
        if(res.status === "SUCCESS") {
            document.getElementById('dashboardView').classList.remove('d-none');
            document.getElementById('m_days').innerText = res.data.Days_Present;
            document.getElementById('m_fines').innerText = "Rs. " + parseFloat(res.data.Accumulated_Fines).toFixed(2);
            
            document.getElementById('f_unif').innerText = res.data.Uniform_Fails;
            document.getElementById('f_weap').innerText = res.data.Weapon_Fails;
            document.getElementById('f_late').innerText = res.data.Late_Fails;
            document.getElementById('f_behav').innerText = res.data.Behavior_Fails;

            const text = res.data.Increment_Suggestion;
            document.getElementById('advisoryText').innerText = text;

            const advBox = document.getElementById('advisoryBox');
            if(text.includes("YES")) {
                advBox.className = "advise-box advise-yes";
            } else {
                advBox.className = "advise-box advise-no";
            }
        } else {
            alert(res.message || "No logs found for selected timeline.");
        }
    } catch(e) {
        alert("Failed to communicate with calculation engine.");
    }
}
</script>
</body>
</html>