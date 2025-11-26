<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Mailer.php';

// Get the config array
$config = require __DIR__ . '/config/config.php';

// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test email details
$testEmail = 'driancalda@gmail.com'; // Change this to the email you want to test with
$testName = 'Test User';

// Create Mailer instance
$mailer = new Mailer($config);

// Test email content
$to = [
    'email' => $testEmail,
    'name' => $testName
];

$subject = 'Gumamela Daycare - Test Email';

$htmlBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #e83e8c; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>Test Email from Gumamela Daycare</h2>
    </div>
    <div class='content'>
        <p>Hello $testName,</p>
        <p>This is a test email from Gumamela Daycare Center.</p>
        <p>If you're receiving this email, it means the email configuration is working correctly!</p>
        <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
    </div>
    <div class='footer'>
        <p>This is an automated test message.</p>
    </div>
</body>
</html>";

// Send the test email
try {
    $result = $mailer->send($to, $subject, $htmlBody);
    
    if ($result) {
        echo "<div style='padding: 20px; background-color: #d4edda; color: #155724; border-radius: 5px; margin: 20px;'>
                <h3>✅ Test Email Sent Successfully!</h3>
                <p>Please check your email at <strong>$testEmail</strong>.</p>
                <p>Don't forget to check your spam/junk folder if you don't see it in your inbox.</p>
              </div>";
    } else {
        echo "<div style='padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
                <h3>❌ Failed to Send Test Email</h3>
                <p>Please check the following:</p>
                <ol>
                    <li>Your internet connection</li>
                    <li>SMTP settings in config.php</li>
                    <li>Email credentials and permissions</li>
                    <li>PHP error logs for more details</li>
                </ol>
              </div>";
    }
} catch (Exception $e) {
    echo "<div style='padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
            <h3>❌ Error Sending Email</h3>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please check your SMTP configuration and try again.</p>
          </div>";
}
?>
