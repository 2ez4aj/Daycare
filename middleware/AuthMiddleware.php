<?php
/**
 * Authentication Middleware
 * 
 * Checks if user is logged in
 */
class AuthMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit();
        }
    }
}
