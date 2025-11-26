<?php
session_start();
require_once '../config/database.php';
require_once '../config/upload.php';

// Check if user is logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: ../index.php');
    exit();
}

// Handle parent submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_activity') {
    try {
        $conn = getDBConnection();
        
        // Ensure submissions table exists
        $conn->exec("
            CREATE TABLE IF NOT EXISTS learning_activity_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                activity_id INT NOT NULL,
                parent_id INT NOT NULL,
                student_id INT NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_size INT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (activity_id) REFERENCES learning_activities(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
            )
        ");
        
        $activity_id = (int)$_POST['activity_id'];
        $student_id = (int)$_POST['student_id'];
        $parent_id = $_SESSION['user_id'];
        $notes = trim($_POST['notes'] ?? '');
        
        // Validate child belongs to parent
        $stmt = $conn->prepare("SELECT id FROM students WHERE id = ? AND parent_id = ? AND status = 'active'");
        $stmt->execute([$student_id, $parent_id]);
        if (!$stmt->fetch()) {
            header('Location: learning_activities.php?error=invalid_child');
            exit();
        }
        
        if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
            header('Location: learning_activities.php?error=file_required');
            exit();
        }
        
        // Upload file
        if (!class_exists('FileUpload')) {
            header('Location: learning_activities.php?error=upload_unavailable');
            exit();
        }
        
        $uploadsDir = dirname(__DIR__) . '/uploads/';
        $uploader = new FileUpload($uploadsDir);
        $uploadResult = $uploader->uploadFile($_FILES['submission_file'], 'learning_activity_submissions');
        
        if (!$uploadResult['success']) {
            header('Location: learning_activities.php?error=' . urlencode($uploadResult['error']));
            exit();
        }
        
        $basePath = str_replace('\\', '/', dirname(__DIR__));
        $fullPath = str_replace('\\', '/', $uploadResult['path']);
        $relativePath = str_replace($basePath . '/', '', $fullPath);
        
        $stmt = $conn->prepare("
            INSERT INTO learning_activity_submissions
                (activity_id, parent_id, student_id, file_path, file_name, file_size, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $activity_id,
            $parent_id,
            $student_id,
            $relativePath,
            $_FILES['submission_file']['name'],
            $_FILES['submission_file']['size'],
            $notes
        ]);
        
        header('Location: learning_activities.php?success=submission_saved');
        exit();
        
    } catch (Exception $e) {
        header('Location: learning_activities.php?error=' . urlencode('Submission failed: ' . $e->getMessage()));
        exit();
    }
}

$children = [];
$submissions_by_activity = [];

try {
    $conn = getDBConnection();
    
    // Ensure submissions table exists for read operations
    $conn->exec("
        CREATE TABLE IF NOT EXISTS learning_activity_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_id INT NOT NULL,
            parent_id INT NOT NULL,
            student_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (activity_id) REFERENCES learning_activities(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )
    ");
    
    // Check if parent has enrolled children
    $stmt = $conn->prepare("SELECT COUNT(*) as child_count FROM students WHERE parent_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $child_count = $stmt->fetch(PDO::FETCH_ASSOC)['child_count'];
    
    // If no enrolled children, redirect or show access denied
    $children = [];
    if ($child_count == 0) {
        $no_access = true;
        $submissions_by_activity = [];
    } else {
        $no_access = false;
        
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name
            FROM students
            WHERE parent_id = ? AND status = 'active'
            ORDER BY first_name, last_name
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get learning activities
        $stmt = $conn->prepare("
            SELECT la.*, u.first_name, u.last_name 
            FROM learning_activities la 
            JOIN users u ON la.created_by = u.id 
            WHERE la.status = 'active'
            ORDER BY la.created_at DESC
        ");
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get submissions for this parent
        $stmt = $conn->prepare("
            SELECT las.*, s.first_name AS child_first_name, s.last_name AS child_last_name
            FROM learning_activity_submissions las
            JOIN students s ON las.student_id = s.id
            WHERE las.parent_id = ?
            ORDER BY las.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($submissions as $submission) {
            $submissions_by_activity[$submission['activity_id']][] = $submission;
        }
    }
    
    // Get unread messages count for sidebar
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    // Get unread notifications count for sidebar badge
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $activities = [];
    $children = [];
    $submissions_by_activity = [];
    $no_access = true;
    $unread_messages = 0;
    $unread_notifications = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Activities - Gumamela Daycare Center</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/parent.css" rel="stylesheet">
    <link href="../assets/css/mobile_nav.css" rel="stylesheet">
<body>
    <div class="parent-container">
        <?php include 'parent_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-graduation-cap me-3"></i>Learning Activities</h1>
            </div>

            <div class="content-body">
                <?php if (isset($_GET['success']) && $_GET['success'] === 'submission_saved'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Learning activity submission uploaded successfully! Our team will review it soon.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                            $errorMap = [
                                'invalid_child' => 'The selected child is not associated with your account.',
                                'file_required' => 'Please attach a file before submitting.',
                                'upload_unavailable' => 'File uploads are currently unavailable.',
                            ];
                            $errorKey = $_GET['error'];
                            echo htmlspecialchars($errorMap[$errorKey] ?? $_GET['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($no_access): ?>
                    <!-- No Access Message -->
                    <div class="no-access-container">
                        <div class="no-access-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>Access Restricted</h3>
                        <p class="text-muted mb-4">
                            Learning activities are only available to parents with enrolled children.<br>
                            Please enroll your child first to access educational content and activities.
                        </p>
                        <a href="enroll.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Enroll Your Child
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Activities Available -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Learning Activities for Your Child</strong><br>
                                These activities are designed to support your child's learning at home, especially during remote learning periods.
                            </div>
                        </div>
                    </div>

                    <?php if (empty($activities)): ?>
                        <div class="text-center py-5">
                            <div class="display-1 text-muted mb-3">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h4>No learning activities available</h4>
                            <p class="text-muted">Check back later for new educational activities and resources.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($activities as $activity): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="activity-card">
                                <div class="activity-header">
                                    <span class="activity-label"><i class="fas fa-home me-2"></i>Home Learning Task</span>
                                    <h4 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                    <div class="activity-meta">
                                        <span><i class="fas fa-user-tie"></i><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></span>
                                        <span><i class="fas fa-calendar-day"></i><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></span>
                                        <?php if (!empty($activity['age_group'])): ?>
                                            <span><i class="fas fa-child"></i><?php echo htmlspecialchars($activity['age_group']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="activity-content">
                                    <!-- Description -->
                                    <div class="activity-section">
                                        <div class="section-icon text-primary"><i class="fas fa-info-circle"></i></div>
                                        <div>
                                            <h6>Description</h6>
                                            <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($activity['description'])); ?></p>
                                        </div>
                                    </div>

                                    <!-- Attachment -->
                                    <?php if ($activity['attachment_path']): ?>
                                        <div class="activity-section">
                                            <div class="section-icon text-success"><i class="fas fa-paperclip"></i></div>
                                            <div class="flex-grow-1">
                                                <h6>Teacher Resource</h6>
                                                <?php if ($activity['attachment_type'] === 'image'): ?>
                                                    <img src="../<?php echo $activity['attachment_path']; ?>" 
                                                         alt="Activity Image" 
                                                         class="attachment-preview"
                                                         onclick="window.open('../<?php echo $activity['attachment_path']; ?>', '_blank')">
                                                <?php else: ?>
                                                    <div class="file-download file-download-clickable" onclick="window.open('../<?php echo $activity['attachment_path']; ?>', '_blank')">
                                                        <div class="file-icon">
                                                            <i class="fas fa-file-<?php echo $activity['attachment_type'] === 'document' ? 'pdf' : ($activity['attachment_type'] === 'video' ? 'video' : 'alt'); ?>"></i>
                                                        </div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['attachment_name']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo number_format($activity['attachment_size'] / 1024, 1); ?> KB • 
                                                            Click to <?php echo $activity['attachment_type'] === 'video' ? 'watch' : 'download'; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Materials Needed -->
                                    <?php if ($activity['materials_needed']): ?>
                                        <div class="activity-section">
                                            <div class="section-icon text-warning"><i class="fas fa-tools"></i></div>
                                            <div>
                                                <h6>Materials Needed</h6>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($activity['materials_needed'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Instructions -->
                                    <?php if ($activity['instructions']): ?>
                                        <div class="activity-section">
                                            <div class="section-icon text-info"><i class="fas fa-list-ol"></i></div>
                                            <div>
                                                <h6>Instructions</h6>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($activity['instructions'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Learning Objectives -->
                                    <?php if ($activity['learning_objectives']): ?>
                                        <div class="activity-section">
                                            <div class="section-icon text-danger"><i class="fas fa-bullseye"></i></div>
                                            <div>
                                                <h6>Learning Objectives</h6>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($activity['learning_objectives'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                            
                                            <!-- Parent Submission Upload -->
                                            <div class="submission-box mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Submit Your Child's Work</h6>
                                <small class="text-muted">Accepted: JPG, PNG, PDF, DOCX, MP4</small>
                            </div>
                            <?php if (empty($children)): ?>
                                <div class="alert alert-warning p-2 mb-0">
                                    You need an active enrolled child to submit activity work.
                                </div>
                            <?php else: ?>
                                <form method="POST" enctype="multipart/form-data" class="activity-submission-form">
                                    <input type="hidden" name="action" value="submit_activity">
                                    <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Select Child</label>
                                            <select class="form-select" name="student_id" required>
                                                <option value="">Choose Child</option>
                                                <?php foreach ($children as $child): ?>
                                                    <option value="<?php echo $child['id']; ?>">
                                                        <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Upload File</label>
                                            <input type="file" class="form-control" name="submission_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.mp4" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes (optional)</label>
                                        <textarea class="form-control" name="notes" rows="2" placeholder="Share any observations or feedback..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-paper-plane me-1"></i>Submit Work
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($submissions_by_activity[$activity['id']])): ?>
                            <div class="submission-list mt-3">
                                <h6 class="mb-2 text-muted">
                                    <i class="fas fa-folder-open me-2"></i>Your uploads for this activity
                                </h6>
                                <?php foreach ($submissions_by_activity[$activity['id']] as $submission): ?>
                                    <div class="submission-item">
                                        <div class="submission-details">
                                            <strong><?php echo htmlspecialchars($submission['child_first_name'] . ' ' . $submission['child_last_name']); ?></strong>
                                            <small class="text-muted d-block">
                                                Submitted on <?php echo date('M d, Y g:i A', strtotime($submission['created_at'])); ?>
                                            </small>
                                            <?php if (!empty($submission['notes'])): ?>
                                                <small class="text-muted d-block">
                                                    “<?php echo htmlspecialchars($submission['notes']); ?>”
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <a href="../<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-download me-1"></i><?php echo htmlspecialchars($submission['file_name']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                                            <!-- Activity Footer -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Posted <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                                </small>
                                                <?php if ($activity['attachment_path']): ?>
                                                    <a href="../<?php echo $activity['attachment_path']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download me-1"></i>
                                                        <?php echo $activity['attachment_type'] === 'video' ? 'Watch' : 'Download'; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add click handlers for file downloads
        document.querySelectorAll('.file-download').forEach(function(element) {
            element.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Add click handlers for image previews
        document.querySelectorAll('.attachment-preview').forEach(function(img) {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    </script>
    <script src="../assets/js/mobile_nav.js"></script>
    <?php include 'mobile_nav.php'; ?>
</body>
</html>
