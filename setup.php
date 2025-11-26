<?php
// Database Setup Script for Gumamela Daycare Center
// Run this file once to set up the database and create the admin user

$host = "localhost";
$username = "root";
$password = "";
$db_name = "gumamela_daycare";

try {
    // First, connect without specifying database to create it
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    echo "✓ Database '$db_name' created successfully<br>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the SQL file
    $sql = file_get_contents('database/daycare_db.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database tables created successfully<br>";
    
    // Check if admin user exists, if not create one
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();
    
    if ($adminExists == 0) {
        // Create admin user with password 'password'
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, user_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@gumamela-daycare.com', $hashedPassword, 'Admin', 'User', 'admin', 'active']);
        echo "✓ Admin user created successfully<br>";
        echo "   Username: admin<br>";
        echo "   Password: password<br>";
    } else {
        echo "✓ Admin user already exists<br>";
    }
    
    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "<a href='index.php'>Go to Login Page</a><br>";
    echo "<br><em>You can delete this setup.php file after successful setup.</em>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Common solutions:</strong><br>";
    echo "1. Make sure XAMPP/WAMP is running<br>";
    echo "2. Check if MySQL service is started<br>";
    echo "3. Verify database credentials in config/database.php<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gumamela Daycare - Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #FF6B9D; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌺 Gumamela Daycare Center - Database Setup</h1>
        <p>This script will set up your database and create the admin user.</p>
        <hr>
        <?php if (!isset($pdo)): ?>
            <p><strong>Click the button below to start setup:</strong></p>
            <form method="post">
                <button type="submit" name="setup" class="setup-button">Start Setup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
