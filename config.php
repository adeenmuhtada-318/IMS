<?php
/**
 * System Core Configuration Options
 * Tactical Cyber-Dark IMS Parameters
 */

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'SecurityFirm_Inventory',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
        'timeout' => 5, // Connection timeout threshold in seconds
        'ssl_ca'  => null // Set path to SSL CA Certificate for production environments
    ]
];
