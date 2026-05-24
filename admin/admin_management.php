<?php
/**
 * ADMIN MANAGEMENT - User & System Control
 */
session_start();
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['Admin', 'Admin/CEO'])) {
    header("Location: ../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | User Management</title>
    <link rel="stylesheet" href="../assets/css/tactical_core.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px;">
                <h1 style="color: var(--text-primary);">User Management</h1>
                <p style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 500;">Manage system operators and security settings</p>
            </header>

            <div class="panel-grid">
                
                <section class="glass-panel" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border-frost);">
                        <h3 style="color: var(--accent-cyan); margin: 0;">System Operators</h3>
                        <button class="btn-fast btn-primary" style="padding: 8px 16px; font-size: 0.75rem;" onclick="openAddUserModal()">Add New User</button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="user-list-body">
                                <!-- Populated via fetch -->
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="glass-panel" style="padding: 30px;">
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">System Status</h3>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-frost); padding-bottom: 10px;">
                            <span style="color: var(--text-dim); font-size: 0.85rem;">API Gateway</span>
                            <span style="color: #22c55e; font-weight: 600; font-size: 0.8rem;">Online</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-dim); font-size: 0.85rem;">Security Protocol</span>
                            <span style="color: #22c55e; font-weight: 600; font-size: 0.8rem;">Active</span>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- Add User Modal (Placeholder) -->
    <div id="addUserModal" class="modal">
        <div class="modal-content glass-panel" style="width: 450px;">
            <span class="close-btn" onclick="closeAddUserModal()">&times;</span>
            <h2 style="color: var(--accent-cyan); margin-bottom: 25px;">Add System Operator</h2>
            <form id="add-user-form">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="glass-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="glass-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="glass-input">
                        <option value="Admin/CEO">Admin/CEO</option>
                        <option value="Operations Supervisor">Operations Supervisor</option>
                        <option value="Accountant">Accountant</option>
                    </select>
                </div>
                <button type="submit" class="btn-fast btn-primary" style="width: 100%; margin-top: 15px;">Register Operator</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin_controller.js"></script>
    <script>
        function openAddUserModal() { document.getElementById('addUserModal').style.display = 'block'; }
        function closeAddUserModal() { document.getElementById('addUserModal').style.display = 'none'; }
    </script>
</body>
</html>
