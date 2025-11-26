<?php
// Database update script to add suffix column to students table
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    // Check if suffix column already exists
    $checkColumn = $conn->prepare("SHOW COLUMNS FROM students LIKE 'suffix'");
    $checkColumn->execute();
    $columnExists = $checkColumn->fetch();
    
    if (!$columnExists) {
        // Add suffix column to students table
        $alterTable = "ALTER TABLE students ADD COLUMN suffix VARCHAR(10) NULL AFTER last_name";
        $conn->exec($alterTable);
        echo "✓ Successfully added suffix column to students table.\n";
    } else {
        echo "✓ Suffix column already exists in students table.\n";
    }
    
    // Verify the change
    $describe = $conn->prepare("DESCRIBE students");
    $describe->execute();
    $columns = $describe->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent students table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\n✓ Database update completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error updating database: " . $e->getMessage() . "\n";
}
?>
