<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/connection.php';
include '../includes/header.php';
?>
<div class="dashboard-viewport">
    <header class="tactical-header">
        <h1>VEHICLE_PATROL_FLEET</h1>
        <p style="color:var(--accent-cyan); font-size:0.75rem;">MODULE UNDER CONSTRUCTION</p>
    </header>
    <div class="glass-panel" style="padding:40px; text-align:center; color:var(--text-dim);">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:3rem; margin-bottom:20px; color:var(--alert-orange);"></i>
        <p style="letter-spacing:2px;">FLEET_MANAGEMENT_IS_BEING_INITIALIZED</p>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
