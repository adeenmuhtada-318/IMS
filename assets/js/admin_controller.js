/**
 * ADMIN CONTROLLER - User Management Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    this_load_users();
    
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addUserForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'create_user';

            try {
                const response = await fetch('../api/api_router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert('User created successfully');
                    closeAddUserModal();
                    this_load_users();
                    addUserForm.reset();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('Connection error');
            }
        });
    }
});

async function this_load_users() {
    const tbody = document.getElementById('user-list-body');
    if (!tbody) return;

    try {
        const response = await fetch('../api/api_router.php?action=get_users');
        const users = await response.json();
        
        tbody.innerHTML = users.map(u => `
            <tr>
                <td style="font-weight: 600; color: var(--text-primary);">${u.username}</td>
                <td><span style="font-size: 0.75rem; background: rgba(0, 251, 255, 0.1); color: var(--accent-cyan); padding: 4px 8px; border-radius: 4px;">${u.user_role}</span></td>
                <td><span style="color: #22c55e; font-size: 0.8rem;">● Active</span></td>
                <td>
                    <button class="btn-fast btn-warn" style="padding: 5px 10px; font-size: 0.7rem;" onclick="suspendUser(${u.user_id})">Suspend</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--alert-orange);">Failed to load operators</td></tr>';
    }
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user?')) {
        alert('Action pending implementation');
    }
}
