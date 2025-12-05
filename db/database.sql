-- 1. Create and Select Database
CREATE DATABASE IF NOT EXISTS pediatech_db;
USE pediatech_db;

-- 2. USERS TABLE (Admin & Employees)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    employee_id VARCHAR(50) UNIQUE, -- e.g. EMP001
    role ENUM('admin', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. PORTFOLIO TABLE (For Dynamic Homepage Projects)
CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    project_url VARCHAR(255),
    duration VARCHAR(100),
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. APPLICATIONS TABLE (Job & Project Enquiries with Files)
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    type VARCHAR(50), -- 'job' or 'project'
    education TEXT,   -- Specific to Job applicants
    file_path VARCHAR(255), -- Resume or ID Proof path
    status VARCHAR(20) DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. COURSES TABLE (For Courses Page)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    fee VARCHAR(50)
);

-- 6. PROJECTS TABLE (Project Management)
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('Ongoing', 'Completed', 'Pending') DEFAULT 'Ongoing',
    progress INT DEFAULT 0
);

-- 7. PROJECT ASSIGNMENTS (Linking Employees to Projects)
CREATE TABLE IF NOT EXISTS project_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    user_id INT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. PROJECT UPDATES (Daily Employee Tasks)
CREATE TABLE IF NOT EXISTS project_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    user_id INT,
    update_text TEXT,
    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. ATTENDANCE TABLE (Daily Logs)
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date DATE,
    check_in_time TIME,
    status ENUM('Present', 'Absent') DEFAULT 'Present',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- SEED DATA (Default Login & Sample Content)
-- ==========================================

-- 1. Default Admin User
-- Email: admin
-- Password: password
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- 2. Sample Courses
INSERT INTO courses (title, description, duration, fee) VALUES 
('Full Stack Web Development', 'Master the MERN stack (MongoDB, Express, React, Node.js).', '3 Months', '₹20,000'),
('Mobile App Development', 'Build iOS and Android apps using Flutter and React Native.', '4 Months', '₹25,000'),
('UI/UX Design Mastery', 'Learn Figma, Adobe XD, and user-centric design principles.', '2 Months', '₹15,000');

-- 3. Sample Projects
INSERT INTO projects (name, status, progress) VALUES 
('E-Commerce Startup', 'Ongoing', 65),
('Internal CRM System', 'Completed', 100),
('Client Mobile App', 'Pending', 10);