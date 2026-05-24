<?php
/**
 * ADVANCED ARMORY PROCUREMENT - Batch & Compliance Engine
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Advanced Armory Procurement</title>
    <link rel="stylesheet" href="../assets/css/tactical_core.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .unit-row { background: rgba(255,255,255,0.03); border: 1px solid var(--border-frost); padding: 20px; margin-bottom: 15px; border-radius: 4px; position: relative; }
        .remove-unit { position: absolute; top: 10px; right: 10px; color: #ff3e3e; cursor: pointer; }
        .compliance-warning { border-color: var(--alert-orange) !important; }
    </style>
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="letter-spacing: 4px; color: var(--text-primary);">ADVANCED_ARMORY_INGESTION</h1>
                    <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Atomic Batch Processing & Compliance Filter</p>
                </div>
                <div class="glass-panel" style="padding: 10px 20px; font-size: 0.7rem; color: var(--text-dim);">
                    STATUS: <span style="color: var(--accent-cyan);">ENCRYPTED_LINK_ACTIVE</span>
                </div>
            </header>

            <div id="batch-interface">
                <!-- 01. MASTER DEFINITION -->
                <div class="glass-panel" style="padding: 30px; margin-bottom: 30px;">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px; font-size: 0.9rem;"><i class="fa-solid fa-microchip"></i> 01. MASTER_MODEL_DEFINITION</h3>
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div class="input-group">
                            <label>MODEL NAME</label>
                            <input type="text" id="master_model" class="glass-input" placeholder="e.g. Glock 17 Gen 5" required>
                        </div>
                        <div class="input-group">
                            <label>CALIBER / BORE</label>
                            <input type="text" id="master_caliber" class="glass-input" placeholder="e.g. 9mm" required>
                        </div>
                        <div class="input-group">
                            <label>MANUFACTURER</label>
                            <input type="text" id="master_manufacturer" class="glass-input" placeholder="e.g. Glock Ges.m.b.H." required>
                        </div>
                    </div>
                </div>

                <!-- 02. HARDWARE MANIFEST -->
                <div class="glass-panel" style="padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3 style="color: var(--accent-cyan); font-size: 0.9rem;"><i class="fa-solid fa-list-check"></i> 02. HARDWARE_UNIT_MANIFEST</h3>
                        <button class="btn-fast btn-secondary" onclick="this_add_unit_row()" style="font-size: 0.7rem;">+ ADD_UNIT_ENTRY</button>
                    </div>

                    <div id="unit-container">
                        <!-- Units dynamically added here -->
                    </div>

                    <div style="margin-top: 40px; border-top: 1px solid var(--border-frost); padding-top: 30px; display: flex; justify-content: flex-end;">
                        <button onclick="this_submit_batch()" id="submit-btn" class="btn-fast btn-primary" style="padding: 15px 40px;">AUTHORIZE_ATOMIC_COMMIT</button>
                    </div>
                </div>
            </div>

            <!-- SUCCESS OVERLAY -->
            <div id="success-overlay" class="glass-panel" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 400px; padding: 40px; text-align: center; z-index: 1000; border-color: var(--accent-cyan);">
                <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: var(--accent-cyan); margin-bottom: 20px;"></i>
                <h2 style="letter-spacing: 2px;">COMMIT_SUCCESS</h2>
                <p id="success-msg" style="color: var(--text-dim); margin: 20px 0; font-size: 0.8rem;"></p>
                <button onclick="location.reload()" class="btn-fast btn-secondary">INITIALIZE_NEW_BATCH</button>
            </div>

        </main>
    </div>

    <script src="../assets/js/theme_controller.js"></script>
    <script>
        let unit_count = 0;

        function this_add_unit_row() {
            unit_count++;
            const container = document.getElementById('unit-container');
            const row = document.createElement('div');
            row.className = 'unit-row';
            row.id = `unit-row-${unit_count}`;
            row.innerHTML = `
                <div class="remove-unit" onclick="this_remove_unit(${unit_count})"><i class="fa-solid fa-trash"></i></div>
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="input-group">
                        <label>SERIAL NUMBER</label>
                        <input type="text" class="glass-input serial-input" placeholder="UNIQUE_ID" required>
                    </div>
                    <div class="input-group">
                        <label>LICENSE NUMBER</label>
                        <input type="text" class="glass-input license-input" placeholder="REG_NO" required>
                    </div>
                    <div class="input-group">
                        <label>EXPIRY DATE</label>
                        <input type="date" class="glass-input expiry-input" onchange="this_check_compliance(this)" required>
                    </div>
                </div>
            `;
            container.appendChild(row);
        }

        function this_remove_unit(id) {
            const row = document.getElementById(`unit-row-${id}`);
            if (row) row.remove();
        }

        function this_check_compliance(input) {
            const expiry = new Date(input.value);
            const today = new Date();
            const diff = (expiry - today) / (1000 * 60 * 60 * 24);
            const row = input.closest('.unit-row');
            
            if (diff < 30) {
                row.classList.add('compliance-warning');
                row.style.border = '1px solid var(--alert-orange)';
            } else {
                row.classList.remove('compliance-warning');
                row.style.border = '1px solid var(--border-frost)';
            }
        }

        async function this_submit_batch() {
            const btn = document.getElementById('submit-btn');
            const master = {
                model_name: document.getElementById('master_model').value,
                caliber: document.getElementById('master_caliber').value,
                manufacturer: document.getElementById('master_manufacturer').value
            };

            const unit_rows = document.querySelectorAll('.unit-row');
            const units = Array.from(unit_rows).map(row => ({
                serial_number: row.querySelector('.serial-input').value,
                license_number: row.querySelector('.license-input').value,
                license_expiry: row.querySelector('.expiry-input').value
            }));

            if (!master.model_name || units.length === 0) {
                alert("ERROR: Master definition or units missing.");
                return;
            }

            btn.innerText = "ATOMIC_COMMIT_IN_PROGRESS...";
            btn.disabled = true;

            try {
                const response = await fetch('../api/api_router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        master_record: master,
                        unit_manifest: units
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    document.getElementById('success-msg').innerText = `${result.payload.total_processed} Units successfully registered and cross-linked with master definition.`;
                    document.getElementById('success-overlay').style.display = 'block';
                    document.getElementById('batch-interface').style.filter = 'blur(5px)';
                    document.getElementById('batch-interface').style.pointerEvents = 'none';
                } else {
                    alert(`COMMIT_REJECTED: ${result.message}`);
                }
            } catch (err) {
                alert("CRITICAL_SYSTEM_ERROR: Connection to Armory API lost.");
            } finally {
                btn.innerText = "AUTHORIZE_ATOMIC_COMMIT";
                btn.disabled = false;
            }
        }

        // Initialize with one row
        window.onload = this_add_unit_row;
    </script>
</body>
</html>
