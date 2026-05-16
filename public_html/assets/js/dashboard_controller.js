/**
 * DASHBOARD CONTROLLER - Command Center Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    this_fetch_operational_summary();
});

async function this_fetch_operational_summary() {
    try {
        const response = await fetch('api_router.php?action=dashboard_stats');
        const stats = await response.json();
        
        document.getElementById('kpi-total-guards').innerText = stats.total_guards;
        document.getElementById('kpi-pending-checks').innerText = stats.pending_checks;
        document.getElementById('kpi-vault-weapons').innerText = stats.vault_weapons;
        document.getElementById('kpi-stock-alerts').innerText = stats.stock_alerts;

        this_fetch_recent_personnel();

    } catch (err) {
        console.error("DASHBOARD_SYNC_FAILURE", err);
    }
}

async function this_fetch_recent_personnel() {
    const tbody = document.getElementById('recent-guards-table');
    
    try {
        const response = await fetch('api_router.php?action=get_recent_guards');
        const guards = await response.json();

        if (guards.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-dim);">NO_RECORDS_FOUND</td></tr>';
            return;
        }

        tbody.innerHTML = guards.map(g => `
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                <td style="font-weight: 700;">${g.full_name.toUpperCase()}</td>
                <td style="color: var(--accent-cyan);">${g.guard_no}</td>
                <td>${g.district || 'N/A'}</td>
                <td style="color: var(--text-dim); font-size: 0.75rem;">${g.joining_date}</td>
            </tr>
        `).join('');

    } catch (err) {
        console.error("PERSONNEL_AUDIT_FAILURE", err);
        tbody.innerHTML = '<tr><td colspan="4">FAILED_TO_LOAD_PERSONNEL</td></tr>';
    }
}
