-- Gumamela Daycare Center - Use Case Implementation
-- This file contains the database schema for the Gumamela Daycare Center system
-- focusing on the two main actors: Admin/Teacher and User/Parent

-- Drop existing tables if they exist (for clean setup)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `progress_reports`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `event_registrations`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `system_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table (Combines both admin/teacher and parent users)
CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `user_type` ENUM('admin', 'teacher', 'parent') NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Students Table
CREATE TABLE `students` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `birthdate` DATE NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `parent_id` INT(11) NOT NULL,
    `enrollment_date` DATE NOT NULL,
    `status` ENUM('active', 'inactive', 'graduated', 'transferred') DEFAULT 'active',
    `allergies` TEXT,
    `special_needs` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `fk_student_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Progress Reports Table
CREATE TABLE `progress_reports` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `report_date` DATE NOT NULL,
    `academic_performance` TEXT,
    `social_skills` TEXT,
    `physical_development` TEXT,
    `behavioral_notes` TEXT,
    `teacher_comments` TEXT,
    `overall_rating` ENUM('excellent', 'good', 'satisfactory', 'needs_improvement') NOT NULL,
    `created_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `student_id` (`student_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `fk_progress_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_teacher` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Attendance Table
CREATE TABLE `attendance` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    `notes` TEXT,
    `recorded_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `student_id` (`student_id`),
    KEY `recorded_by` (`recorded_by`),
    CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attendance_teacher` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Events Table
CREATE TABLE `events` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `event_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `max_participants` INT(11) DEFAULT NULL,
    `registration_required` TINYINT(1) DEFAULT 0,
    `created_by` INT(11) NOT NULL,
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `fk_event_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Event Registrations Table
CREATE TABLE `event_registrations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    `notes` TEXT,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_registration` (`event_id`, `student_id`),
    KEY `user_id` (`user_id`),
    KEY `student_id` (`student_id`),
    CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_registration_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_registration_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FAQs Table
CREATE TABLE `faqs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `display_order` INT(11) DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `fk_faq_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- System Settings Table
CREATE TABLE `system_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `description` TEXT,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
-- Default password is 'admin123' (hashed with password_hash())
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `user_type`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@daycare.com', 'Admin', 'User', 'admin', 'active');

-- Insert some default FAQs
INSERT INTO `faqs` (`question`, `answer`, `category`, `display_order`, `status`, `created_by`) VALUES
('What are your operating hours?', 'Our daycare is open from 7:00 AM to 6:00 PM, Monday through Friday.', 'General', 1, 'active', 1),
('What should my child bring to daycare?', 'Please bring a change of clothes, any necessary diapers/pull-ups, and any comfort items your child may need.', 'General', 2, 'active', 1),
('How do I update my contact information?', 'You can update your contact information through the parent portal or by contacting the front desk.', 'Account', 3, 'active', 1);

-- Insert some default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`, `is_public`) VALUES
('site_name', 'Gumamela Daycare Center', 'The name of the daycare center', 1),
('contact_email', 'info@daycare.com', 'Primary contact email', 1),
('contact_phone', '(123) 456-7890', 'Primary contact phone number', 1),
('address', '123 Main St, City, Country', 'Physical address of the daycare', 1),
('attendance_notification', '1', 'Enable/disable attendance notifications', 0);
