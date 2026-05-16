<?php
/**
 * LOGOUT ENGINE - Session Termination
 */

session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the server-side session
session_destroy();

/**
 * RATIONALE:
 * Immediate redirect to the login gateway to prevent 
 * browser cache access to the dashboard.
 */
header("Location: ../public_html/login.php"); // Adjust path to your login page
exit;
