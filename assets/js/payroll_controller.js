/**
 * PAYROLL CONTROLLER - Financial Logic & Rendering
 */

document.addEventListener('DOMContentLoaded', () => {
    this_fetch_payroll_data();
});

async function this_fetch_payroll_data() {
    const tbody = document.getElementById('payroll-list-body');
    if (!tbody) return;
    
    try {
        const response = await fetch('../api/payroll_actions.php?action=get_data');
        const personnel = await response.json();
        
        if (personnel.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-dim);">NO_PERSONNEL_RECORDS_FOUND</td></tr>';
            return;
        }
        
        this_render_payroll_grid(personnel);
    } catch (err) {
        console.error("FINANCIAL_SYNC_FAILURE:", err);
        tbody.innerHTML = '<tr><td colspan="8" style="color: var(--alert-orange); text-align: center;">CRITICAL: Financial server offline.</td></tr>';
    }
}

function this_render_payroll_grid(guards) {
    const tbody = document.getElementById('payroll-list-body');
    let total_net = 0;
    let total_deductions = 0;

    tbody.innerHTML = guards.map(guard => {
        const base_salary = parseFloat(guard.base_salary || 0);
        const id_fine      = parseFloat(guard.lost_id_card_fines || 0);
        const negligence   = parseFloat(guard.shift_misconduct_fines || 0);
        const custom_fine  = parseFloat(guard.custom_client_penalties || 0);
        
        const uniform_fee = 500; 
        
        const deductions = uniform_fee + id_fine + negligence + custom_fine;
        const net_pay = base_salary - deductions;

        total_net += net_pay;
        total_deductions += deductions;

        return `
            <tr style="transition: transform 0.2s ease;">
                <td>#ID_${guard.guard_id.toString().padStart(3, '0')}</td>
                <td style="font-weight: 600;">${guard.full_name.toUpperCase()}</td>
                <td>PKR ${base_salary.toLocaleString()}</td>
                <td style="color: var(--alert-orange); font-weight: 700;">-PKR ${uniform_fee}</td>
                <td style="color: var(--alert-orange); font-weight: 700;">-PKR ${id_fine}</td>
                <td style="color: var(--alert-orange); font-weight: 700;">-PKR ${(negligence + custom_fine).toLocaleString()}</td>
                <td style="color: var(--accent-cyan); font-weight: 800; font-size: 1rem;">PKR ${net_pay.toLocaleString()}</td>
                <td>
                    <button class="btn-fast btn-primary" style="padding: 5px 12px; font-size: 0.65rem;" onclick="this_process_payment(${guard.guard_id})">RELEASE_FUNDS</button>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('summary-total-net').innerText = `PKR ${total_net.toLocaleString()}`;
    document.getElementById('summary-total-fines').innerText = `PKR ${total_deductions.toLocaleString()}`;
    document.getElementById('summary-guard-count').innerText = guards.length;
}

async function this_process_payment(id) {
    if(!confirm("AUTHORIZE_CASH_DISBURSEMENT? This will log a final financial transaction.")) return;
    
    const fd = new FormData();
    fd.append('guard_id', id);
    fd.append('action', 'release_payroll');

    try {
        const response = await fetch('../api/api_router.php', { method: 'POST', body: fd });
        const result = await response.json();

        if (result.status === 'success') {
            alert("PAYMENT_COMMITTED: Funds routed to Guard Signature.");
            this_fetch_payroll_data();
        } else {
            alert("PAYMENT_FAILED: " + result.message);
        }
    } catch (err) {
        alert("CRITICAL: API_SYNC_TIMEOUT");
    }
}

function this_generate_payslips() { window.print(); }
