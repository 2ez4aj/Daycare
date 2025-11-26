<?php
// parent/mobile_nav.php
?>
<style>
    .mobile-nav.parent-dashboard {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 0.5rem 0;
    }
    .mobile-nav-item {
        position: relative;
        flex: 1;
        text-align: center;
    }
    .mobile-nav-item.enrollment {
        flex: 0 0 auto;
        margin: 0 5px;
    }
    .mobile-nav-item .nav-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem;
        color: #6c757d;
        text-decoration: none;
    }
    .mobile-nav-item.active .nav-link {
        color: #0d6efd;
    }
    .mobile-nav-item i {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    .mobile-nav-item span {
        font-size: 0.75rem;
    }
</style>

<nav class="mobile-nav parent-dashboard d-lg-none">
    <div class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
        <a href="dashboard.php" class="nav-link">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    </div>
    <div class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : ''; ?>">
        <a href="messages.php" class="nav-link">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="unreadMessagesBadge" style="display: none; font-size: 0.5rem; padding: 0.2em 0.4em;">0</span>
        </a>
    </div>
    <div class="mobile-nav-item enrollment <?php echo basename($_SERVER['PHP_SELF']) === 'enroll.php' ? 'active' : ''; ?>">
        <a href="enroll.php" class="nav-link" style="background-color: #0d6efd; color: white !important; border-radius: 50%; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; margin: 0 auto;">
            <i class="fas fa-user-plus"></i>
            <span style="display: none;">Enroll</span>
        </a>
    </div>
    <div class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
        <a href="notifications.php" class="nav-link">
            <i class="fas fa-bell"></i>
            <span>Alerts</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="unreadNotificationsBadge" style="display: none; font-size: 0.5rem; padding: 0.2em 0.4em;">0</span>
        </a>
    </div>
    <div class="mobile-nav-item dropdown">
        <a href="#" class="nav-link" id="parentMoreDropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-h"></i>
            <span>More</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="parentMoreDropdown">
            <h6 class="dropdown-header">My Account</h6>
            <a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a>
            <a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Account Settings</a>
            <div class="dropdown-divider"></div>
            <h6 class="dropdown-header">Kids &amp; Learning</h6>
            <a class="dropdown-item" href="attendance.php"><i class="fas fa-clipboard-check me-2"></i>Attendance</a>
            <a class="dropdown-item" href="progress.php"><i class="fas fa-chart-line me-2"></i>Progress Reports</a>
            <a class="dropdown-item" href="learning_activities.php"><i class="fas fa-puzzle-piece me-2"></i>Learning Activities</a>
            <a class="dropdown-item ps-4" href="activity_submissions.php"><i class="fas fa-arrow-right me-2"></i>My Submissions</a>
            <a class="dropdown-item" href="announcements.php"><i class="fas fa-bullhorn me-2"></i>Announcements</a>
            <div class="dropdown-divider"></div>
            <h6 class="dropdown-header">Events &amp; Support</h6>
            <a class="dropdown-item" href="events.php"><i class="fas fa-calendar-star me-2"></i>Events</a>
            <a class="dropdown-item" href="faqs.php"><i class="fas fa-question-circle me-2"></i>FAQs</a>
            <a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
    </div>
</nav>
