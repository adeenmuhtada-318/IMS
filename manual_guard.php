<?php
require_once __DIR__ . '/includes/db.php';
$sql = "INSERT INTO guards (guard_no, joining_date, full_name, father_name, cnic, dob, phone_number, base_salary) 
        VALUES ('G-TEST-1', '2026-01-01', 'Test Guard', 'Father', '11122-3334445-6', '1990-01-01', '03001234567', 25000)";
try {
    $pdo->exec($sql);
    echo "MANUAL_GUARD_INSERT_SUCCESS\n";
    $count = $pdo->query("SELECT COUNT(*) FROM guards")->fetchColumn();
    echo "GUARDS_IN_DB: $count\n";
} catch (Exception $e) {
    echo "MANUAL_GUARD_INSERT_FAILURE: " . $e->getMessage() . "\n";
}
