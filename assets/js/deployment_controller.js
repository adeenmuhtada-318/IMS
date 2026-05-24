/**
 * DEPLOYMENT CONTROLLER - Tactical Dispatch Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_deployment_engine();
});

function this_initialize_deployment_engine() {
    this_load_dispatch_data();
    this_fetch_active_duty_logs();

    const d_form = document.getElementById('deployment-form');
    if (d_form) {
        d_form.onsubmit = async (e) => {
            e.preventDefault();
            this_execute_dispatch(d_form);
        };
    }
}

async function this_load_dispatch_data() {
    try {
        const guard_res = await fetch('../api/api_router.php?action=get_guards');
        const guards = await guard_res.json();
        const guard_select = document.getElementById('guard-select');
        
        guard_select.innerHTML = '<option value="">SELECT_AUTHORIZED_PERSONNEL</option>' + 
            guards.map(g => `<option value="${g.guard_id}">${g.guard_no} | ${g.full_name.toUpperCase()}</option>`).join('');

        const asset_res = await fetch('../api/api_router.php?action=get_inventory');
        const assets = await asset_res.json();
        const asset_select = document.getElementById('asset-select');
        
        asset_select.innerHTML = '<option value="">SELECT_OPERATIONAL_ASSET</option>' + 
            assets.filter(i => i.current_stock > 0).map(i => 
                `<option value="${i.asset_id}">${i.sku} | ${i.asset_name.toUpperCase()} (STOCK: ${i.current_stock})</option>`
            ).join('');

    } catch (err) {
        console.error("DISPATCH_DATA_SYNC_FAILURE", err);
    }
}

async function this_execute_dispatch(form_element) {
    const btn = form_element.querySelector('button[type="submit"]');
    const original_text = btn.innerText;
    
    btn.innerText = 'AUTHORIZING_DISPATCH...';
    btn.disabled = true;

    const fd = new FormData(form_element);
    fd.append('action', 'deploy');

    try {
        const response = await fetch('../api/api_router.php', {
            method: 'POST',
            body: fd
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert("DISPATCH_AUTHORIZED: Personnel and equipment deployed to site.");
            form_element.reset();
            this_load_dispatch_data();
            this_fetch_active_duty_logs();
        } else {
            alert("DISPATCH_REJECTED: " + result.message);
        }

    } catch (err) {
        console.error("DEPLOYMENT_GATEWAY_FAILURE", err);
        alert("CRITICAL: Network isolation from deployment hub.");
    } finally {
        btn.innerText = original_text;
        btn.disabled = false;
    }
}

async function this_fetch_active_duty_logs() {
    const list_area = document.getElementById('active-duty-list');
    
    try {
        const response = await fetch('../api/api_router.php?action=get_issuances');
        const issuances = await response.json();

        list_area.innerHTML = issuances.filter(iss => iss.status === 'active_duty').map(iss => `
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: all 0.3s;" class="active-deployment-row">
                <td style="padding: 15px 10px; color: var(--text-dim);">#${iss.guard_id}</td>
                <td style="padding: 15px 10px; font-weight: 600;">${iss.guard_name.toUpperCase()}</td>
                <td style="padding: 15px 10px; color: var(--accent-cyan);">${iss.asset_name}</td>
                <td style="padding: 15px 10px; font-size: 0.75rem;">${iss.deployment_location || 'FIELD_SITE'}</td>
                <td style="padding: 15px 10px; color: var(--alert-orange); font-size: 0.75rem;">${iss.expected_return_date}</td>
                <td style="padding: 15px 10px;">
                    <span style="border: 1px solid var(--accent-cyan); color: var(--accent-cyan); padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; box-shadow: 0 0 10px rgba(0,251,255,0.1);">
                        ACTIVE_DUTY
                    </span>
                </td>
            </tr>
        `).join('');

        if (list_area.innerHTML === '') {
            list_area.innerHTML = '<tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-dim);">NO_ACTIVE_DEPLOYMENTS_REPORTED</td></tr>';
        }

    } catch (err) {
        console.error("LOG_SYNC_FAILURE", err);
    }
}
