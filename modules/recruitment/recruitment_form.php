<?php 
session_start();

// Capture dynamic alert flash states from active sessions
$success_message = isset($_SESSION['success_flash']) ? $_SESSION['success_flash'] : "";
$error_message = isset($_SESSION['error_flash']) ? $_SESSION['error_flash'] : "";

unset($_SESSION['success_flash']);
unset($_SESSION['error_flash']);

// Buffer and capture the dynamic sidebar execution markup safely
ob_start();
include '../../includes/sidebar.php';
$sidebar_markup = ob_get_clean();

// Fetch the clean decoupled UI HTML template file
$html_view = file_get_contents("views/form_template.html");

// Format layout message banners
$success_markup = "";
if (!empty($success_message)) {
    $success_markup = '<div class="AlertStatusBanner BannerSuccess">✔ ' . htmlspecialchars($success_message) . '</div>';
}

$error_markup = "";
if (!empty($error_message)) {
    $error_markup = '<div class="AlertStatusBanner BannerError">❌ ' . htmlspecialchars($error_message) . '</div>';
}

// Inject components dynamically into design tokens
$html_view = str_replace("{{GLOBAL_SIDEBAR}}", $sidebar_markup, $html_view);
$html_view = str_replace("{{SUCCESS_BANNER}}", $success_markup, $html_view);
$html_view = str_replace("{{ERROR_BANNER}}", $error_markup, $html_view);

// Render out the complete stabilized user interface
echo $html_view;
?>