-- ==========================================
-- ASMS RECRUITMENT SCHEMA REBUILD
-- Alignment: Official Physical Recruitment Form (Bharti Form)
-- Architect: Adeen Muhtada (2025-CS-318)
-- ==========================================

USE security_ims_pro;

-- 1. DROP LEGACY STRUCTURES (Order respects Foreign Key constraints)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS guard_initial_kit;
DROP TABLE IF EXISTS guard_witnesses;
DROP TABLE IF EXISTS guard_certifications; -- Legacy table from previous iterations
DROP TABLE IF EXISTS guards;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. CORE GUARDS TABLE
-- Designed to mirror the multi-section Urdu recruitment form
CREATE TABLE guards (
    guard_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_no VARCHAR(50) UNIQUE NOT NULL, -- Manual Agency ID
    joining_date DATE NOT NULL,
    discharge_date DATE NULL,
    
    -- Personal Biometrics & Identity
    full_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    caste VARCHAR(50),
    education VARCHAR(100),
    religion VARCHAR(50),
    cnic VARCHAR(20) UNIQUE NOT NULL,
    dob DATE NOT NULL,
    district VARCHAR(50),
    
    -- Background & Experience
    previous_experience_ref TEXT,
    temporary_address TEXT,
    permanent_address TEXT,
    
    -- Military Service Record (Ex-Army Verification)
    army_joining_date DATE NULL,
    army_discharge_date DATE NULL,
    army_discharge_reason VARCHAR(255) NULL,
    
    -- Government References (Relative Checks)
    govt_relative_name VARCHAR(100) NULL,
    govt_relative_designation VARCHAR(100) NULL,
    govt_relative_department VARCHAR(100) NULL,
    
    -- Next of Kin (Emergency Context)
    next_of_kin_name_address TEXT NOT NULL,
    next_of_kin_mobile VARCHAR(20) NOT NULL,
    
    -- Financial & Compliance
    base_salary DECIMAL(10, 2) NOT NULL,
    police_verification_ref_no VARCHAR(100) NULL,
    
    -- System Parameters
    is_red_flagged TINYINT DEFAULT 0, -- Blacklist Toggle
    is_deleted TINYINT DEFAULT 0,     -- Soft Delete for Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. LEGAL WITNESSES TABLE
-- Mandatory 2-Slot mapping for legal guarantors
CREATE TABLE guard_witnesses (
    witness_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    witness_name VARCHAR(100) NOT NULL,
    witness_phone VARCHAR(20) NOT NULL,
    witness_address TEXT,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. GUARD INITIAL KIT TRACKING
-- Rule 11 Compliance: Tracking uniform handover and deductions
CREATE TABLE guard_initial_kit (
    kit_id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    shirt_trousers TINYINT DEFAULT 0, -- 1 = Issued
    cap TINYINT DEFAULT 0,            -- 1 = Issued
    belt TINYINT DEFAULT 0,           -- 1 = Issued
    boots TINYINT DEFAULT 0,          -- 1 = Issued
    jersey TINYINT DEFAULT 0,         -- 1 = Issued
    uniform_deduction_applied TINYINT DEFAULT 1, -- 1 = Yes, 0 = Waived
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES guards(guard_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Indexing for high-speed CNIC lookups during recruitment checks
CREATE INDEX idx_guard_cnic ON guards(cnic);
CREATE INDEX idx_guard_no ON guards(guard_no);
