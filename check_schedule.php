<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: index.php');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get the first child of the parent to check
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM students WHERE parent_id = ? AND status IN ('active', 'pending') LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $child = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$child) {
        die("No children found for this parent.");
    }
    
    echo "<h2>Checking schedule for: " . htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) . "</h2>";
    
    // Check student_schedules table
    echo "<h3>1. Checking student_schedules table:</h3>";
    $stmt = $conn->prepare("SELECT * FROM student_schedules WHERE student_id = ?");
    $stmt->execute([$child['id']]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($schedules)) {
        echo "<p>No schedules found in student_schedules for this student.</p>";
    } else {
        echo "<pre>";
        print_r($schedules);
        echo "</pre>";
        
        // Check if any schedule is active
        $active_schedule = array_filter($schedules, function($schedule) {
            return $schedule['is_active'] == 1;
        });
        
        if (empty($active_schedule)) {
            echo "<p>No active schedules found for this student.</p>";
        } else {
            echo "<p>Active schedule found.</p>";
        }
    }
    
    // Check schedules table
    echo "<h3>2. Checking schedules table:</h3>";
    $stmt = $conn->prepare("
        SELECT s.* 
        FROM schedules s
        JOIN student_schedules ss ON s.id = ss.schedule_id
        WHERE ss.student_id = ? AND ss.is_active = 1
    ");
    $stmt->execute([$child['id']]);
    $schedule_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (empty($schedule_details)) {
        echo "<p>No schedule details found in schedules table for this student.</p>";
    } else {
        echo "<pre>";
        print_r($schedule_details);
        echo "</pre>";
    }
    
    // Check the query being used in attendance.php
    echo "<h3>3. Testing the attendance query:</h3>";
    $stmt = $conn->prepare("
        SELECT 
            s.id as student_id,
            s.first_name, 
            s.last_name, 
            sch.schedule_name as session_name, 
            sch.start_time, 
            sch.end_time,
            ss.is_active as schedule_active
        FROM students s
        LEFT JOIN (
            SELECT ss1.* 
            FROM student_schedules ss1
            INNER JOIN (
                SELECT student_id, MAX(id) as max_id
                FROM student_schedules
                WHERE is_active = 1
                GROUP BY student_id
            ) ss2 ON ss1.id = ss2.max_id AND ss1.student_id = ss2.student_id
            WHERE ss1.is_active = 1
        ) ss ON s.id = ss.student_id
        LEFT JOIN schedules sch ON ss.schedule_id = sch.id
        WHERE s.id = ?
    ");
    $stmt->execute([$child['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>Query Result:</h4>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<h3>4. Next Steps:</h3>
<ol>
    <li>If no schedules are found in student_schedules, you'll need to assign a schedule to the student.</li>
    <li>If schedules exist but none are active, you'll need to activate a schedule for the student.</li>
    <li>If the schedule exists but isn't showing in the attendance page, there might be an issue with the query or data structure.</li>
</ol>
