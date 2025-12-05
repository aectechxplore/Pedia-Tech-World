USE pediatech_db;

-- Table for single global flash sale settings
CREATE TABLE IF NOT EXISTS flash_sale (
    id INT PRIMARY KEY DEFAULT 1,
    expiry_date DATETIME,
    description VARCHAR(255) DEFAULT 'Limited Time Offer!'
);

-- Initialize default row
INSERT IGNORE INTO flash_sale (id, expiry_date, description) VALUES (1, NULL, 'Hurry! Offer Ends Soon.');