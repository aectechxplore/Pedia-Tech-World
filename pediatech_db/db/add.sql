-- Add category column to portfolio table
ALTER TABLE portfolio ADD COLUMN category ENUM('project', 'client', 'achievement') DEFAULT 'project' AFTER title;