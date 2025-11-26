<?php
/**
 * MVC Application Entry Point - Public Access
 */

// Start session
session_start();

// Define application root (one level up)
define('APPROOT', dirname(__DIR__));

// Load core classes
require_once APPROOT . '/core/Application.php';
require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Router.php';
require_once APPROOT . '/core/BaseController.php';
require_once APPROOT . '/core/BaseModel.php';

// Load middleware
require_once APPROOT . '/middleware/AuthMiddleware.php';
require_once APPROOT . '/middleware/AdminMiddleware.php';
require_once APPROOT . '/middleware/ParentMiddleware.php';

// Load models
require_once APPROOT . '/app/Models/User.php';
require_once APPROOT . '/app/Models/Student.php';
require_once APPROOT . '/app/Models/Attendance.php';
require_once APPROOT . '/app/Models/Announcement.php';

// Load controllers
require_once APPROOT . '/app/Controllers/AuthController.php';
require_once APPROOT . '/app/Controllers/AdminController.php';
require_once APPROOT . '/app/Controllers/ParentController.php';

// Initialize and run application
$app = Application::getInstance();
$app->run();
