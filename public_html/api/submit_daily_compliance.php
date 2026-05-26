<?php
header("Content-Type: application/json");
require_once dirname(__DIR__) . '/includes/db.php'; // Absolute Parent Tree Resolution

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['guard_id']) || !isset($data['log_date']) || !isset($data['attendance_status'])) {
        echo json_encode(["status" => "ERROR", "message" => "Mandatory compliance fields are empty."]);
        exit;
    }

    try {
        $sql = "INSERT INTO Guard_Daily_Compliance 
                (Guard_ID, Log_Date, Attendance_Status, Overtime_Shift, Violation_Uniform, Violation_Weapon, Violation_Late, Violation_Behavior) 
                VALUES (:guard_id, :log_date, :attendance_status, :overtime_shift, :v_uniform, :v_weapon, :v_late, :v_behavior)
                ON DUPLICATE KEY UPDATE 
                Attendance_Status = :attendance_status, Overtime_Shift = :overtime_shift,
                Violation_Uniform = :v_uniform, Violation_Weapon = :v_weapon, 
                Violation_Late = :v_late, Violation_Behavior = :v_behavior";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':guard_id'          => $data['guard_id'],
            ':log_date'          => $data['log_date'],
            ':attendance_status' => $data['attendance_status'],
            ':overtime_shift'    => $data['overtime_shift'] ?? 'No',
            ':v_uniform'         => $data['violation_uniform'] ? 1 : 0,
            ':v_weapon'          => $data['violation_weapon'] ? 1 : 0,
            ':v_late'            => $data['violation_late'] ? 1 : 0,
            ':v_behavior'        => $data['violation_behavior'] ? 1 : 0
        ]);

        echo json_encode(["status" => "SUCCESS", "message" => "Daily metrics safely committed to ledger."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "CONFLICT", "message" => "Database pipeline break: " . $e->getMessage()]);
    }
}
?>