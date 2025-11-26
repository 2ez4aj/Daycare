<?php
session_start();
require_once 'config/database.php';

echo "<h2>Announcement Debug Information</h2>";

try {
    $conn = getDBConnection();
    
    // Check if announcements table exists and has data
    echo "<h3>Announcements Table Status:</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM announcements");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total announcements in database: " . $total . "<br>";
    
    // Get all announcements with details
    echo "<h3>All Announcements (Raw Data):</h3>";
    $stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    $all_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($all_announcements)) {
        echo "No announcements found in database.<br>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Author ID</th><th>Priority</th><th>Status</th><th>Created</th></tr>";
        foreach ($all_announcements as $ann) {
            echo "<tr>";
            echo "<td>" . $ann['id'] . "</td>";
            echo "<td>" . htmlspecialchars($ann['title']) . "</td>";
            echo "<td>" . $ann['author_id'] . "</td>";
            echo "<td>" . $ann['priority'] . "</td>";
            echo "<td>" . $ann['status'] . "</td>";
            echo "<td>" . $ann['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Check users table for admin users
    echo "<h3>Admin Users:</h3>";
    $stmt = $conn->prepare("SELECT id, first_name, last_name, user_type FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "No admin users found.<br>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "</td>";
            echo "<td>" . $admin['user_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Test the admin query with JOIN
    echo "<h3>Admin Query Test (with JOIN):</h3>";
    $stmt = $conn->prepare("
        SELECT a.*, u.first_name, u.last_name 
        FROM announcements a 
        JOIN users u ON a.author_id = u.id 
        WHERE a.status != 'archived'
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $joined_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($joined_announcements)) {
        echo "No announcements found with JOIN query.<br>";
        
        // Check if there's a mismatch in author_id
        echo "<h4>Checking for author_id mismatches:</h4>";
        $stmt = $conn->prepare("
            SELECT a.id, a.title, a.author_id, 
                   CASE WHEN u.id IS NULL THEN 'MISSING USER' ELSE 'USER EXISTS' END as user_status
            FROM announcements a 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.status != 'archived'
        ");
        $stmt->execute();
        $mismatch_check = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($mismatch_check as $check) {
            echo "Announcement ID: " . $check['id'] . ", Title: " . htmlspecialchars($check['title']) . 
                 ", Author ID: " . $check['author_id'] . ", Status: " . $check['user_status'] . "<br>";
        }
    } else {
        echo "Found " . count($joined_announcements) . " announcements with JOIN.<br>";
        foreach ($joined_announcements as $ann) {
            echo "- " . htmlspecialchars($ann['title']) . " by " . htmlspecialchars($ann['first_name'] . ' ' . $ann['last_name']) . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
