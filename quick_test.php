<?php
// Quick registration system test
require_once 'config/database.php';

echo "Testing registration system...\n";

try {
    $conn = getDBConnection();
    if (!$conn) {
        echo "❌ Database connection failed\n";
        exit;
    }
    
    echo "✅ Database connection OK\n";
    
    // Check required files
    $files = ['register.php', 'registration_success.php', 'auth/register.php'];
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "✅ $file exists\n";
        } else {
            echo "❌ $file missing\n";
        }
    }
    
    // Check uploads directory
    if (is_dir('uploads')) {
        echo "✅ Uploads directory exists\n";
    } else {
        echo "❌ Uploads directory missing\n";
    }
    
    echo "\nRegistration system should be working now!\n";
    echo "Try registering at: http://localhost/NewDaycare/register.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
