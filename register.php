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

$registerErrors = $_SESSION['register_errors'] ?? [];
$oldInput = $_SESSION['register_form_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gumamela Daycare Center - Register</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/register.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body style="background-image: url('assets/images/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="register-container">
        <div class="decorative-elements">
            <div class="tag-element"></div>
            <div class="circle-element"></div>
        </div>
        
        <div class="register-card">
            <div class="header-section">
                <h2>Registration Page</h2>
                <a href="index.php" class="back-to-login">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            
            <form id="registerForm" method="POST" action="auth/register.php" enctype="multipart/form-data">
                <div class="form-section">
                    <h3>Register Account</h3>
                    
                    <?php if (!empty($registerErrors)): ?>
                        <div class="alert alert-danger">
                            <h6 class="mb-2"><i class="fas fa-exclamation-circle me-2"></i>Unable to submit registration</h6>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($registerErrors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($oldInput['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($oldInput['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="tel" class="form-control" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($oldInput['phone'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <textarea class="form-control" name="address" placeholder="Address" rows="3" required><?php echo htmlspecialchars($oldInput['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_proof" class="form-label">Upload Valid ID (e.g., Passport, Driver's License)</label>
                        <input type="file" class="form-control" id="id_proof" name="id_proof" accept="image/*,.pdf" required>
                        <div class="form-text">Max file size: 5MB. Accepted formats: JPG, PNG, PDF</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="child_photo" class="form-label">Child's Photo</label>
                        <input type="file" class="form-control" id="child_photo" name="child_photo" accept="image/*" required>
                        <div class="form-text">Clear photo of the child's face. Max file size: 5MB. Accepted formats: JPG, PNG</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the Terms and Conditions of Gumamela Daycare Center
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-register">SIGN UP</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html>
