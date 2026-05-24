<?php
/**
 * AUTHENTICATION DIAGNOSTIC TOOL
 * Checks database connectivity and validates user credentials.
 */

require_once 'includes/connection.php';

echo "<h2>IMS Auth Diagnostic</h2>";

try {
    // 1. Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->fetch()) {
        echo "[OK] 'users' table exists.<br>";
    } else {
        die("[ERROR] 'users' table missing. Run database/schema.sql first.");
    }

    // 2. Check for default admin user
    $username = 'ADMIN_SECURE';
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, user_role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "[OK] User '$username' found. Role: " . $user['user_role'] . "<br>";
        
        // 3. Test known passwords
        $passwords_to_test = ['password', 'Password@123', 'TACTICAL_2026'];
        $found = false;
        
        foreach ($passwords_to_test as $pass) {
            if (password_verify($pass, $user['password_hash'])) {
                echo "[SUCCESS] Password verified! The correct password is: <b>$pass</b><br>";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "[WARNING] Default admin password not recognized. Creating a fallback account...<br>";
            $fallback_user = 'OPERATOR_TEST';
            $fallback_pass = 'FAST_2026';
            $hash = password_hash($fallback_pass, PASSWORD_DEFAULT);
            
            $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, user_role) VALUES (?, ?, 'ADMIN')")
                ->execute([$fallback_user, $hash]);
            
            echo "[OK] Fallback user created: <b>$fallback_user</b> / <b>$fallback_pass</b><br>";
        }
    } else {
        echo "[ERROR] Admin user not found. Seeding database...<br>";
        // Seed if missing
        $hash = password_hash('Password@123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password_hash, user_role) VALUES (?, ?, 'Admin/CEO')")
            ->execute(['ADMIN_SECURE', $hash]);
        echo "[OK] User 'ADMIN_SECURE' created with password 'Password@123'.<br>";
    }

} catch (PDOException $e) {
    echo "[CRITICAL] Database Error: " . $e->getMessage();
}

echo "<br><a href='login.php'>Go to Login Page</a>";
