USE pediatech_db;

-- 1. Create Promo Codes Table
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount VARCHAR(100) NOT NULL, -- e.g., "10% OFF", "₹500 Cashback"
    type ENUM('project', 'course', 'all') DEFAULT 'all',
    expiry_date DATE NOT NULL,
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Add promo_code column to applications table (to track which code was used)
ALTER TABLE applications ADD COLUMN promo_code VARCHAR(50) AFTER file_path;