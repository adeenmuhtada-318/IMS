<?php
/**
 * ARMORY AUDIT TRAIL - HARDENED V5
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
        <div>
            <h1 style="letter-spacing: 4px; color: var(--text-primary);">ARMORY_AUDIT_TRAIL</h1>
            <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Immutable Records of Every Asset Movement</p>
        </div>
    </header>

    <div class="glass-panel" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>TIMESTAMP</th>
                    <th>ITEM_NAME</th>
                    <th>TYPE</th>
                    <th>QTY</th>
                    <th>OPERATOR</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody id="audit-table-body">
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px; font-size: 0.8rem; letter-spacing: 2px;">
                        LOADING_AUDIT_RECORDS...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const tbody = document.getElementById('audit-table-body');
        try {
            const response = await fetch('api/api_router.php?action=get_audit_logs');
            const logs = await response.json();

            if (!logs || logs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:var(--text-dim); padding:40px; font-size:0.8rem; letter-spacing:2px;">NO_AUDIT_RECORDS_FOUND</td></tr>`;
                return;
            }

            tbody.innerHTML = logs.map(log => `
                <tr>
                    <td style="color: var(--text-dim); font-size: 0.8rem;">${log.transaction_date ?? '-'}</td>
                    <td>${log.item_name ?? '-'}</td>
                    <td style="color: ${log.transaction_type === 'IN' ? 'var(--accent-cyan)' : '#ff3e3e'}; font-weight: 700; font-size: 0.75rem;">${log.transaction_type ?? '-'}</td>
                    <td>${log.quantity_changed ?? '-'}</td>
                    <td>${log.performed_by ?? '-'}</td>
                    <td style="font-style: italic; font-size: 0.8rem; color: var(--text-dim);">${log.remarks || '-'}</td>
                </tr>
            `).join('');
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ff3e3e; padding:40px; font-size:0.8rem; letter-spacing:2px;">SYSTEM_ERROR: Could not load audit records.</td></tr>`;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
