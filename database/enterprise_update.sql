-- ==========================================
-- ASMS ENTERPRISE SCHEMA (Class Table Inheritance)
-- Alignment with FAST Security Requirements
-- ==========================================

USE security_ims_pro;

-- 1. Master Assets Table (Common Data)
CREATE TABLE IF NOT EXISTS assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    category_type ENUM('operational', 'apparel', 'logistics', 'office') NOT NULL,
    tracking_type ENUM('serialized', 'bulk') NOT NULL,
    current_stock INT DEFAULT 0,
    min_threshold INT DEFAULT 5,
    purchase_cost DECIMAL(15, 2) NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Operational Sub-Table (Weapons/Comms)
CREATE TABLE IF NOT EXISTS operational_assets (
    asset_id INT PRIMARY KEY,
    serial_number VARCHAR(100) UNIQUE,
    bore_caliber VARCHAR(50),
    license_number VARCHAR(100),
    last_calibration_date DATE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Apparel Sub-Table (Uniforms)
CREATE TABLE IF NOT EXISTS apparel_assets (
    asset_id INT PRIMARY KEY,
    item_size VARCHAR(10),
    material_type VARCHAR(50),
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Logistics Sub-Table (Vehicles)
CREATE TABLE IF NOT EXISTS logistics_assets (
    asset_id INT PRIMARY KEY,
    registration_number VARCHAR(50) UNIQUE,
    chassis_number VARCHAR(100) UNIQUE,
    next_service_date DATE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Office Sub-Table (Supplies/Tech)
CREATE TABLE IF NOT EXISTS office_assets (
    asset_id INT PRIMARY KEY,
    asset_type VARCHAR(50),
    location_room VARCHAR(50),
    depreciation_rate_annual DECIMAL(5, 2),
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Asset Issuances (Guard Deployment Ledger)
CREATE TABLE IF NOT EXISTS asset_issuances (
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
    FOREIGN KEY (guard_id) REFERENCES users(user_id) -- Assuming guards are also in users table or have a guard table
) ENGINE=InnoDB;
