<?php
/**
 * MVC Test Page
 */

// Start session
session_start();

// Define application root
define('APPROOT', dirname(__DIR__));

echo "<h1>MVC Application Test</h1>";

// Test if we can load the core classes
$core_files = [
    'Application' => APPROOT . '/core/Application.php',
    'Database' => APPROOT . '/core/Database.php',
    'Router' => APPROOT . '/core/Router.php',
    'BaseController' => APPROOT . '/core/BaseController.php',
    'BaseModel' => APPROOT . '/core/BaseModel.php'
];

echo "<h2>Core Classes Check:</h2>";
foreach ($core_files as $class => $file) {
    if (file_exists($file)) {
        echo "<p>✓ $class class exists</p>";
        try {
            require_once $file;
            echo "<p>✓ $class class loaded successfully</p>";
        } catch (Exception $e) {
            echo "<p>✗ $class class failed to load: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✗ $class class file missing: $file</p>";
    }
}

// Test Application
echo "<h2>Application Test:</h2>";
try {
    $app = \core\Application::getInstance();
    echo "<p>✓ Application instance created</p>";
    
    $db = $app->getDatabase();
    echo "<p>✓ Database connection available</p>";
    
    $router = $app->getRouter();
    echo "<p>✓ Router available</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Application failed: " . $e->getMessage() . "</p>";
}

// Test Controllers
echo "<h2>Controllers Check:</h2>";
$controllers = [
    'AuthController' => APPROOT . '/app/Controllers/AuthController.php',
    'AdminController' => APPROOT . '/app/Controllers/AdminController.php',
    'ParentController' => APPROOT . '/app/Controllers/ParentController.php'
];

foreach ($controllers as $controller => $file) {
    if (file_exists($file)) {
        echo "<p>✓ $controller exists</p>";
    } else {
        echo "<p>✗ $controller missing</p>";
    }
}

// Test Views
echo "<h2>Views Check:</h2>";
$views = [
    'auth/login' => APPROOT . '/app/Views/auth/login.php',
    'layouts/main' => APPROOT . '/app/Views/layouts/main.php',
    'admin/dashboard' => APPROOT . '/app/Views/admin/dashboard.php'
];

foreach ($views as $view => $file) {
    if (file_exists($file)) {
        echo "<p>✓ $view view exists</p>";
    } else {
        echo "<p>✗ $view view missing</p>";
    }
}

echo "<h2>Test Links:</h2>";
echo "<p><a href='../'>Go to Root</a></p>";
echo "<p><a href='../login'>Try Login Page</a></p>";
echo "<p><a href='../admin/dashboard'>Try Admin Dashboard</a></p>";
?>
