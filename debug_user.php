<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "No user session found. Please log in first.";
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check session data
    echo "<h3>Session Data:</h3>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
    echo "First Name: " . ($_SESSION['first_name'] ?? 'Not set') . "<br>";
    echo "Last Name: " . ($_SESSION['last_name'] ?? 'Not set') . "<br>";
    
    // Check if user exists in database
    echo "<h3>Database Check:</h3>";
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found in database:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "User Type: " . $user['user_type'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        echo "Created: " . $user['created_at'] . "<br>";
    } else {
        echo "❌ User NOT found in database with ID: " . $_SESSION['user_id'] . "<br>";
        
        // Check all users
        echo "<h3>All Users in Database:</h3>";
        $allUsers = $conn->query("SELECT id, username, email, user_type, status FROM users");
        while ($u = $allUsers->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$u['id']}, Username: {$u['username']}, Email: {$u['email']}, Type: {$u['user_type']}, Status: {$u['status']}<br>";
        }
    }
    
    // Check existing students for this parent
    echo "<h3>Existing Students:</h3>";
    $studentsStmt = $conn->prepare("SELECT * FROM students WHERE parent_id = ?");
    $studentsStmt->execute([$_SESSION['user_id']]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($students) {
        foreach ($students as $student) {
            echo "Student: {$student['first_name']} {$student['last_name']}, Status: {$student['status']}<br>";
        }
    } else {
        echo "No students found for this parent.<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
