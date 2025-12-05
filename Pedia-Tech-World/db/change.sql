-- Change image_path to TEXT to hold multiple paths (JSON format)
ALTER TABLE portfolio MODIFY image_path TEXT;

-- Add video_path column
ALTER TABLE portfolio ADD COLUMN video_path VARCHAR(255) AFTER image_path;