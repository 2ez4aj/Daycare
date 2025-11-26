<?php
// Test registration system
require_once 'config/database.php';

echo "=== Registration System Test ===\n\n";

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        die("Database connection failed");
    }
    
    echo "✓ Database connection: SUCCESS\n";
    
    // Check if id_picture_path column exists
    $stmt = $conn->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasIdPicturePath = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'id_picture_path') {
            $hasIdPicturePath = true;
            break;
        }
    }
    
    if ($hasIdPicturePath) {
        echo "✓ id_picture_path column: EXISTS\n";
    } else {
        echo "✗ id_picture_path column: MISSING\n";
        echo "Running migration...\n";
        
        $conn->exec("ALTER TABLE users ADD COLUMN id_picture_path VARCHAR(500) NULL AFTER profile_photo_path");
        echo "✓ Migration completed successfully\n";
    }
    
    // Check uploads directory
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "✓ Created uploads directory\n";
    } else {
        echo "✓ Uploads directory exists\n";
    }
    
    // Check if required files exist
    $requiredFiles = [
        'register.php',
        'registration_success.php',
        'auth/register.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "✓ $file exists\n";
        } else {
            echo "✗ $file missing\n";
        }
    }
    
    // Test a simple query
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Current users in database: $userCount\n";
    
    echo "\n=== All checks completed ===\n";
    echo "Registration system should be working.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
