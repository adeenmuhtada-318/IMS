-- ==========================================
-- ASMS SCHEMA ENHANCEMENT: DEPLOYMENT TRACKING
-- ==========================================

USE security_ims_pro;

-- Update issuance ledger to support site-specific tracking
ALTER TABLE asset_issuances 
ADD COLUMN deployment_location VARCHAR(255) AFTER quantity,
ADD COLUMN dispatch_notes TEXT AFTER return_remarks;
