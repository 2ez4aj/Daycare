<?php
// router.php


// Get the requested path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Sanitize the path to prevent directory traversal
$path = ltrim($path, '/');

// Construct the full path to the requested file in the public directory
$publicPath = __DIR__ . '/public/' . $path;

// If the requested path is a file and it exists in the public directory, serve it directly.
// This is for assets like CSS, JS, and images.
if ($path !== '' && file_exists($publicPath) && is_file($publicPath)) {
    return false; // Serve the requested file as-is.
}

// For all other requests, route them through the main index.php front controller.
require_once __DIR__ . '/public/index.php';
