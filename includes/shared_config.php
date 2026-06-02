<?php
/**
 * IMS CORE CONFIGURATION - The System Heart
 * Location: C:/xampp/htdocs/IMS/includes/shared_config.php
 */

// 1. PATHING DEFINITIONS
define('ROOT_DIR', 'C:/xampp/htdocs/IMS');
define('BASE_URL', '/IMS');

// 2. GLOBAL ERROR SHIELDING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 3. DATABASE BRIDGE (PDO)
$host = 'localhost';
$db   = 'SecurityFirm_Inventory';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("SYSTEM_FATAL_ERROR: Database link failed. " . $e->getMessage());
}
?>
