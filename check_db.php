<?php
/**
 * SCHEMA DOCTOR - Database Debugger
 * Location: C:\xampp\htdocs\IMS\check_db.php
 */
require_once 'includes/connection.php';

echo "<h2>IMS Database Diagnostic</h2>";

function checkTable($pdo, $tableName) {
    echo "<h3>Table: $tableName</h3>";
    try {
        $q = $pdo->query("DESCRIBE $tableName");
        echo "<table border='1' style='border-collapse:collapse; width:100%; text-align:left;'>";
        echo "<tr style='background:#eee;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $q->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach($row as $val) echo "<td style='padding:5px;'>$val</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error: Table '$tableName' not found or inaccessible.</p>";
    }
}

checkTable($pdo, 'security_guards');
checkTable($pdo, 'payroll_rules');

echo "<p>--- End of Diagnostic ---</p>";
?>
