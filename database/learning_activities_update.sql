-- Update learning_activities table to support file attachments
-- Run this to update existing database

USE gumamela_daycare;

-- Add file attachment columns to learning_activities table
ALTER TABLE learning_activities 
ADD COLUMN attachment_path VARCHAR(500) NULL AFTER learning_objectives,
ADD COLUMN attachment_name VARCHAR(255) NULL AFTER attachment_path,
ADD COLUMN attachment_size INT NULL AFTER attachment_name,
ADD COLUMN attachment_type ENUM('image', 'document', 'video', 'other') NULL AFTER attachment_size;
