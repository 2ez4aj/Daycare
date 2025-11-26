-- Add scheduling system to Gumamela Daycare Database
-- This adds schedule management functionality

-- Create schedules table for time slots
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_capacity INT DEFAULT 20,
    current_enrolled INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert the three default schedules
INSERT INTO schedules (schedule_name, start_time, end_time, max_capacity) VALUES
('Morning Session', '08:00:00', '10:00:00', 20),
('Mid-Morning Session', '10:00:00', '12:00:00', 20),
('Afternoon Session', '13:00:00', '15:00:00', 20);

-- Add schedule_id column to students table
ALTER TABLE students ADD COLUMN schedule_id INT NULL AFTER gender;
ALTER TABLE students ADD FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL;

-- Create student_schedules table for tracking schedule assignments
CREATE TABLE student_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    schedule_id INT NOT NULL,
    assigned_date DATE DEFAULT (CURRENT_DATE),
    assigned_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_active_student_schedule (student_id, is_active)
);
