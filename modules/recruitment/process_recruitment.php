<?php
session_start();
require_once "../../includes/connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $badge_ref = "SEC-2026-" . rand(100, 999);
    
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $cnic_num = isset($_POST['cnic_number']) ? trim($_POST['cnic_number']) : '';
    $phone_num = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    
    $address = isset($_POST['home_address']) ? trim($_POST['home_address']) : '';
    $city = 'Faisalabad';
    $district = 'Faisalabad';
    
    $dob = isset($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : '';
    $blood_group = isset($_POST['blood_group']) ? trim($_POST['blood_group']) : 'Unknown';
    $clearance = isset($_POST['clearance_level']) ? trim($_POST['clearance_level']) : 'Standard';
    $emergency_contact = isset($_POST['emergency_contact']) ? trim($_POST['emergency_contact']) : '';
    $emergency_phone = isset($_POST['emergency_phone']) ? trim($_POST['emergency_phone']) : '';
    
    // FIX: Capturing dynamic user input parameters from the interface
    $base_salary = isset($_POST['monthly_base_salary']) ? floatval($_POST['monthly_base_salary']) : 45000.00; 
    $duty_status = isset($_POST['deployment_status']) ? trim($_POST['deployment_status']) : 'Free';
    
    $enroll_date = date("Y-m-d");

    if (!empty($full_name) && !empty($cnic_num) && !empty($phone_num)) {
        try {
            $insert_query = "INSERT INTO security_guards 
                (badge_number, full_name, cnic, phone_number, home_address, home_city, home_district, deployment_status, monthly_base_salary, enrollment_date, date_of_birth, blood_group, experience_tier, emergency_contact_name, emergency_contact_phone) 
                VALUES (:badge, :name, :cnic, :phone, :address, :city, :district, :status, :salary, :enroll_date, :dob, :blood, :tier, :e_name, :e_phone)";
                
            $stmt = $conn->prepare($insert_query);
            $stmt->execute([
                ':badge'         => $badge_ref,
                ':name'          => $full_name,
                ':cnic'          => $cnic_num,
                ':phone'         => $phone_num,
                ':address'       => $address,
                ':city'          => $city,
                ':district'      => $district,
                ':status'        => $duty_status,
                ':salary'        => $base_salary,
                ':enroll_date'   => $enroll_date,
                ':dob'           => !empty($dob) ? $dob : null,
                ':blood'         => $blood_group,
                ':tier'          => $clearance,
                ':e_name'        => $emergency_contact,
                ':e_phone'       => $emergency_phone
            ]);
            
            $_SESSION['success_flash'] = "Hiring process completed. Assigned Employee Badge: " . $badge_ref;
        } catch (PDOException $e) {
            $_SESSION['error_flash'] = "Database processing failure: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_flash'] = "Mandatory identification metrics are missing.";
    }
    
    header("Location: recruitment_form.php");
    exit();
}
?>