<?php
/**
 * GUARD PERFORMANCE AUDIT - Pre-Payroll Module
 * Optimized for local operations staff.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Performance Audit</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div class="dashboard-wrapper">
        <!-- UNIFIED TACTICAL SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN PERFORMANCE VIEW -->
        <main class="main-viewport">
            
            <!-- BRAND HEADER -->
            <header style="margin-bottom: 40px; display: flex; align-items: center; gap: 20px;">
                <div class="brand-logo" style="width: 50px; height: 50px; fill: var(--accent-cyan);">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                    </svg>
                </div>
                <div>
                    <h1 style="letter-spacing: 2px; color: var(--text-primary); font-weight: 800;">FAST SECURITY SERVICES</h1>
                    <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 4px; font-weight: 600;">PERFORMANCE_AUDIT_LOG</p>
                </div>
            </header>

            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 40px;">
                
                <!-- LEFT: PERFORMANCE FORM -->
                <section class="glass-panel" style="padding: 40px;">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 30px;">Monthly Performance Data Entry</h3>
                    
                    <form id="performance-form">
                        <!-- Guard Selection -->
                        <div class="input-group" style="margin-bottom: 30px;">
                            <label>Select Guard to Audit</label>
                            <select name="guard_id" id="guard-selector" class="glass-input" required style="width: 100%;">
                                <option value="">SCANNING_PERSONNEL_DATABASE...</option>
                            </select>
                        </div>

                        <!-- Attendance Section -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="input-group">
                                <label>Total Days Present on Duty</label>
                                <input type="number" name="total_present_days" class="glass-input" value="26" min="0" max="31" required>
                            </div>
                            <div class="input-group">
                                <label>Double Shifts Completed</label>
                                <input type="number" name="double_shifts_count" class="glass-input" value="0" min="0">
                            </div>
                        </div>

                        <!-- Penalty Section (Orange Accent) -->
                        <div class="glass-panel" style="background: rgba(234, 88, 12, 0.05); border: 1px solid var(--alert-orange); padding: 30px;">
                            <h4 style="color: var(--alert-orange); margin-bottom: 20px; font-size: 0.85rem; letter-spacing: 1px;">RULE VIOLATIONS & PENALTIES (PKR)</h4>
                            
                            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="input-group">
                                    <label>Lost Agency ID Card Incidents</label>
                                    <input type="number" name="lost_id_card_incidents" class="glass-input" value="0" style="border-color: var(--alert-orange);">
                                    <p style="font-size: 0.6rem; color: var(--text-dim); margin-top: 5px;">* Rule 6: 1,000 Fine per instance</p>
                                </div>
                                <div class="input-group">
                                    <label>Duty Sleep or Post Abandonment</label>
                                    <input type="number" name="shift_misconduct_incidents" class="glass-input" value="0" style="border-color: var(--alert-orange);">
                                    <p style="font-size: 0.6rem; color: var(--text-dim); margin-top: 5px;">* Rule 7: 500 Fine per instance</p>
                                </div>
                            </div>
                            
                            <div class="input-group" style="margin-top: 20px;">
                                <label>Other Direct Client Penalties (Amount)</label>
                                <input type="number" name="custom_client_penalties" class="glass-input" value="0.00" step="0.01" style="border-color: var(--alert-orange);">
                            </div>
                        </div>

                        <div class="input-group" style="margin-top: 30px;">
                            <label>Internal Auditor Remarks</label>
                            <textarea name="performance_notes" class="glass-input" style="height: 80px; resize: none;" placeholder="Provide context for penalties or exceptional performance..."></textarea>
                        </div>

                        <div style="margin-top: 40px; text-align: right;">
                            <button type="submit" class="btn-fast btn-primary" style="background: var(--accent-cyan); color: #020406; padding: 15px 35px;">
                                LOCK PERFORMANCE AND SEND TO PAYROLL
                            </button>
                        </div>
                    </form>
                </section>

                <!-- RIGHT: RECENT AUDITS LIST -->
                <section>
                    <h3 style="color: var(--text-primary); margin-bottom: 20px;">Recent Audit Submissions</h3>
                    <div id="recent-audits-list" style="display: flex; flex-direction: column; gap: 15px;">
                        <!-- Dynamically populated -->
                        <div class="glass-panel" style="padding: 20px; text-align: center; color: var(--text-dim);">
                            SCANNING_AUDIT_TRAIL...
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- THEME TOGGLE -->
    <div id="theme-toggle">🌓</div>

    <!-- SYSTEM SCRIPTS -->
    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/performance_controller.js"></script>
</body>
</html>
