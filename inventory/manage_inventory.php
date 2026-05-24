<?php
/**
 * MANAGE INVENTORY - FAST Security Interface
 */
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Inventory Management</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px;">
                <h1 style="letter-spacing: 4px; color: var(--text-primary);">ASSET_INVENTORY_MANAGEMENT</h1>
                <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Real-time Asset Scoping and Table Rendering</p>
            </header>

            <div class="glass-panel" style="margin-bottom: 30px;">
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: flex-end;">
                    <div class="input-group">
                        <label>SEARCH ASSET</label>
                        <input type="text" id="asset-search" class="glass-input" placeholder="Enter Item Name or SKU...">
                    </div>
                    <div class="input-group">
                        <label>FILTER BY CATEGORY</label>
                        <select id="filter-category" class="glass-input">
                            <option value="all">ALL_CATEGORIES</option>
                            <option value="operational">OPERATIONAL</option>
                            <option value="apparel">APPAREL</option>
                            <option value="office">OFFICE</option>
                        </select>
                    </div>
                    <button class="btn-fast btn-primary" onclick="this_refresh_inventory()">REFRESH_DATA</button>
                </div>
            </div>

            <div class="table-container glass-panel" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>ITEM NAME</th>
                            <th>CATEGORY</th>
                            <th>TYPE</th>
                            <th>STOCK</th>
                            <th>UNIT COST</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-list-body">
                        <!-- Data populated via fetch -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="assets/js/manage_inventory.js"></script>
</body>
</html>
