/**
 * PERFORMANCE CONTROLLER - Audit & Penalty Handshake
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_performance_engine();
});

function this_initialize_performance_engine() {
    this_load_guard_options();
    this_fetch_audit_history();

    const p_form = document.getElementById('performance-form');
    if (p_form) {
        p_form.onsubmit = async (e) => {
            e.preventDefault();
            this_submit_performance_audit(p_form);
        };
    }
}

/**
 * DATA LOADER - Syncs Personnel for Audit
 */
async function this_load_guard_options() {
    const select = document.getElementById('guard-selector');
    try {
        const response = await fetch('../api/api_router.php?action=get_guards');
        const guards = await response.json();
        
        select.innerHTML = '<option value="">SELECT_GUARD_FOR_AUDIT</option>' + 
            guards.map(g => `<option value="${g.guard_id}">${g.guard_no} | ${g.full_name.toUpperCase()}</option>`).join('');

    } catch (err) {
        console.error("PERSONNEL_SYNC_FAILURE", err);
    }
}

/**
 * ASYNCHRONOUS AUDIT SUBMISSION
 */
async function this_submit_performance_audit(form_element) {
    const btn = form_element.querySelector('button[type="submit"]');
    const original_text = btn.innerText;
    
    btn.innerText = 'COMMITTING_AUDIT_PARAMETERS...';
    btn.disabled = true;

    const fd = new FormData(form_element);
    
    try {
        const response = await fetch('../api/performance_actions.php?action=save', {
            method: 'POST',
            body: fd
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert("AUDIT_LOCKED: Performance metrics routed to Payroll Module.");
            form_element.reset();
            this_fetch_audit_history();
        } else {
            alert("AUDIT_REJECTED: " + (result.message || 'Verification failure.'));
        }

    } catch (err) {
        console.error("PERFORMANCE_GATEWAY_FAILURE", err);
        alert("CRITICAL: Network isolation from audit server.");
    } finally {
        btn.innerText = original_text;
        btn.disabled = false;
    }
}

/**
 * FETCH AUDIT HISTORY
 */
async function this_fetch_audit_history() {
    const history_area = document.getElementById('recent-audits-list');
    
    try {
        const response = await fetch('../api/performance_actions.php?action=history');
        const history = await response.json();

        history_area.innerHTML = history.slice(0, 10).map(item => `
            <div class="glass-panel" style="padding: 20px; border-left: 3px solid var(--accent-cyan); margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <span style="font-weight: 700; color: var(--text-primary);">${item.full_name.toUpperCase()}</span>
                    <span style="font-size: 0.65rem; color: var(--text-dim);">${item.billing_month}</span>
                </div>
                <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-dim);">
                    Duty Days: <b style="color: var(--accent-cyan);">${item.total_present_days}</b> | 
                    Total Fines: <b style="color: var(--alert-orange);">${(parseFloat(item.lost_id_card_fines) + parseFloat(item.shift_misconduct_fines) + parseFloat(item.custom_client_penalties)).toLocaleString()} PKR</b>
                </div>
            </div>
        `).join('');

        if (history_area.innerHTML === '') {
            history_area.innerHTML = '<div class="glass-panel" style="padding: 20px; text-align: center; color: var(--text-dim);">NO_RECENT_AUDITS_REPORTED</div>';
        }

    } catch (err) {
        console.error("AUDIT_TRAIL_FAILURE", err);
    }
}
