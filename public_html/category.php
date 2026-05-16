<?php
/**
 * CATEGORY MANAGEMENT - FAST Security Interface
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
    <title>FAST Security | Category Management</title>
    <link rel="stylesheet" href="assets/css/fast_security.css">
</head>
<body>

    <!-- UNIFIED TACTICAL SIDEBAR -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-layout">
        <header class="top-header">
            <div class="profile-widget">
                <div class="profile-name"><?php echo strtoupper($_SESSION['username']); ?></div>
                <div class="profile-avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
            </div>
        </header>

        <main class="container">
            <h1 class="page-title">CATEGORY_MANAGEMENT</h1>

            <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px;">
                <!-- ADD CATEGORY FORM -->
                <div class="glass-panel" style="background: var(--fast-card); padding: 30px; border-radius: 8px; border: 1px solid var(--fast-border);">
                    <h3 style="margin-bottom: 20px; color: var(--fast-accent);">REGISTER_NEW_CATEGORY</h3>
                    <form id="add-category-form">
                        <div class="input-group">
                            <label>CATEGORY NAME</label>
                            <input type="text" name="category_name" class="fast-input" required placeholder="e.g. Tactical Weapons">
                        </div>
                        <button type="submit" class="btn-fast btn-primary" style="width: 100%; margin-top: 20px;">AUTHORIZE_SAVE</button>
                    </form>
                </div>

                <!-- CATEGORY LIST -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>CATEGORY NAME</th>
                                <th>ASSETS COUNT</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="category-list-body">
                            <!-- Populated via fetch -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/category_manager.js"></script>
</body>
</html>
