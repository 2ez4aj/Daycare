<?php
// Check database structure
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if students table exists and get its structure
    $stmt = $conn->query("SHOW TABLES LIKE 'students'");
    if ($stmt->rowCount() === 0) {
        die("Error: The 'students' table does not exist in the database.\n");
    }
    
    // Get table structure
    $stmt = $conn->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Students table structure:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-20s %-15s %-10s %-5s %-5s %-10s\n", 
           'Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
    echo str_repeat("-", 80) . "\n";
    
    foreach ($columns as $column) {
        printf("%-20s %-15s %-10s %-5s %-5s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'],
            $column['Default'] ?? 'NULL',
            $column['Extra']
        );
    }
    
    // Check if the current user has a child
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE parent_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $child = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nSample child data for current parent (user_id: {$_SESSION['user_id']}):\n";
        echo str_repeat("-", 80) . "\n";
        if ($child) {
            print_r($child);
        } else {
            echo "No children found for this parent.\n";
        }
    } else {
        echo "\nNot logged in. Cannot check child data.\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
