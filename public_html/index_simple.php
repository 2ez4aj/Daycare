<!DOCTYPE html>
<html>
<head>
    <title>Public HTML Index</title>
</head>
<body>
    <h1>Public HTML Directory</h1>
    <p>You are in the public_html directory.</p>
    
    <h2>Available Pages:</h2>
    <ul>
        <li><a href="diagnostic.php">Diagnostic Test</a></li>
        <li><a href="hello.html">Hello HTML</a></li>
        <li><a href="xampp_test.php">XAMPP Test</a></li>
        <li><a href="test.php">Application Test</a></li>
        <li><a href="login">Login Page (MVC)</a></li>
    </ul>
    
    <h2>Current Info:</h2>
    <p>Directory: <?php echo __DIR__; ?></p>
    <p>URL: <?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?></p>
</body>
</html>
