<?php
/**
 * Module Node: Tactical_Database_Bridge
 * Architectural Scope: Database Bridge Connection Layer
 */

// Load secure environment parameters from the non-public root directory configuration file
$configPath = dirname(__DIR__, 1) . '/config.php';

if (!file_exists($configPath)) {
    // Fail silently without exposing system internal path configurations
    error_log("Critical System Error: Configuration file instance missing.");
    die("System Error: Core Database Bridge Failure.");
}

$settings = require $configPath;
$dbSettings = $settings['db'];

// Construct the Data Source Name string using robust utf8mb4 encoding rules
$dsn = "mysql:host=" . $dbSettings['host'] . ";dbname=" . $dbSettings['name'] . ";charset=" . $dbSettings['charset'];

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_TIMEOUT            => $dbSettings['timeout'],
    /* 
     * CRITICAL PERFORMANCE CONFIGURATION:
     * ATTR_EMULATE_PREPARES is forced to false to ensure native database engine tracking.
     * This protects the 3NF data tier against SQL Injection vectors at the native runtime level.
     */
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// If an SSL CA certificate is defined in the configuration, enforce encryption pathways
if (!empty($dbSettings['ssl_ca'])) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $dbSettings['ssl_ca'];
}

try {
    // Instantiate persistent secure connection bridge to SecurityFirm_Inventory
    $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
} catch (PDOException $e) {
    // Intercept hazardous raw diagnostic strings to prevent credential or internal configuration leaks
    error_log("Database Connection Failure Exception: " . $e->getMessage());
    die("System Error: Core Database Bridge Failure.");
}
