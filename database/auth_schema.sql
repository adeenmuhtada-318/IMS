-- ==========================================
-- AUTHENTICATION SCHEMA UPDATE
-- Add this to your existing database in Workbench
-- ==========================================

-- 1. Create the Users Table
-- Normalization: Separates identity from inventory
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Stores secure BCRYPT hashes
    role_id INT DEFAULT 2,               -- 1: Admin, 2: Operator
    is_active TINYINT(1) DEFAULT 1,      -- For account suspension
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Seed Initial Admin User
-- Password is: TACTICAL_2026
-- Generate fresh hashes for each deployment for security.
INSERT INTO users (username, password_hash, role_id) 
VALUES ('ADMIN_SECURE', '$2y$10$8W3Y6u3r2y.Gq0.Z3/1e7e7e7e7e7e7e7e7e7e7e7e7e7e7e', 1);
