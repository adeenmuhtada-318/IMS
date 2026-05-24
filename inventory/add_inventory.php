<?php
/**
 * PROCUREMENT CENTER - Advanced Armory Procurement
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
        <h1>ADVANCED_ARMORY_INGESTION</h1>
    </header>

    <div class="glass-panel" style="padding: 25px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="input-group">
                <label>MODEL NAME</label>
                <input type="text" id="master_model" class="glass-input" placeholder="e.g. Glock 17">
            </div>
            <div class="input-group">
                <label>CALIBER</label>
                <input type="text" id="master_caliber" class="glass-input" placeholder="e.g. 9mm">
            </div>
            <div class="input-group">
                <label>MANUFACTURER</label>
                <input type="text" id="master_manufacturer" class="glass-input" placeholder="e.g. Glock Ges.m.b.H.">
            </div>
        </div>
    </div>

    <div class="glass-panel" style="padding: 25px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="color: var(--accent-cyan);">HARDWARE_MANIFEST</h3>
            <button class="btn-tactical" onclick="this_add_unit_row()" style="padding: 5px 15px;">+ ADD UNIT</button>
        </div>
        <div id="unit-container"></div>
        <button onclick="this_submit_batch()" id="submit-btn" class="btn-authorize" style="width:100%; margin-top:20px;">AUTHORIZE_ATOMIC_COMMIT</button>
    </div>
</div>

<style>
    .unit-row { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 15px; margin-bottom: 10px; position: relative; border-radius: 4px; }
    .remove-unit { position: absolute; top: 10px; right: 10px; color: var(--alert-orange); cursor: pointer; }
</style>

<script>
    let unit_count = 0;
    function this_add_unit_row() {
        unit_count++;
        const row = document.createElement('div');
        row.className = 'unit-row';
        row.id = `unit-row-${unit_count}`;
        row.innerHTML = `
            <div class="remove-unit" onclick="document.getElementById('unit-row-${unit_count}').remove()">
                <i class="fa-solid fa-trash"></i>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px;">
                <div class="input-group">
                    <label>SERIAL</label>
                    <input type="text" class="glass-input serial-input" placeholder="SERIAL">
                </div>
                <div class="input-group">
                    <label>LICENSE</label>
                    <input type="text" class="glass-input license-input" placeholder="LICENSE">
                </div>
                <div class="input-group">
                    <label>EXPIRY</label>
                    <input type="date" class="glass-input expiry-input">
                </div>
            </div>`;
        document.getElementById('unit-container').appendChild(row);
    }

    async function this_submit_batch() {
        const btn = document.getElementById('submit-btn'); 
        btn.disabled = true;
        btn.innerText = 'PROCESSING_ATOMIC_COMMIT...';

        const payload = {
            action: 'add_asset',
            master_record: { 
                model_name: document.getElementById('master_model').value, 
                caliber: document.getElementById('master_caliber').value, 
                manufacturer: document.getElementById('master_manufacturer').value 
            },
            unit_manifest: Array.from(document.querySelectorAll('.unit-row')).map(row => ({ 
                serial_number: row.querySelector('.serial-input').value, 
                license_number: row.querySelector('.license-input').value, 
                license_expiry: row.querySelector('.expiry-input').value 
            }))
        };

        try {
            const res = await fetch('api/api_router.php?action=add_asset', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify(payload) 
            });
            const data = await res.json();
            if(data.status === 'success') { 
                alert("SUCCESS: Batch committed to armory."); 
                location.reload(); 
            } else { 
                alert("ERROR: " + data.message); 
            }
        } catch(e) { 
            alert("CRITICAL_NETWORK_FAILURE: Connection to API lost."); 
        } finally { 
            btn.disabled = false; 
            btn.innerText = 'AUTHORIZE_ATOMIC_COMMIT';
        }
    }
    window.onload = this_add_unit_row;
</script>

<?php include 'includes/footer.php'; ?>
