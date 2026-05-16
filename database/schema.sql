-- ==========================================
-- IMS ENTERPRISE SCHEMA (3rd Normal Form)
-- Optimized for: Security Firm Asset Management
-- Architect: Adeen Muhtada (2025-CS-318)
-- ==========================================

CREATE DATABASE IF NOT EXISTS security_ims_pro;
USE security_ims_pro;

-- 1. CATEGORIES (Normalization: Item classification)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. SUPPLIERS (Audit Trail for Procurement)
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone_number VARCHAR(20),
    email_address VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. USERS (RBAC & Authentication)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_role ENUM('ADMIN', 'OPERATOR') DEFAULT 'OPERATOR',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- 4. INVENTORY (Core 3NF Table)
-- Supports both Serialized (Weapons) and Bulk (Uniforms)
CREATE TABLE IF NOT EXISTS inventory (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    sku_code VARCHAR(50) UNIQUE NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    current_stock INT DEFAULT 0,
    min_threshold INT DEFAULT 5,
    is_serialized TINYINT(1) DEFAULT 0, -- 1 = Needs serial/license tracking
    is_deleted TINYINT(1) DEFAULT 0,    -- Soft Delete for audit integrity
    valuation_wac DECIMAL(10, 2) DEFAULT 0.00, -- Weighted Average Cost
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. ASSET_ATTRIBUTES (Entity-Attribute Model)
-- For dynamic fields like Caliber, Size, License Number, etc.
CREATE TABLE IF NOT EXISTS asset_attributes (
    attribute_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    attr_name VARCHAR(50) NOT NULL, -- e.g., 'Caliber', 'Size'
    attr_value TEXT,
    FOREIGN KEY (item_id) REFERENCES inventory(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. TRANSACTIONS (The Immutable Ledger)
CREATE TABLE IF NOT EXISTS transactions (
    trans_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    trans_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) DEFAULT 0.00, -- Required for WAC calculation
    performed_by INT NOT NULL,
    reference_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory(item_id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Initial Seed Data
INSERT INTO users (username, password_hash, user_role) 
VALUES ('ADMIN_SECURE', '$2y$10$8W3Y6u3r2y.Gq0.Z3/1e7e7e7e7e7e7e7e7e7e7e7e7e7e7e', 'ADMIN'); -- Password: TACTICAL_2026 (Example hash)

INSERT INTO categories (category_name) VALUES ('Ballistic Weaponry'), ('Operational Apparel'), ('Surveillance Gear');
