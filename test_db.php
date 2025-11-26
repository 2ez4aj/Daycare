<?php
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    // Check if any students exist
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM students");
    $stmt->execute();
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total students: $total_students\n\n";
    
    // Check first few students
    if ($total_students > 0) {
        $stmt = $conn->prepare("SELECT s.id, s.first_name, s.last_name, s.schedule_id, ss.schedule_id as ss_schedule_id FROM students s LEFT JOIN student_schedules ss ON s.id = ss.student_id AND ss.is_active = 1 LIMIT 5");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Students data:\n";
        foreach ($students as $student) {
            echo "ID: {$student['id']}, Name: {$student['first_name']} {$student['last_name']}, schedule_id: " . ($student['schedule_id'] ?? 'NULL') . ", ss_schedule_id: " . ($student['ss_schedule_id'] ?? 'NULL') . "\n";
        }
    }
    
    // Check if schedules exist
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM schedules");
    $stmt->execute();
    $total_schedules = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "\nTotal schedules: $total_schedules\n\n";
    
    if ($total_schedules > 0) {
        $stmt = $conn->prepare("SELECT id, schedule_name, start_time, end_time FROM schedules LIMIT 3");
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Sample schedules:\n";
        foreach ($schedules as $schedule) {
            echo "ID: {$schedule['id']}, Name: {$schedule['schedule_name']}, Time: {$schedule['start_time']} - {$schedule['end_time']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
