<?php
session_start();

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: parent/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gumamela Daycare Center - Login</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/login.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
    <?php
    if (isset($_GET['error'])) {
        $error_message = '';
        switch ($_GET['error']) {
            case 'admin_using_parent_portal':
                $error_message = 'Admin users must login through the Admin Login portal';
                break;
            case 'parent_using_admin_portal':
                $error_message = 'Parent users must login through the Parent Login portal';
                break;
            case 'invalid_credentials':
                $error_message = 'Invalid username or password';
                break;
            case 'account_inactive':
                $error_message = 'Your account is inactive. Please contact administrator.';
                break;
            case 'empty_fields':
                $error_message = 'Please fill in all required fields';
                break;
            case 'invalid_user_type':
                $error_message = 'Invalid login portal selected';
                break;
            default:
                $error_message = 'An error occurred. Please try again.';
        }
        echo '<div class="error-banner">' . htmlspecialchars($error_message) . '</div>';
    }
    ?>
    <div class="decorative-elements">
        <div class="tag-element"></div>
        <div class="circle-element"></div>
    </div>
    <div class="login-background" style="background-image: url('./assets/images/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="login-container">
            <div class="login-tabs">
                <button class="tab-btn active" data-tab="user">Parent Login</button>
                <button class="tab-btn" data-tab="admin">Admin Login</button>
            </div>
            
                        
            <!-- Parent Login Form -->
            <div class="login-card active" id="user-login">
                <div class="logo-section">
                    <div class="logo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2>Parent Login</h2>
                    <p class="login-subtitle">Access your child's information</p>
                </div>
                
                <form id="userLoginForm" method="POST" action="auth/login.php">
                    <input type="hidden" name="user_type" value="parent">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Enter your username or email" required>
                    </div>
                    
                    <div class="form-group password-container">
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="parent-password" placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#parent-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember-user">
                        <label class="form-check-label" for="remember-user">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">Login as Parent</button>
                    
                    <div class="register-link">
                        Don't have an account? <a href="register.php">Register here</a>
                    </div>
                    
                </form>
            </div>
            
            <!-- Admin Login Form -->
            <div class="login-card" id="admin-login">
                <div class="logo-section">
                    <div class="logo admin-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2>Admin Login</h2>
                </div>
                
                <form id="adminLoginForm" method="POST" action="auth/login.php">
                    <input type="hidden" name="user_type" value="admin">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Enter admin username" required>
                    </div>
                    
                    <div class="form-group password-container">
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="admin-password" placeholder="Enter admin password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#admin-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember-admin">
                        <label class="form-check-label" for="remember-admin">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login admin-btn">Login as Admin</button>
                    
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
    <style>
        .password-container .input-group {
            position: relative;
        }
        .password-container .toggle-password {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            background: transparent;
            border-left: none;
        }
        .password-container .form-control {
            padding-right: 45px;
        }
    </style>
    
    <!-- Inline JavaScript fallback for tab switching -->
    <script>
        console.log('Login page loaded - initializing tab switching...');
        
        // Fallback tab switching in case external JS doesn't load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - setting up tabs...');
            
            const tabButtons = document.querySelectorAll('.tab-btn');
            const loginCards = document.querySelectorAll('.login-card');
            
            console.log('Found tab buttons:', tabButtons.length);
            console.log('Found login cards:', loginCards.length);
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    console.log('Tab clicked:', this.getAttribute('data-tab'));
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and cards
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    loginCards.forEach(card => card.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding card
                    this.classList.add('active');
                    const targetCard = document.getElementById(`${targetTab}-login`);
                    if (targetCard) {
                        targetCard.classList.add('active');
                        console.log('Activated card:', targetCard.id);
                    }
                });
            });
            
            // Ensure user login is visible by default
            const userLoginCard = document.getElementById('user-login');
            const userTabButton = document.querySelector('[data-tab="user"]');
            if (userLoginCard && userTabButton) {
                userLoginCard.classList.add('active');
                userTabButton.classList.add('active');
                console.log('User login card set as active by default');
            }
            
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.querySelector(targetId);
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>
</html>
