<?php
/**
 * Comprehensive diagnostic test
 */

echo "<h1>XAMPP Diagnostic Test</h1>";

// Basic PHP info
echo "<h2>PHP Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not set') . "</p>";

// Server variables
echo "<h2>Server Variables:</h2>";
$server_vars = [
    'DOCUMENT_ROOT',
    'SERVER_NAME',
    'SERVER_PORT',
    'REQUEST_URI',
    'SCRIPT_NAME',
    'SCRIPT_FILENAME',
    'HTTP_HOST',
    'HTTPS'
];

foreach ($server_vars as $var) {
    $value = $_SERVER[$var] ?? 'Not set';
    echo "<p><strong>$var:</strong> $value</p>";
}

// File system tests
echo "<h2>File System Tests:</h2>";
$tests = [
    'Current directory' => __DIR__,
    'Script filename' => __FILE__,
    'Parent directory exists' => is_dir(__DIR__ . '/..'),
    'public_html directory exists' => is_dir(__DIR__ . '/public_html'),
    'public_html readable' => is_readable(__DIR__ . '/public_html'),
];

foreach ($tests as $test => $result) {
    $status = is_bool($result) ? ($result ? '✓ PASS' : '✗ FAIL') : $result;
    echo "<p><strong>$test:</strong> $status</p>";
}

// Directory listing
echo "<h2>Current Directory Contents:</h2>";
$files = scandir(__DIR__);
echo "<ul>";
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $type = is_dir(__DIR__ . '/' . $file) ? 'DIR' : 'FILE';
        echo "<li><strong>$file</strong> ($type)</li>";
    }
}
echo "</ul>";

// Test if mod_rewrite is enabled
echo "<h2>Apache Modules:</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p>mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '✓ ENABLED' : '✗ DISABLED') . "</p>";
} else {
    echo "<p>Cannot check Apache modules (apache_get_modules not available)</p>";
}

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='hello.html'>Test HTML file</a></li>";
echo "<li><a href='test.php'>Test PHP file</a></li>";
echo "<li><a href='../'>Go up one directory</a></li>";
echo "</ul>";
?>
