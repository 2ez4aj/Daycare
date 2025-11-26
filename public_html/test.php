<?php
/**
 * Simple test page
 */

echo "<h1>Gumamela Daycare Center - MVC Application</h1>";
echo "<p>Application is working!</p>";
echo "<p><a href='login'>Go to Login</a></p>";

// Test if we can access the core files
echo "<h2>System Check:</h2>";

if (defined('APPROOT')) {
    echo "✓ APPROOT defined: " . APPROOT . "<br>";
} else {
    echo "✗ APPROOT not defined<br>";
}

$coreFiles = [
    '/core/Application.php',
    '/core/Database.php',
    '/core/Router.php',
    '/app/Controllers/AuthController.php',
    '/app/Models/User.php',
    '/config/config.php'
];

foreach ($coreFiles as $file) {
    if (file_exists(APPROOT . $file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file missing<br>";
    }
}

echo "<h2>Next Steps:</h2>";
echo "<p>1. <a href='login'>Go to Login Page</a></p>";
echo "<p>2. Test admin and parent login</p>";
echo "<p>3. Verify dashboard functionality</p>";
?>
