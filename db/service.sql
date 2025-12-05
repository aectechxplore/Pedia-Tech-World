USE pediatech_db;

-- 1. Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL, -- e.g., 'Website Development', 'Mobile App'
    title VARCHAR(100) NOT NULL,    -- e.g., 'Basic', 'Business Pro'
    price VARCHAR(50) NOT NULL,
    duration VARCHAR(50),
    features TEXT,                  -- JSON array of features
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Courses Table (Ensure it exists)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    fee VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Seed Data for Services (Optional sample)
INSERT INTO services (category, title, price, duration, features) VALUES 
('Website Development', 'Startup Package', '₹15,000', '2 Weeks', '["5 Pages", "Contact Form", "Mobile Responsive"]');