<?php
// Front Controller

// --- Static File Server ---
// This part handles requests for static files (CSS, JS, images)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $path;

if ($path !== '/' && file_exists($filePath) && is_file($filePath)) {
    $mimeType = mime_content_type($filePath);
    header('Content-Type: ' . $mimeType);
    readfile($filePath);
    exit();
}

// --- MVC Application Bootstrap ---
session_start();

// Set the base path of the application
define('BASE_PATH', dirname(__DIR__));

// Autoload core classes
spl_autoload_register(function ($className) {
    $corePath = BASE_PATH . '/core/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($corePath)) {
        require_once $corePath;
    }
});

// Autoload app classes (controllers, models)
spl_autoload_register(function ($className) {
    $appPath = BASE_PATH . '/app/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($appPath)) {
        require_once $appPath;
    }
});

// Load the database configuration
require_once BASE_PATH . '/config/database.php';

// Load the router and dispatch the request
require_once BASE_PATH . '/core/Router.php';

$router = new Router();
$router->dispatch();
