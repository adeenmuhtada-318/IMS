/**
 * FAST SECURITY - CORE ENGINE
 * Pure Vanilla JavaScript | No Frameworks
 */

let staged_items = [];

document.addEventListener('DOMContentLoaded', () => {
    this_load_categories();
    this_bind_forms();
});

/**
 * TOGGLE BETWEEN OFFICE AND OPERATIONAL FORMS
 */
function this_toggle_form(type) {
    const office_container = document.getElementById('office-form-container');
    const opera_container  = document.getElementById('operational-form-container');
    const tabs = document.querySelectorAll('.tab-btn');

    if (type === 'office') {
        office_container.style.display = 'block';
        opera_container.style.display = 'none';
        tabs[0].classList.add('active');
        tabs[1].classList.remove('active');
    } else {
        office_container.style.display = 'none';
        opera_container.style.display = 'block';
        tabs[0].classList.remove('active');
        tabs[1].classList.add('active');
    }
}

/**
 * FETCH CATEGORIES FROM EXISTING BACKEND
 */
async function this_load_categories() {
    try {
        const response = await fetch('api_router.php?action=get_categories');
        const categories = await response.json();
        
        const office_select = document.getElementById('office-cat-select');
        const opera_select  = document.getElementById('operational-cat-select');

        const html = categories.map(c => `<option value="${c.category_id}">${c.category_name}</option>`).join('');
        
        if (office_select) office_select.innerHTML = html;
        if (opera_select) opera_select.innerHTML = html;
    } catch (err) {
        console.error("CAT_LOAD_FAILURE:", err);
    }
}

/**
 * BIND FORM SUBMISSIONS TO STAGING LOGIC
 */
function this_bind_forms() {
    const forms = ['office-asset-form', 'operational-asset-form'];
    
    forms.forEach(form_id => {
        const form = document.getElementById(form_id);
        if (!form) return;

        form.onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            const item = Object.fromEntries(fd.entries());
            
            // Map category name for table display
            const cat_select = form.querySelector('select');
            item.category_name = cat_select.options[cat_select.selectedIndex].text;
            
            this_stage_item(item);
            form.reset();
        };
    });
}

/**
 * STAGE ITEM INTO THE TEMPORARY GRID
 */
function this_stage_item(item) {
    staged_items.push(item);
    this_render_staging_table();
}

function this_render_staging_table() {
    const tbody = document.getElementById('staging-table-body');
    if (!tbody) return;

    tbody.innerHTML = staged_items.map((item, index) => `
        <tr onclick="this_select_row(${index})">
            <td>${item.sku_code}</td>
            <td>${item.item_name}</td>
            <td>${item.barcode || 'N/A'}</td>
            <td>${item.category_name}</td>
            <td>$${parseFloat(item.rate || 0).toFixed(2)}</td>
            <td>$${parseFloat(item.unit_price || 0).toFixed(2)}</td>
            <td>${item.quantity}</td>
        </tr>
    `).join('');

    this_update_summary();
}

/**
 * CALCULATE TOTALS
 */
function this_update_summary() {
    let total_qty = 0;
    let total_rate = 0;
    let grand_total = 0;

    staged_items.forEach(item => {
        const qty = parseInt(item.quantity || 0);
        const rate = parseFloat(item.rate || 0);
        const price = parseFloat(item.unit_price || 0);

        total_qty += qty;
        total_rate += rate;
        grand_total += (qty * price);
    });

    document.getElementById('total-items').innerText = staged_items.length;
    document.getElementById('total-rate').innerText = `$${total_rate.toFixed(2)}`;
    document.getElementById('total-qty').innerText = total_qty;
    document.getElementById('grand-total').innerText = `$${grand_total.toFixed(2)}`;
}

/**
 * SAVE STAGED DATA TO BACKEND
 */
async function this_save_bulk() {
    if (staged_items.length === 0) {
        alert("STAGING_EMPTY: Add items first.");
        return;
    }

    if (!confirm(`AUTHORIZE_SAVE: Committing ${staged_items.length} assets to database.`)) return;

    // We loop and call the existing 'add' action for each item to preserve backend logic
    for (const item of staged_items) {
        const fd = new FormData();
        for (const [key, value] of Object.entries(item)) {
            fd.append(key, value);
        }

        try {
            await fetch('api_router.php?action=add', {
                method: 'POST',
                body: fd
            });
        } catch (err) {
            console.error("SAVE_FAILED_FOR:", item.sku_code);
        }
    }

    alert("DATA_SYNCHRONIZED: Inventory updated successfully.");
    staged_items = [];
    this_render_staging_table();
}

function this_delete_staged() {
    staged_items.pop(); // Simplistic delete last for this view
    this_render_staging_table();
}

function this_reset_form(type) {
    document.getElementById(`${type}-asset-form`).reset();
}

function this_print() {
    window.print();
}
