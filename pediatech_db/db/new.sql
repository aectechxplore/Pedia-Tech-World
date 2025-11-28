-- Add a dedicated column for the main cover image
ALTER TABLE portfolio ADD COLUMN cover_image VARCHAR(255) AFTER description;

-- (Optional) Rename image_path to gallery_images for clarity, or just use image_path for the "Other Images"
-- We will continue using 'image_path' for the gallery (other images) to keep it simple.