<?php
/**
 * IMS TACTICAL LOGIN - Glassmorphism Edition
 */
session_start();

// If already authenticated, bypass login screen completely
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE_IMS | Authorization Required</title>
    <link rel="stylesheet" href="assets/css/glass_theme.css">
</head>
<body class="login-gateway">

    <div class="login-card glass-panel">
        <h2 style="margin-bottom: 30px; letter-spacing: 4px; color: var(--accent-cyan);">IMS_GATEWAY</h2>
        
        <div id="error-display" style="color: var(--alert-orange); font-size: 0.8rem; margin-bottom: 20px; display: none;"></div>

        <form id="login-form">
            <div class="input-group">
                <label>Operator_ID</label>
                <input type="text" name="username" class="glass-input" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label>Pass_Key</label>
                <input type="password" name="password" class="glass-input" required>
            </div>
            
            <button type="submit" class="btn-authorize" style="margin-top: 20px;">Authorize_Session</button>
        </form>

        <p style="margin-top: 40px; font-size: 0.65rem; color: var(--text-dim); letter-spacing: 1px;">
            ENCRYPTED_CONNECTION_SECURE
        </p>
    </div>

    <!-- Theme Toggle Hook -->
    <div class="theme-switch glass-panel" id="theme-toggle">
        🌓
    </div>

    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/login_auth.js"></script>
</body>
</html>
