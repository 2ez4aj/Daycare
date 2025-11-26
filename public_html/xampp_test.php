<!DOCTYPE html>
<html>
<head>
    <title>XAMPP Test</title>
</head>
<body>
    <h1>XAMPP is Working!</h1>
    <p>If you can see this page, XAMPP is running correctly.</p>
    <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p>PHP Version: <?php echo phpversion(); ?></p>
    <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></p>
    <p>Script Name: <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?></p>
    <p>Request URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?></p>
    
    <h2>Directory Contents:</h2>
    <pre><?php 
    $dir = __DIR__;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo $file . "\n";
        }
    }
    ?></pre>
</body>
</html>
