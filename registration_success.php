<?php
session_start();

// Check if user just completed registration
if (!isset($_SESSION['user_id']) || !isset($_SESSION['id_proof_path'])) {
    header('Location: register.php');
    exit();
}

// Clear registration session data but keep success message
$user_id = $_SESSION['user_id'];
$id_proof_path = $_SESSION['id_proof_path'];
unset($_SESSION['user_id']);
unset($_SESSION['id_proof_path']);

// Set success message
$_SESSION['success_message'] = 'Registration completed successfully! Your account is pending approval by the administrator.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Gumamela Daycare Center</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon i {
            color: white;
            font-size: 48px;
        }
        
        .success-title {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .success-message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .btn-continue {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .pending-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 30px;
            color: #856404;
        }
        
        .pending-notice i {
            margin-right: 10px;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="success-title">Registration Successful!</h1>
        
        <div class="pending-notice">
            <i class="fas fa-clock"></i>
            Your account is currently pending approval by the administrator.
        </div>
        
        <p class="success-message">
            Thank you for registering with Gumamela Daycare Center. Your account has been created successfully and is now pending approval. You will receive an email once your account has been activated.
        </p>
        
        <p class="success-message">
            <strong>What happens next?</strong><br>
            • Our administrator will review your application<br>
            • You'll receive an email approval notification<br>
            • Once approved, you can log in and access the parent dashboard
        </p>
        
        <a href="index.php" class="btn-continue">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
    
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
