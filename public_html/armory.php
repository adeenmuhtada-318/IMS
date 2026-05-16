<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IMS | Tactical Armory Logs</title>
    <link rel="stylesheet" href="assets/css/tactical.css">
    <style>
        :root {
            --bg-deep: #020406;
            --bg-card: #0a0d11;
            --accent-cyan: #00fbff;
            --border-glow: #1a2228;
            --text-dim: #7a8a99;
        }
        body { background-color: var(--bg-deep); color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background: #05070a; border-right: 1px solid var(--border-glow); position: fixed; height: 100vh; width: 250px; }
        .main-content { margin-left: 250px; padding: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: var(--text-dim); border-bottom: 1px solid var(--border-glow); padding: 15px; font-weight: 300; }
        td { padding: 15px; border-bottom: 1px solid #0a0d11; font-size: 0.9rem; }
        .type-in { color: #00fbff; }
        .type-out { color: #ff3e3e; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2 style="color: var(--accent-cyan); padding: 20px;">SECURE_IMS</h2>
        <nav style="padding: 20px;">
            <a href="dashboard.php" style="display: block; color: var(--text-dim); text-decoration: none; margin-bottom: 15px;">DASHBOARD</a>
            <a href="armory.php" style="display: block; color: var(--accent-cyan); text-decoration: none; margin-bottom: 15px;">ARMORY_LOGS</a>
            <a href="../auth/logout.php" style="display: block; color: #ff3e3e; text-decoration: none; margin-top: 50px;">TERMINATE_SESSION</a>
        </nav>
    </div>

    <div class="main-content">
        <header>
            <h1 style="letter-spacing: 2px;">ARMORY_AUDIT_TRAIL</h1>
            <p style="color: var(--text-dim);">Immutable records of every asset movement.</p>
        </header>

        <table>
            <thead>
                <tr>
                    <th>TIMESTAMP</th>
                    <th>ITEM_NAME</th>
                    <th>TYPE</th>
                    <th>QTY</th>
                    <th>OPERATOR</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody id="audit-table-body">
                <!-- Populated via AJAX -->
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const response = await fetch('api_router.php?action=get_audit_logs');
            const logs = await response.json();
            
            const tbody = document.getElementById('audit-table-body');
            tbody.innerHTML = logs.map(log => `
                <tr>
                    <td style="color: var(--text-dim);">${log.transaction_date}</td>
                    <td>${log.item_name}</td>
                    <td class="type-${log.transaction_type.toLowerCase()}">${log.transaction_type}</td>
                    <td>${log.quantity_changed}</td>
                    <td>${log.performed_by}</td>
                    <td style="font-style: italic; font-size: 0.8rem;">${log.remarks || '-'}</td>
                </tr>
            `).join('');
        });
    </script>
</body>
</html>
