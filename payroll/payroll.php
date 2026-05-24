<?php
/**
 * IMS | PAYROLL MANAGEMENT
 */
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'includes/connection.php';
include 'includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <h1>PAYROLL_COMPILATION_CENTER</h1>
        <p style="color: var(--accent-cyan); font-size: 0.7rem;">Automated Salary Disbursement Logic</p>
    </header>

    <form id="payroll-form">
        <div class="glass-panel" style="padding: 30px;">
            <div class="input-group">
                <label>TARGET_EMPLOYEE</label>
                <select id="employee_id" class="glass-input" required>
                    <option value="1">FS-9901 : Ahmed Khan</option>
                </select>
            </div>
            <div class="input-group" style="margin-top: 15px;">
                <label>DAILY_RATE (PKR)</label>
                <input type="number" id="daily_rate" class="glass-input" value="1200" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                <div class="input-group">
                    <label>MONTH</label>
                    <input type="number" id="month" class="glass-input" value="<?= date('m') ?>" min="1" max="12">
                </div>
                <div class="input-group">
                    <label>YEAR</label>
                    <input type="number" id="year" class="glass-input" value="<?= date('Y') ?>">
                </div>
            </div>
            <button type="submit" id="submit-btn" class="btn-authorize" style="width: 100%; margin-top: 20px;">AUTHORIZE_PAYROLL_COMPILATION</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('payroll-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submit-btn'); 
        btn.disabled = true;
        btn.innerText = 'PROCESSING_PAYROLL_COMPILATION...';

        const payload = {
            action: 'release_payroll',
            employee_id: document.getElementById('employee_id').value,
            month: document.getElementById('month').value,
            year: document.getElementById('year').value,
            daily_rate: document.getElementById('daily_rate').value
        };
        try {
            const res = await fetch('api/api_router.php?action=release_payroll', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify(payload) 
            });
            const data = await res.json();
            if(data.status === 'success') { 
                alert("SUCCESS: Payroll compiled for month."); 
                location.reload();
            } else { 
                alert("ERROR: " + data.message); 
            }
        } catch(e) { 
            alert("CRITICAL_NETWORK_FAILURE: Connection to API lost."); 
        } finally { 
            btn.disabled = false; 
            btn.innerText = 'AUTHORIZE_PAYROLL_COMPILATION';
        }
    };
</script>

<?php include 'includes/footer.php'; ?>
