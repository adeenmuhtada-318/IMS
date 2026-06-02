<?php
/**
 * ATTENDANCE DATA MUTATOR
 * Processes batch updates for security personnel.
 */
session_start();
require_once '../../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shiftType = $_POST['shift_type'] ?? 'Day';
    $logDate   = date('Y-m-d');
    $attendanceData = $_POST['attendance'] ?? []; 
    $remarksData    = $_POST['remarks'] ?? [];

    if (empty($attendanceData)) {
        $_SESSION['flash_error'] = "SUBMISSION_REJECTED: No personnel data identified in POST buffer.";
        header("Location: manage_attendance.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO attendance_logs (badge_number, date, status, shift_type, remarks) 
                VALUES (:badge, :date, :status, :shift, :remarks)
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                remarks = VALUES(remarks)";
        
        $stmt = $pdo->prepare($sql);

        foreach ($attendanceData as $badge => $status) {
            $remarks = $remarksData[$badge] ?? '';
            $stmt->execute([
                ':badge'   => $badge,
                ':date'    => $logDate,
                ':status'  => $status,
                ':shift'   => $shiftType,
                ':remarks' => $remarks
            ]);
        }

        $pdo->commit();
        $_SESSION['flash_success'] = "REGISTRY_SYNCHRONIZED: " . count($attendanceData) . " Personnel logs saved for " . $logDate;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['flash_error'] = "DATABASE_MUTATION_FAILED: " . $e->getMessage();
    }

    header("Location: manage_attendance.php");
    exit();
} else {
    header("Location: manage_attendance.php");
    exit();
}
