USE pediatech_db;

-- Create table for Admin-managed Employee Checklists
CREATE TABLE IF NOT EXISTS project_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Ongoing', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);