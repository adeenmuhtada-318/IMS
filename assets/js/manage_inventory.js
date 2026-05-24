/**
 * MANAGE INVENTORY CONTROLLER
 */

document.addEventListener('DOMContentLoaded', () => {
    this_refresh_inventory();
    
    // Search listener
    const searchInput = document.getElementById('asset-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            this_filter_table(e.target.value);
        });
    }
});

async function this_refresh_inventory() {
    const tbody = document.getElementById('inventory-list-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">SCANNING_RECORDS...</td></tr>';

    try {
        const response = await fetch('../api/api_router.php?action=get_inventory');
        const items = await response.json();
        this_render_inventory(items);
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="8" style="color:var(--fast-warn); text-align:center;">ERROR_FETCHING_INVENTORY</td></tr>';
    }
}

function this_render_inventory(items) {
    const tbody = document.getElementById('inventory-list-body');
    
    tbody.innerHTML = items.map(item => {
        const is_low = parseInt(item.current_stock) <= parseInt(item.min_threshold);
        return `
            <tr>
                <td style="color: var(--accent-cyan); font-weight:700;">${item.sku}</td>
                <td>${item.asset_name}</td>
                <td>${item.category_type.toUpperCase()}</td>
                <td style="font-size: 0.7rem; color: var(--text-dim);">${item.tracking_type.toUpperCase()}</td>
                <td style="color: ${is_low ? 'var(--alert-orange)' : 'var(--text-primary)'}">${item.current_stock}</td>
                <td>$${parseFloat(item.purchase_cost).toFixed(2)}</td>
                <td>
                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.65rem; background: ${is_low ? 'rgba(255,123,0,0.1)' : 'rgba(0,251,255,0.1)'}; color: ${is_low ? 'var(--alert-orange)' : 'var(--accent-cyan)'};">
                        ${is_low ? 'LOW_STOCK' : 'AVAILABLE'}
                    </span>
                </td>
                <td>
                    <button class="btn-fast btn-secondary" style="padding: 5px 10px; font-size: 0.65rem;" onclick="this_edit_item(${item.asset_id})">EDIT</button>
                    <button class="btn-fast btn-warn" style="padding: 5px 10px; font-size: 0.65rem;" onclick="this_decommission_item(${item.asset_id})">DEL</button>
                </td>
            </tr>
        `;
    }).join('');
}

function this_filter_table(query) {
    const rows = document.querySelectorAll('#inventory-list-body tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
    });
}

async function this_decommission_item(id) {
    if (!confirm("AUTHORIZE_ASSET_DECOMMISSION?")) return;
    const fd = new FormData();
    fd.append('action', 'decommission');
    fd.append('item_id', id);
    await fetch('../api/api_router.php', { method: 'POST', body: fd });
    this_refresh_inventory();
}
