/**
 * CATEGORY MANAGER CONTROLLER
 */

document.addEventListener('DOMContentLoaded', () => {
    this_load_categories();

    document.getElementById('add-category-form').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.append('action', 'add_category');
        
        await fetch('../api/api_router.php', { method: 'POST', body: fd });
        e.target.reset();
        this_load_categories();
    };
});

async function this_load_categories() {
    const tbody = document.getElementById('category-list-body');
    try {
        const response = await fetch('../api/api_router.php?action=get_categories');
        const cats = await response.json();
        
        tbody.innerHTML = cats.map(c => `
            <tr>
                <td>#${c.category_id}</td>
                <td style="color: var(--fast-accent); font-weight: 700;">${c.category_name.toUpperCase()}</td>
                <td>-</td>
                <td>
                    <button class="btn-fast btn-warn" style="padding: 5px 10px; font-size: 0.65rem;">DELETE</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="4">FAILED_TO_LOAD_CATEGORIES</td></tr>';
    }
}
