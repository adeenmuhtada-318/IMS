<?php
/**
 * SECURE_IMS | AUTHORIZATION GATEWAY
 */
session_start();
require_once '../public_html/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operator_id = $_POST['operator_id'] ?? '';
    $pass_key    = $_POST['pass_key'] ?? '';

    // SECURE_AUDIT: Using PDO Prepared Statements to prevent SQL Injection
    $stmt = $pdo_conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$operator_id]);
    $user = $stmt->fetch();

    // Verify Hash against Tactical Secret
    if ($user && password_verify($pass_key, $user['password_hash'])) {
        session_regenerate_id(true); // Prevent Session Fixation
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_role'] = $user['user_role'];
        
        // Update Last Login Trace
        $pdo_conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?")
                 ->execute([$user['user_id']]);

        header("Location: ../public_html/dashboard.php");
        exit;
    } else {
        $error = "ACCESS_DENIED: INVALID_OPERATOR_SIGNATURE";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECURE_IMS | Authorization Required</title>
    <link rel="stylesheet" href="../public_html/assets/css/tactical.css">
    <style>
        body { background: #020406; color: #00fbff; font-family: 'Courier New', monospace; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #0a0d11; border: 1px solid #1a2228; padding: 40px; width: 350px; box-shadow: 0 0 30px rgba(0, 251, 255, 0.05); }
        .input-tactical { background: #020406; border: 1px solid #1a2228; color: #00fbff; width: 100%; padding: 12px; margin-top: 10px; font-family: inherit; }
        .btn-authorize { background: transparent; border: 1px solid #00fbff; color: #00fbff; width: 100%; padding: 12px; margin-top: 30px; cursor: pointer; text-transform: uppercase; }
        .btn-authorize:hover { background: #00fbff; color: #020406; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="margin-bottom: 30px; text-align: center; letter-spacing: 3px;">AUTH_REQUIRED</h2>
        <?php if(isset($error)): ?>
            <p style="color: #ff3e3e; font-size: 0.8rem; border: 1px solid #ff3e3e; padding: 10px;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>OPERATOR_ID</label>
            <input type="text" name="operator_id" class="input-tactical" required autocomplete="off">
            
            <div style="margin-top: 20px;">
                <label>PASS_KEY</label>
                <input type="password" name="pass_key" class="input-tactical" required>
            </div>
            
            <button type="submit" class="btn-authorize">INITIALIZE_SESSION</button>
        </form>
    </div>
</body>
</html>
