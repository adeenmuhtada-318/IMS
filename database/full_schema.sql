-- ==========================================
-- IMS | SECURITY FIRM TACTICAL INTERFACE
-- UNIFIED MASTER SCHEMA (3rd Normal Form + CTI)
-- ==========================================

CREATE DATABASE IF NOT EXISTS security_ims_pro;
USE security_ims_pro;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payroll_logs;
DROP TABLE IF EXISTS performance_audits;
DROP TABLE IF EXISTS guard_witnesses;
DROP TABLE IF EXISTS guard_initial_kit;
DROP TABLE IF EXISTS asset_issuances;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS operational_assets;
DROP TABLE IF EXISTS apparel_assets;
DROP TABLE IF EXISTS office_assets;
DROP TABLE IF EXISTS logistics_assets;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 1. USERS (RBAC & Authentication)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_role ENUM('ADMIN', 'OPERATOR') DEFAULT 'OPERATOR',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CATEGORIES
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. ASSETS
CREATE TABLE assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    category_id INT NOT NULL,
    category_type ENUM('operational', 'apparel', 'logistics', 'office') NOT NULL,
    tracking_type ENUM('serialized', 'bulk') NOT NULL,
    current_stock INT DEFAULT 0,
    min_threshold INT DEFAULT 5,
    purchase_cost DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. OPERATIONAL ASSETS
CREATE TABLE operational_assets (
    asset_id INT PRIMARY KEY,
    serial_number VARCHAR(100) UNIQUE,
    bore_caliber VARCHAR(50),
    license_number VARCHAR(100),
    last_calibration_date DATE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. APPAREL ASSETS
CREATE TABLE apparel_assets (
    asset_id INT PRIMARY KEY,
    item_size VARCHAR(10),
    material_type VARCHAR(50),
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. LOGISTICS ASSETS
CREATE TABLE logistics_assets (
    asset_id INT PRIMARY KEY,
    registration_number VARCHAR(50) UNIQUE,
    chassis_number VARCHAR(100) UNIQUE,
    next_service_date DATE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. OFFICE ASSETS
CREATE TABLE office_assets (
    asset_id INT PRIMARY KEY,
    asset_type VARCHAR(50),
    location_room VARCHAR(50),
    depreciation_rate_annual DECIMAL(5, 2),
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. GUARDS
CREATE TABLE guards (
    guard_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_no VARCHAR(50) UNIQUE NOT NULL,
    joining_date DATE NOT NULL,
    discharge_date DATE NULL,
    full_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    caste VARCHAR(50),
    education VARCHAR(100),
    religion VARCHAR(50),
    cnic VARCHAR(20) UNIQUE NOT NULL,
    dob DATE NOT NULL,
    district VARCHAR(50),
    phone_number VARCHAR(20),
    blood_group VARCHAR(10),
    temporary_address TEXT,
    permanent_address TEXT,
    is_ex_army TINYINT DEFAULT 0,
    army_joining_date DATE NULL,
    army_discharge_date DATE NULL,
    army_discharge_reason VARCHAR(255) NULL,
    govt_relative_name VARCHAR(100) NULL,
    govt_relative_designation VARCHAR(100) NULL,
    govt_relative_department VARCHAR(100) NULL,
    previous_experience_ref TEXT,
    next_of_kin_name_address TEXT,
    next_of_kin_mobile VARCHAR(20),
    base_salary DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    police_verification_ref_no VARCHAR(100) NULL,
    is_red_flagged TINYINT DEFAULT 0,
    is_deleted TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. WITNESSES
CREATE TABLE guard_witnesses (
    witness_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    witness_name VARCHAR(100) NOT NULL,
    witness_phone VARCHAR(20) NOT NULL,
    witness_address TEXT,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. KIT
CREATE TABLE guard_initial_kit (
    kit_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    shirt_trousers TINYINT DEFAULT 0,
    cap TINYINT DEFAULT 0,
    belt TINYINT DEFAULT 0,
    boots TINYINT DEFAULT 0,
    jersey TINYINT DEFAULT 0,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. ISSUANCES
CREATE TABLE asset_issuances (
    issuance_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    guard_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('active_duty', 'returned_intact', 'damaged_loss', 'unaccounted_lost') DEFAULT 'active_duty',
    expected_return_date DATE,
    actual_return_date TIMESTAMP NULL,
    return_remarks TEXT,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id),
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id)
) ENGINE=InnoDB;

-- 12. TRANSACTIONS
CREATE TABLE transactions (
    trans_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    trans_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    performed_by INT NOT NULL,
    reference_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 13. AUDITS
CREATE TABLE performance_audits (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    billing_month VARCHAR(20) NOT NULL,
    total_present_days INT DEFAULT 0,
    double_shifts INT DEFAULT 0,
    lost_id_card_fines DECIMAL(10, 2) DEFAULT 0.00,
    shift_misconduct_fines DECIMAL(10, 2) DEFAULT 0.00,
    custom_client_penalties DECIMAL(10, 2) DEFAULT 0.00,
    audit_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 14. PAYROLL
CREATE TABLE payroll_logs (
    payroll_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    billing_month VARCHAR(20) NOT NULL,
    base_salary DECIMAL(10, 2) NOT NULL,
    total_deductions DECIMAL(10, 2) NOT NULL,
    net_pay DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'released') DEFAULT 'pending',
    released_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed Data
INSERT IGNORE INTO users (username, password_hash, user_role) 
VALUES ('ADMIN_SECURE', '$2y$10$8W3Y6u3r2y.Gq0.Z3/1e7e7e7e7e7e7e7e7e7e7e7e7e7e7e', 'ADMIN');

INSERT IGNORE INTO categories (category_name) VALUES 
('Ballistic Weaponry'), 
('Operational Apparel'), 
('Logistics & Transport'), 
('Office Supplies');
