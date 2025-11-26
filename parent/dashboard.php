<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: ../index.php');
    exit();
}

// Get parent's data and announcements
try {
    $conn = getDBConnection();
    
    // Get recent announcements
    $stmt = $conn->prepare("SELECT title, content, created_at FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get parent's children
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM students WHERE parent_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread messages count
    $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
    
    // Get user profile data including photo
    $stmt = $conn->prepare("SELECT profile_photo_path FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if enrollment is enabled
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'enrollment_enabled'");
    $stmt->execute();
    $enrollment_setting = $stmt->fetch(PDO::FETCH_ASSOC);
    $enrollment_enabled = $enrollment_setting ? ($enrollment_setting['setting_value'] == '1') : false;
    
    // Get unread notifications count for sidebar badge
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
} catch (Exception $e) {
    $announcements = [];
    $children = [];
    $unread_messages = 0;
    $unread_notifications = 0;
    $enrollment_enabled = false;
    $user_profile = ['profile_photo_path' => null];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Gumamela Daycare Center</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/parent.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/mobile_nav.css" rel="stylesheet">
</head>
<body>
    <div class="parent-container">
        <?php include 'parent_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="parent-hero mb-4">
                <div class="hero-profile">
                    <div class="hero-avatar">
                        <?php if (!empty($user_profile['profile_photo_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($user_profile['profile_photo_path']); ?>" alt="Profile photo">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="eyebrow">Hello, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                        <h1>Your children are thriving today.</h1>
                        <p class="text-muted mb-3">Track announcements, attendance, and activities designed for your family.</p>
                        <div class="hero-chips">
                            <span><i class="fas fa-child me-2"></i><?php echo count($children); ?> Enrolled</span>
                            <span><i class="fas fa-envelope me-2"></i><?php echo $unread_messages; ?> Unread Messages</span>
                            <span><i class="fas fa-bell me-2"></i><?php echo $unread_notifications; ?> Notifications</span>
                        </div>
                    </div>
                </div>
                <div class="hero-cta">
                    <p class="text-uppercase small text-muted mb-2">Need to enroll another child?</p>
                    <h4 class="mb-3 <?php echo $enrollment_enabled ? 'text-success' : 'text-warning'; ?>">
                        <?php echo $enrollment_enabled ? 'Enrollment Currently Open' : 'Enrollment Closed'; ?>
                    </h4>
                    <a href="enroll.php" class="btn btn-primary w-100 <?php echo $enrollment_enabled ? '' : 'disabled'; ?>">
                        <i class="fas fa-user-plus me-2"></i>Start Enrollment
                    </a>
                    <?php if (!$enrollment_enabled): ?>
                        <small class="text-muted d-block text-center mt-2">Admin will reopen soon.</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Enrollment Status Alert -->
            <?php if (!$enrollment_enabled): ?>
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Enrollment Closed:</strong> New child enrollment is currently disabled by the administration.
            </div>
            <?php endif; ?>
            
            <!-- Announcements Section -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card announcement-timeline">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Announcements</h5>
                            <a href="announcements.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($announcements)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-bullhorn fa-3x mb-3 opacity-25"></i>
                                    <p>No announcement</p>
                                </div>
                            <?php else: ?>
                                <ul class="timeline-list">
                                    <?php foreach ($announcements as $announcement): ?>
                                        <li>
                                            <div class="timeline-dot"></div>
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></small>
                                                </div>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($announcement['content']); ?></p>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card quick-links-card">
                        <div class="card-header">
                            <h5>Shortcuts</h5>
                        </div>
                        <div class="card-body">
                            <a href="messages.php" class="quick-link">
                                <span class="icon bg-primary-subtle text-primary"><i class="fas fa-envelope-open-text"></i></span>
                                <div>
                                    <strong>Messages</strong>
                                    <p class="mb-0 text-muted">Reply to admin updates</p>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $unread_messages; ?></span>
                            </a>
                            <a href="attendance.php" class="quick-link">
                                <span class="icon bg-success-subtle text-success"><i class="fas fa-calendar-check"></i></span>
                                <div>
                                    <strong>Attendance</strong>
                                    <p class="mb-0 text-muted">Track arrival/departure</p>
                                </div>
                            </a>
                            <a href="progress.php" class="quick-link">
                                <span class="icon bg-warning-subtle text-warning"><i class="fas fa-chart-line"></i></span>
                                <div>
                                    <strong>Progress Reports</strong>
                                    <p class="mb-0 text-muted">See milestones</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Parent Resources Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Parent Resources</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="resource-card">
                                        <div class="resource-icon">
                                            <i class="fas fa-puzzle-piece"></i>
                                        </div>
                                        <div class="resource-content">
                                            <h6>Learning Activities</h6>
                                            <p>Fun educational activities for your child</p>
                                            <a href="learning_activities.php" class="btn btn-sm btn-primary">View Activities</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="resource-card">
                                        <div class="resource-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="resource-content">
                                            <h6>Upcoming Events</h6>
                                            <p>Stay updated with daycare events</p>
                                            <a href="events.php" class="btn btn-sm btn-primary">View Events</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="resource-card">
                                        <div class="resource-icon">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                        <div class="resource-content">
                                            <h6>FAQ</h6>
                                            <p>Find answers to common questions</p>
                                            <a href="faqs.php" class="btn btn-sm btn-primary">View FAQ</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/parent.js"></script>
    <script>
        // Handle success/error messages
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const error = urlParams.get('error');
        
        if (success) {
            let message = '';
            switch (success) {
                case 'enrollment_submitted':
                    message = 'Child enrollment submitted successfully! We will review your application and contact you soon.';
                    break;
            }
            if (message) {
                showNotification(message, 'success');
            }
        }
        
        if (error) {
            let message = '';
            switch (error) {
                case 'enrollment_disabled':
                    message = 'Enrollment is currently disabled by the administration.';
                    break;
                case 'system_error':
                    message = 'System error occurred. Please try again later.';
                    break;
            }
            if (message) {
                showNotification(message, 'error');
            }
        }
    </script>
    <script src="../assets/js/mobile_nav.js"></script>
    <?php include 'mobile_nav.php'; ?>
</body>
</html>
