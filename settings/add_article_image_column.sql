-- Add article_image column to articles table
-- Run this in phpMyAdmin or your MySQL client

ALTER TABLE articles 
ADD COLUMN article_image VARCHAR(255) NULL 
AFTER article_body;

-- Add comment for documentation
ALTER TABLE articles 
MODIFY COLUMN article_image VARCHAR(255) NULL 
COMMENT 'Path to article image (e.g., ../../uploads/u1/a1/image_1.jpg)';

