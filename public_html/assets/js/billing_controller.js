/**
 * BILLING & ISSUANCE CONTROLLER
 */

document.addEventListener('DOMContentLoaded', () => {
    this_load_deployment_data();

    document.getElementById('issuance-form').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.append('action', 'deploy');
        
        const response = await fetch('api/asset_actions.php?action=deploy', { 
            method: 'POST', 
            body: fd 
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert("DEPLOYMENT_AUTHORIZED");
            this_load_deployment_data();
        } else {
            alert("DEPLOYMENT_REJECTED: " + result.message);
        }
    };
});

async function this_load_deployment_data() {
    // Load Guards
    const guard_select = document.getElementById('guard-select');
    const guard_res = await fetch('api_router.php?action=get_users');
    const guards = await guard_res.json();
    guard_select.innerHTML = guards.map(g => `<option value="${g.user_id}">${g.username.toUpperCase()}</option>`).join('');

    // Load Assets
    const asset_select = document.getElementById('asset-select');
    const ready_list = document.getElementById('ready-assets-list');
    const asset_res = await fetch('api_router.php?action=dashboard');
    const asset_data = await asset_res.json();
    
    asset_select.innerHTML = asset_data.items.map(i => `<option value="${item.asset_id}">${i.asset_name} (Stock: ${i.current_stock})</option>`).join('');
    
    ready_list.innerHTML = asset_data.items.slice(0, 5).map(i => `
        <tr>
            <td>${i.asset_name}</td>
            <td style="color: var(--fast-accent); font-weight:700;">${i.current_stock}</td>
            <td>$${parseFloat(i.purchase_cost).toFixed(2)}</td>
        </tr>
    `).join('');
}
