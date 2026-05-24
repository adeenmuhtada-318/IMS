<?php
/**
 * IMS | CLIENT DEPLOYMENT GRID
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
        <h1 style="letter-spacing: 3px;">CLIENT_DEPLOYMENT_GRID</h1>
        <p style="color: var(--accent-cyan); font-size: 0.7rem;">Authorized Asset Allocation</p>
    </header>

    <form id="deployment-form">
        <div class="glass-panel">
            <div class="form-grid">
                <div class="input-group">
                    <label>CLIENT_NAME</label>
                    <input type="text" id="client_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>LOCATION</label>
                    <input type="text" id="location" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>LOG_DATE</label>
                    <input type="date" id="log_date" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>SUPERVISOR</label>
                    <input type="text" id="supervisor" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>TOTAL_GUARDS</label>
                    <input type="number" id="total_guards" class="glass-input" value="1" required>
                </div>
                <div class="input-group">
                    <label>RATE_GUARD</label>
                    <input type="number" id="rate_guard" class="glass-input" step="0.01" required>
                </div>
                <div class="input-group">
                    <label>RATE_SUPER</label>
                    <input type="number" id="rate_super" class="glass-input" step="0.01" required>
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 0.8rem; color: var(--accent-cyan);">EQUIPMENT_MANIFEST</h3>
                <button type="button" class="btn-tactical" onclick="this_add_row()" style="padding: 5px 15px;">+ ADD_UNIT_LINE</button>
            </div>
            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; font-size: 0.6rem; color: var(--text-dim);">
                            <th>TYPE</th>
                            <th>SERIAL_NO</th>
                            <th>AMMO</th>
                            <th>SHT</th>
                            <th>PNT</th>
                            <th>CAP</th>
                            <th>SHS</th>
                            <th>BLT</th>
                            <th>JSY</th>
                            <th>REMARKS</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="grid-body"></tbody>
                </table>
            </div>
        </div>
        <button type="submit" id="submit-btn" class="btn-authorize" style="width: 100%; margin-top: 20px;">AUTHORIZE_DEPLOYMENT_COMMIT</button>
    </form>
</div>

<style>
    .kit-input { width: 50px !important; text-align: center; padding: 5px !important; }
    .row-remove { color: var(--alert-orange); cursor: pointer; }
</style>

<script>
    const gridBody = document.getElementById('grid-body');
    function this_add_row() {
        const row = document.createElement('tr');
        row.className = 'grid-row';
        row.innerHTML = `
            <td>
                <select class="glass-input row-type" style="padding: 5px;">
                    <option value="1">12 BORE</option>
                    <option value="2">30 BORE</option>
                </select>
            </td>
            <td><input type="text" class="glass-input row-serial" style="padding: 5px;" required></td>
            <td><input type="number" class="glass-input row-ammo kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-shirt kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-pants kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-cap kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-shoes kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-belt kit-input" value="0"></td>
            <td><input type="number" class="glass-input row-jersey kit-input" value="0"></td>
            <td><input type="text" class="glass-input row-remarks" style="padding: 5px;"></td>
            <td><i class="fa-solid fa-trash row-remove" onclick="this_remove_row(this)"></i></td>
        `;
        gridBody.appendChild(row);
    }

    function this_remove_row(btn) { 
        if(document.querySelectorAll('.grid-row').length > 1) btn.closest('tr').remove(); 
    }

    document.getElementById('deployment-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submit-btn');
        btn.disabled = true; 
        btn.innerText = "PROCESSING_COMMITMENT...";

        const payload = {
            action: 'deploy',
            master_log: {
                client_name: document.getElementById('client_name').value,
                location: document.getElementById('location').value,
                log_date: document.getElementById('log_date').value,
                supervisor_name: document.getElementById('supervisor').value,
                total_guards: parseInt(document.getElementById('total_guards').value),
                rate_per_guard: parseFloat(document.getElementById('rate_guard').value),
                rate_per_supervisor: parseFloat(document.getElementById('rate_super').value)
            },
            accessories_grid: Array.from(document.querySelectorAll('.grid-row')).map(row => ({
                weapon_type_id: parseInt(row.querySelector('.row-type').value),
                body_number: row.querySelector('.row-serial').value,
                ammunition_count: parseInt(row.querySelector('.row-ammo').value),
                shirt_qty: parseInt(row.querySelector('.row-shirt').value),
                pants_qty: parseInt(row.querySelector('.row-pants').value),
                cap_qty: parseInt(row.querySelector('.row-cap').value),
                shoes_qty: parseInt(row.querySelector('.row-shoes').value),
                belt_qty: parseInt(row.querySelector('.row-belt').value),
                jersey_qty: parseInt(row.querySelector('.row-jersey').value),
                remarks: row.querySelector('.row-remarks').value
            }))
        };

        try {
            const res = await fetch('api/api_router.php?action=deploy', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify(payload) 
            });
            const data = await res.json();
            if(data.status === 'success') { 
                alert("SUCCESS: Deployment authorized."); 
                location.reload(); 
            } else { 
                alert("ERROR: " + data.message); 
            }
        } catch(e) { 
            alert("CRITICAL_NETWORK_FAILURE: Connection to API lost."); 
        } finally { 
            btn.disabled = false; 
            btn.innerText = "AUTHORIZE_DEPLOYMENT_COMMIT"; 
        }
    };
    window.onload = () => { this_add_row(); document.getElementById('log_date').valueAsDate = new Date(); };
</script>

<?php include 'includes/footer.php'; ?>
