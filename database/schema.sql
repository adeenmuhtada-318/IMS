-- IMS | SECURITY FIRM UNIFIED MASTER SCHEMA
-- Architect: IMS Data Structural Engineer
-- Version: 5.1 (Idempotent)

CREATE DATABASE IF NOT EXISTS SecurityFirm_Inventory;
USE SecurityFirm_Inventory;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS users, login_attempts, guards_personnel, clients, client_sites, attendance, Individual_Weapons, Vehicle_Assets, Bulk_Inventory, Inventory_Assignments, payroll, guard_blacklist, system_settings, dismissed_alerts, audit_log;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. USERS (RBAC & Authentication)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_role ENUM('Admin/CEO', 'Accountant', 'Operations Supervisor') NOT NULL,
    is_active TINYINT DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. LOGIN ATTEMPTS (Security Auditing)
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_successful TINYINT DEFAULT 0
) ENGINE=InnoDB;

-- 3. GUARDS PERSONNEL (Core Workforce)
CREATE TABLE IF NOT EXISTS guards_personnel (
    guard_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_no VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    parentage VARCHAR(255) NOT NULL,
    cnic VARCHAR(20) UNIQUE NOT NULL,
    dob DATE NOT NULL,
    guard_phone VARCHAR(20),
    caste VARCHAR(100),
    education VARCHAR(255),
    religion VARCHAR(100) DEFAULT 'Islam',
    home_district VARCHAR(100),
    permanent_address TEXT,
    temporary_address TEXT,
    heir_name VARCHAR(255),
    heir_phone VARCHAR(20),
    heir_relation VARCHAR(100),
    heir_address TEXT,
    prev_experience_ref TEXT,
    gov_relative_details TEXT,
    is_ex_army TINYINT(1) DEFAULT 0,
    army_enroll_date DATE NULL,
    army_discharge_date DATE NULL,
    witness1_name VARCHAR(255) NOT NULL,
    witness1_phone VARCHAR(20) NOT NULL,
    witness1_cnic VARCHAR(20) NOT NULL,
    witness1_address TEXT NOT NULL,
    witness2_name VARCHAR(255) NOT NULL,
    witness2_phone VARCHAR(20) NOT NULL,
    witness2_cnic VARCHAR(20) NOT NULL,
    witness2_address TEXT NOT NULL,
    fingerprint_status ENUM('Pending', 'Enrolled') DEFAULT 'Pending',
    police_verification_status ENUM('Pending', 'Verified') DEFAULT 'Pending',
    police_verification_no VARCHAR(100),
    special_branch_status ENUM('Pending', 'Verified') DEFAULT 'Pending',
    duty_status ENUM('Active Duty', 'Off Duty', 'On Leave') DEFAULT 'Off Duty',
    designation VARCHAR(100) DEFAULT 'Security Guard',
    joining_date DATE NOT NULL,
    base_salary_per_day DECIMAL(10, 2) DEFAULT 0.00,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (guard_no),
    INDEX (cnic)
) ENGINE=InnoDB;

-- 4. CLIENTS
CREATE TABLE IF NOT EXISTS clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL UNIQUE,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. CLIENT SITES
CREATE TABLE IF NOT EXISTS client_sites (
    site_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    office_phone VARCHAR(50),
    supervisor_name VARCHAR(255),
    supervisor_phone1 VARCHAR(20),
    supervisor_phone2 VARCHAR(20),
    required_day_guards INT DEFAULT 0,
    required_night_guards INT DEFAULT 0,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. ATTENDANCE (Tactical Operations)
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    site_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    shift_type ENUM('Day', 'Night') NOT NULL,
    attendance_status ENUM('Present', 'Absent', 'Reliever') NOT NULL,
    reliever_assigned_to INT NULL,
    change_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_double_book (guard_id, attendance_date, shift_type),
    FOREIGN KEY (guard_id) REFERENCES guards_personnel(guard_id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES client_sites(site_id) ON DELETE CASCADE,
    FOREIGN KEY (reliever_assigned_to) REFERENCES guards_personnel(guard_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. INDIVIDUAL WEAPONS (Armory)
CREATE TABLE IF NOT EXISTS Individual_Weapons (
    Weapon_ID INT AUTO_INCREMENT PRIMARY KEY,
    weapon_serial VARCHAR(100) UNIQUE NOT NULL,
    weapon_type VARCHAR(100) NOT NULL,
    weapon_model VARCHAR(100),
    Status ENUM('Available', 'Assigned', 'Maintenance') DEFAULT 'Available',
    expiry_date DATE NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. VEHICLE ASSETS (Patrol)
CREATE TABLE IF NOT EXISTS Vehicle_Assets (
    Vehicle_ID INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_no VARCHAR(100) UNIQUE NOT NULL,
    vehicle_type VARCHAR(100),
    model VARCHAR(100),
    Status ENUM('Available', 'Deployed', 'Maintenance') DEFAULT 'Available',
    registration_expiry DATE NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. BULK INVENTORY (Logistics)
CREATE TABLE IF NOT EXISTS Bulk_Inventory (
    Item_ID INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    item_category VARCHAR(100),
    Quantity_On_Hand INT DEFAULT 0,
    reorder_level INT DEFAULT 5,
    unit VARCHAR(50) DEFAULT 'Pcs',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 10. INVENTORY ASSIGNMENTS (Chain of Custody)
CREATE TABLE IF NOT EXISTS Inventory_Assignments (
    Assignment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Guard_ID INT NOT NULL,
    Asset_ID INT NOT NULL,
    Asset_Category ENUM('Weapon', 'Vehicle', 'Bulk_Item') NOT NULL,
    Quantity_Issued INT DEFAULT 1,
    Assignment_Status ENUM('Deployed', 'Returned') DEFAULT 'Deployed',
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    FOREIGN KEY (Guard_ID) REFERENCES guards_personnel(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. PAYROLL (Finance)
CREATE TABLE IF NOT EXISTS payroll (
    payroll_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    month_year VARCHAR(7) NOT NULL, -- Format: 'YYYY-MM'
    base_salary_per_day DECIMAL(10, 2) NOT NULL,
    total_presents INT DEFAULT 0,
    overtime_days INT DEFAULT 0,
    overtime_bonus_amount DECIMAL(10, 2) DEFAULT 0.00,
    alertness_bonus DECIMAL(10, 2) DEFAULT 0.00,
    disciplinary_deduction DECIMAL(10, 2) DEFAULT 0.00,
    uniform_deduction DECIMAL(10, 2) DEFAULT 0.00,
    id_loss_fine DECIMAL(10, 2) DEFAULT 0.00,
    sleeping_fine DECIMAL(10, 2) DEFAULT 0.00,
    absence_forfeiture_amount DECIMAL(10, 2) DEFAULT 0.00,
    net_salary DECIMAL(10, 2) DEFAULT 0.00,
    payout_status ENUM('Draft', 'Approved', 'Paid') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_guard_month (guard_id, month_year),
    FOREIGN KEY (guard_id) REFERENCES guards_personnel(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. GUARD BLACKLIST (Risk Management)
CREATE TABLE IF NOT EXISTS guard_blacklist (
    blacklist_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    reason TEXT,
    blacklisted_by INT,
    blacklisted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES guards_personnel(guard_id) ON DELETE CASCADE,
    FOREIGN KEY (blacklisted_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. SYSTEM SETTINGS (Configuration)
CREATE TABLE IF NOT EXISTS system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT
) ENGINE=InnoDB;

-- 14. DISMISSED ALERTS (User UX)
CREATE TABLE IF NOT EXISTS dismissed_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_key VARCHAR(200) NOT NULL,
    dismissed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. AUDIT LOG (Internal Tracking)
CREATE TABLE IF NOT EXISTS audit_log (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action_performed VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- STORED OBJECTS: VIEWS & PROCEDURES
-- ==========================================

-- A. EXPIRING ASSETS VIEW
DROP VIEW IF EXISTS vw_expiring_assets;
CREATE VIEW vw_expiring_assets AS
SELECT 'Weapon' as Category, weapon_serial as Identifier, expiry_date as Expiry
FROM Individual_Weapons 
WHERE expiry_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
AND is_deleted = 0
UNION ALL
SELECT 'Vehicle' as Category, vehicle_no as Identifier, registration_expiry as Expiry
FROM Vehicle_Assets 
WHERE registration_expiry BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
AND is_deleted = 0;

-- B. DASHBOARD KPI PROCEDURE
DROP PROCEDURE IF EXISTS sp_GetDashboardKPIs;
DELIMITER //
CREATE PROCEDURE sp_GetDashboardKPIs(IN p_exclude_admin TINYINT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM Individual_Weapons WHERE Status = 'Available' AND is_deleted = 0) as total_weapons,
        (SELECT COUNT(*) FROM Vehicle_Assets WHERE Status = 'Deployed' AND is_deleted = 0) as active_patrols,
        (SELECT COUNT(*) FROM guards_personnel WHERE duty_status = 'Active Duty' AND (p_exclude_admin = 0 OR designation != 'Admin') AND is_deleted = 0) as guards_on_duty,
        (SELECT COUNT(*) FROM Bulk_Inventory WHERE Quantity_On_Hand <= reorder_level AND is_deleted = 0) as stock_alerts;
END //
DELIMITER ;

-- ==========================================
-- SEED DATA: INITIAL CONFIGURATION
-- ==========================================

INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES 
('dashboard_exclude_admin_duty', '1'),
('company_name', 'FAST Security Services'),
('uniform_deduction_threshold_days', '180'),
('uniform_deduction_amount', '1500');

-- Initial Admin User (Default Password: Password@123 - Hashed)
INSERT IGNORE INTO users (username, password_hash, user_role) VALUES 
('ADMIN_SECURE', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin/CEO');
