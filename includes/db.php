<?php
/**
 * UNIFIED DATABASE CONNECTION
 * Centralized PDO layer with robust error handling.
 */

$host = '127.0.0.1';
$db   = 'security_ims_pro';
$user = 'root';
$pass = '11223344@'; // Updated with the password found in public_html/includes/db_connect.php
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
    // In production, log error and show a generic message
    error_log($e->getMessage());
    die("SYSTEM_ERROR: Database connection unavailable. Contact Administrator.");
}

// Global variable for backward compatibility where needed
$pdo_conn = $pdo;
$db_conn = $pdo;
