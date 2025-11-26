<?php
/**
 * Parent Middleware
 * 
 * Checks if user has parent role
 */
class ParentMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
            header('Location: /login');
            exit();
        }
    }
}
