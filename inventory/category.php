<?php
/**
 * CATEGORY MANAGEMENT - FAST Security Interface
 */
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'includes/connection.php';
include 'includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <h1 class="page-title">CATEGORY_MANAGEMENT</h1>
    </header>

    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px;">
        <!-- ADD CATEGORY FORM -->
        <div class="glass-panel" style="padding: 30px;">
            <h3 style="margin-bottom: 20px; color: var(--accent-cyan);">REGISTER_NEW_CATEGORY</h3>
            <form id="add-category-form">
                <div class="input-group">
                    <label>CATEGORY NAME</label>
                    <input type="text" name="category_name" class="glass-input" required placeholder="e.g. Tactical Weapons">
                </div>
                <button type="submit" class="btn-authorize" style="width: 100%; margin-top: 20px;">AUTHORIZE_SAVE</button>
            </form>
        </div>

        <!-- CATEGORY LIST -->
        <div class="glass-panel" style="padding: 20px;">
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
    </div>
</div>

<script src="assets/js/category_manager.js"></script>
<?php include 'includes/footer.php'; ?>
