<?php
/**
 * IMS TACTICAL LOGIN - Glassmorphism Edition
 */
session_start();

// PREVENT AUTHENTICATED PAGE CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// GENERATE CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If already authenticated, bypass login screen completely
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE_IMS | Authorization Required</title>
    <link rel="stylesheet" href="assets/css/glass_theme.css">
</head>
<body class="login-gateway dark-theme">

    <div class="login-card glass-panel">
        <h2 class="gateway-title">IMS LOGIN</h2>
        
        <div id="error-display" class="error-msg hidden"></div>

        <form id="login-form">
            <!-- CSRF PROTECTION -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="input-group">
                <label for="op_id">Operator ID</label>
                <input type="text" id="op_id" name="username" class="glass-input" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <label for="pass_key">Password</label>
                <input type="password" id="pass_key" name="password" class="glass-input" placeholder="••••••••" required>
            </div>
            
            <button type="submit" id="btn-authorize" class="btn-authorize">Login to Dashboard</button>
        </form>

        <a href="#" class="forgot-password" onclick="alert('Please contact the System Administrator for credential recovery.')">
            Forgot Password?
        </a>

        <p class="encryption-tag">
            SECURE ENCRYPTED CONNECTION
        </p>
    </div>

    <!-- Theme Toggle Hook -->
    <div class="theme-switch glass-panel" id="theme-toggle">
        🌓
    </div>

    <style>
    /* LOCAL OVERRIDES FOR DECALRATIVE CLEANUP */
    .gateway-title { margin-bottom: 30px; letter-spacing: 4px; color: var(--accent-cyan); }
    .error-msg { color: var(--alert-orange); font-size: 0.8rem; margin-bottom: 20px; }
    .encryption-tag { margin-top: 40px; font-size: 0.65rem; color: var(--text-dim); letter-spacing: 1px; }
    </style>

    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/login_auth.js"></script>
</body>
</html>
