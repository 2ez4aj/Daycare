<?php
require_once 'config/database.php';

echo "<h2>Announcement Database Test</h2>";

try {
    $conn = getDBConnection();
    
    // Check if announcements table exists
    echo "<h3>1. Table Structure Check:</h3>";
    $stmt = $conn->prepare("DESCRIBE announcements");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Count all announcements
    echo "<h3>2. Raw Count:</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM announcements");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total announcements in database: " . $count . "<br><br>";
    
    // Show all announcements
    echo "<h3>3. All Announcements (Raw Data):</h3>";
    $stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    $all_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($all_announcements)) {
        echo "No announcements found.<br>";
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
    
    // Test the exact query from admin page
    echo "<h3>4. Admin Page Query Test:</h3>";
    $stmt = $conn->prepare("
        SELECT a.*, u.first_name, u.last_name 
        FROM announcements a 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.status != 'archived'
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $admin_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query returned " . count($admin_announcements) . " announcements<br>";
    
    if (!empty($admin_announcements)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th></tr>";
        foreach ($admin_announcements as $ann) {
            $author_name = $ann['first_name'] ? $ann['first_name'] . ' ' . $ann['last_name'] : 'Unknown Author';
            echo "<tr>";
            echo "<td>" . $ann['id'] . "</td>";
            echo "<td>" . htmlspecialchars($ann['title']) . "</td>";
            echo "<td>" . htmlspecialchars($author_name) . "</td>";
            echo "<td>" . $ann['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Check users table
    echo "<h3>5. Users Check:</h3>";
    $stmt = $conn->prepare("SELECT id, first_name, last_name, user_type FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Admin users found: " . count($admins) . "<br>";
    foreach ($admins as $admin) {
        echo "- ID: " . $admin['id'] . ", Name: " . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
