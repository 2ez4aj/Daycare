<?php
/**
 * Admin Middleware
 * 
 * Checks if user has admin role
 */
class AdminMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: /login');
            exit();
        }
    }
}
