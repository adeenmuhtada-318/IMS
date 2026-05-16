/**
 * ADMIN CONTROLLER
 */

document.addEventListener('DOMContentLoaded', () => {
    this_load_users();
});

async function this_load_users() {
    const tbody = document.getElementById('user-list-body');
    try {
        const response = await fetch('api_router.php?action=get_users');
        const users = await response.json();
        
        tbody.innerHTML = users.map(u => `
            <tr>
                <td style="color: var(--fast-accent); font-weight: 700;">${u.username.toUpperCase()}</td>
                <td><span style="font-size: 0.65rem; border: 1px solid var(--fast-border); padding: 2px 5px;">${u.user_role}</span></td>
                <td><span style="color: #22c55e;">● ACTIVE</span></td>
                <td>
                    <button class="btn-fast btn-secondary" style="padding: 5px 10px; font-size: 0.65rem;">SUSPEND</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="4">ERROR_LOADING_USERS</td></tr>';
    }
}
