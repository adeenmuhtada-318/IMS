/**
 * HISTORY CONTROLLER - End-to-End Gear Tracking
 */

document.addEventListener('DOMContentLoaded', () => {
    this_load_history();
});

async function this_load_history() {
    const tbody = document.getElementById('history-list-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">SCANNING_AUDIT_TRAIL...</td></tr>';

    try {
        const response = await fetch('../api/api_router.php?action=get_issuances');
        const issuances = await response.json();
        
        if (issuances.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color: var(--text-dim);">NO_GEAR_TRANSACTIONS_FOUND</td></tr>';
            return;
        }

        tbody.innerHTML = issuances.map(iss => `
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                <td>#ISS-${iss.issuance_id.toString().padStart(4, '0')}</td>
                <td style="color: var(--accent-cyan); font-weight: 700;">${iss.asset_name.toUpperCase()}</td>
                <td>${iss.guard_name.toUpperCase()}</td>
                <td>${iss.quantity}</td>
                <td>
                    <span style="background: ${this_get_status_color(iss.status)}; color: #000; padding: 3px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; display: inline-block;">
                        ${iss.status.toUpperCase()}
                    </span>
                </td>
                <td style="color: var(--alert-orange); font-size: 0.75rem;">${iss.expected_return_date || 'N/A'}</td>
                <td style="font-size: 0.75rem; color: var(--text-dim);">${new Date(iss.issued_at).toLocaleString()}</td>
                <td>
                    ${iss.status === 'active_duty' 
                        ? `<button class="btn-fast btn-primary" style="padding: 5px 10px; font-size: 0.6rem;" onclick="this_process_return(${iss.issuance_id})">COLLECT_GEAR</button>` 
                        : `<span style="font-size: 0.6rem; color: var(--text-dim);">ARCHIVED</span>`
                    }
                </td>
            </tr>
        `).join('');
    } catch (err) {
        console.error("HISTORY_SYNC_FAILURE", err);
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color: var(--alert-orange);">CRITICAL_HISTORY_SYNC_FAILURE</td></tr>';
    }
}

function this_get_status_color(status) {
    switch(status) {
        case 'active_duty': return '#00fbff'; // Cyan
        case 'returned_intact': return '#22c55e'; // Green
        case 'damaged_loss': return '#ff7b00'; // Orange
        case 'unaccounted_lost': return '#ef4444'; // Red
        default: return '#94a3b8';
    }
}

async function this_process_return(id) {
    const status = prompt("SELECT RETURN STATUS:\n1. returned_intact\n2. damaged_loss\n3. unaccounted_lost", "returned_intact");
    if (!status) return;

    const remarks = prompt("ENTER RETURN REMARKS:", "Processed by Armory Operator");
    
    const fd = new FormData();
    fd.append('action', 'collect');
    fd.append('issuance_id', id);
    fd.append('return_status', status);
    fd.append('remarks', remarks || '');

    try {
        const response = await fetch('../api/api_router.php', { method: 'POST', body: fd });
        const result = await response.json();

        if (result.status === 'success') {
            alert("GEAR_COLLECTION_FINALIZED: Stock levels updated.");
            this_load_history();
        } else {
            alert("COLLECTION_REJECTED: " + result.message);
        }
    } catch (err) {
        alert("CRITICAL_API_TIMEOUT");
    }
}
