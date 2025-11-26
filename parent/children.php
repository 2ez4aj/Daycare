<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: ../index.php');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle form submission for updating child information
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_child'])) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $child_id = $_POST['child_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $birthdate = $_POST['birthdate'];
        $allergies = trim($_POST['allergies']);
        $medical_notes = trim($_POST['medical_notes']);
        
        // Input validation
        if (empty($first_name) || empty($last_name) || empty($birthdate)) {
            $error = 'First name, last name, and birthdate are required.';
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $error]);
                exit();
            }
            $_SESSION['error_message'] = $error;
            header('Location: children.php');
            exit();
        }
            
            // Verify the child belongs to the logged-in parent
            $stmt = $conn->prepare("SELECT id FROM students WHERE id = ? AND parent_id = ?");
            $stmt->execute([$child_id, $_SESSION['user_id']]);
            
            if (!$stmt->fetch()) {
                $error = 'Invalid child record or permission denied.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit();
                }
                $_SESSION['error_message'] = $error;
                header('Location: children.php');
                exit();
            }
            
            // Update child information
            $stmt = $conn->prepare("
                UPDATE students 
                SET first_name = ?, last_name = ?, date_of_birth = ?, 
                    allergies = ?, medical_conditions = ?, updated_at = NOW()
                WHERE id = ? AND parent_id = ?
            ");
            
            // Log the update attempt with all data
            $updateData = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $birthdate,  // Changed from birthdate to date_of_birth
                'allergies' => $allergies,
                'medical_conditions' => $medical_notes,  // Changed from medical_notes to medical_conditions
                'child_id' => $child_id,
                'parent_id' => $_SESSION['user_id']
            ];
            error_log("Update attempt with data: " . print_r($updateData, true));
            
            // Execute the update
            $result = $stmt->execute([
                $first_name, 
                $last_name, 
                $birthdate, 
                $allergies, 
                $medical_notes, 
                $child_id, 
                $_SESSION['user_id']
            ]);
            
            $affectedRows = $stmt->rowCount();
            error_log("Update query executed. Rows affected: " . $affectedRows);
            
            if ($affectedRows > 0) {
                $message = 'Child information updated successfully!';
                
                // If it's an AJAX request, return JSON response
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => $message,
                        'redirect' => 'children.php'  // Add redirect URL
                    ]);
                    exit();
                }
                
                $_SESSION['success_message'] = $message;
            } else {
                $errorInfo = $stmt->errorInfo();
                $error = 'Failed to update child information. ' . ($errorInfo[2] ?? 'Unknown database error');
                error_log("Database error: " . $error);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit();
                }
                
                $_SESSION['error_message'] = $error;
            }
            
            // Redirect for non-AJAX requests
            if (!$isAjax) {
                header('Location: children.php');
                exit();
            }
        
        if (!$isAjax) {
            header('Location: children.php');
            exit();
        }
    }
    
    // Get parent's children with their latest progress report
    $stmt = $conn->prepare("
        SELECT s.*, 
               (SELECT created_at FROM progress_reports 
                WHERE student_id = s.id 
                ORDER BY created_at DESC LIMIT 1) as last_report_date
        FROM students s 
        WHERE s.parent_id = ? AND s.status = 'active'
        ORDER BY s.first_name, s.last_name
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread notifications count for sidebar
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_notifications = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    // Get unread messages count for sidebar
    $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_messages = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
    
} catch (Exception $e) {
    $children = [];
    $unread_notifications = 0;
    $unread_messages = 0;
    $error_message = 'Error loading children data: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children - Gumamela Daycare Center</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/parent.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/mobile_nav.css" rel="stylesheet">
    <style>
        .child-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }
        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .progress-report-card {
            border-left: 4px solid #4e73df;
        }
    </style>
</head>
<body>
    <div class="parent-container">
        <?php include 'parent_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Children</h1>
                <a href="enroll.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Enroll New Child
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($children)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-child fa-4x text-muted mb-3"></i>
                        <h3>No Children Enrolled</h3>
                        <p class="text-muted">You haven't enrolled any children yet.</p>
                        <a href="enroll.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Enroll Your First Child
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($children as $child): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card child-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h4 class="card-title mb-1">
                                                <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                                            </h4>
                                            <p class="text-muted mb-2">
                                                <?php 
                                                    if (!empty($child['date_of_birth']) && $child['date_of_birth'] !== '0000-00-00') {
                                                        $birthdate = new DateTime($child['date_of_birth']);
                                                        $today = new DateTime();
                                                        $age = $birthdate->diff($today)->y;
                                                        echo $age . ' years old';
                                                    } else {
                                                        echo 'Age not specified';
                                                    }
                                                ?>
                                            </p>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    id="childActionsDropdown<?php echo $child['id']; ?>" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="childActionsDropdown<?php echo $child['id']; ?>">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                                       data-bs-target="#editChildModal<?php echo $child['id']; ?>">
                                                        <i class="fas fa-edit me-2"></i>Edit Information
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="progress.php?child_id=<?php echo $child['id']; ?>">
                                                        <i class="fas fa-chart-line me-2"></i>View Progress
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                                       data-bs-target="#downloadReportModal<?php echo $child['id']; ?>">
                                                        <i class="fas fa-download me-2"></i>Download Report
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="small text-uppercase text-muted mb-2">Medical Information</h6>
                                        <?php if (!empty($child['allergies'])): ?>
                                            <p class="mb-1">
                                                <i class="fas fa-allergies text-danger me-2"></i>
                                                <strong>Allergies:</strong> <?php echo htmlspecialchars($child['allergies']); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                No known allergies
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($child['medical_notes'])): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-notes-medical text-primary me-2"></i>
                                                <strong>Notes:</strong> <?php echo htmlspecialchars($child['medical_notes']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($child['last_report_date']): ?>
                                        <div class="progress-report-card p-3 bg-light rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">Latest Progress Report</h6>
                                                <span class="badge bg-primary">
                                                    <?php echo date('M j, Y', strtotime($child['last_report_date'])); ?>
                                                </span>
                                            </div>
                                            <a href="progress.php?child_id=<?php echo $child['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-eye me-1"></i> View Full Report
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No progress reports available yet.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Child Modal -->
                        <div class="modal fade" id="editChildModal<?php echo $child['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Child Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="editChildForm-<?php echo $child['id']; ?>">
                                        <div class="modal-body">
                                            <input type="hidden" name="child_id" value="<?php echo $child['id']; ?>">
                                            <input type="hidden" name="update_child" value="1">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="first_name" 
                                                       value="<?php echo htmlspecialchars($child['first_name']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="last_name" 
                                                       value="<?php echo htmlspecialchars($child['last_name']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Birthdate</label>
                                                <input type="date" class="form-control" name="birthdate" 
                                                       value="<?php echo !empty($child['date_of_birth']) && $child['date_of_birth'] !== '0000-00-00' ? $child['date_of_birth'] : ''; ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Allergies</label>
                                                <input type="text" class="form-control" name="allergies" 
                                                       value="<?php echo !empty($child['allergies']) ? htmlspecialchars($child['allergies']) : ''; ?>" 
                                                       placeholder="List any allergies or leave blank if none">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Medical Notes</label>
                                                <textarea class="form-control" name="medical_notes" rows="3" 
                                                          placeholder="Any additional medical information"><?php echo !empty($child['medical_notes']) ? htmlspecialchars($child['medical_notes']) : ''; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Download Report Modal -->
                        <div class="modal fade" id="downloadReportModal<?php echo $child['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Download Progress Report</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Select the report format you'd like to download:</p>
                                        <div class="d-grid gap-2">
                                            <a href="download_report.php?child_id=<?php echo $child['id']; ?>&format=pdf" 
                                               class="btn btn-danger">
                                                <i class="fas fa-file-pdf me-2"></i>Download as PDF
                                            </a>
                                            <a href="download_report.php?child_id=<?php echo $child['id']; ?>&format=excel" 
                                               class="btn btn-success">
                                                <i class="fas fa-file-excel me-2"></i>Download as Excel
                                            </a>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/parent.js"></script>
    <script>
    // Handle form submission with AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const editForms = document.querySelectorAll('form[id^="editChildForm"]');
        
        editForms.forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                const modalId = this.closest('.modal').id;
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById(modalId));
                
                try {
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    
                    const formData = new FormData(this);
                    
                    // Add AJAX header
                    formData.append('is_ajax', '1');
                    
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                    // Show success message
                    showAlert('success', result.message);
                    
                    // Close the modal after a short delay
                    setTimeout(() => {
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        // Redirect to the same page to refresh all data
                        if (result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                    } else {
                        throw new Error(result.message || 'Failed to update child information');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('danger', error.message || 'An error occurred while updating the child information.');
                } finally {
                    // Reset button state
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                }
            });
        });
        
        // Function to show alert messages
        function showAlert(type, message) {
            // Remove any existing alerts
            const existingAlerts = document.querySelectorAll('.ajax-alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create and show new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show ajax-alert`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insert the alert at the top of the main content
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.insertBefore(alertDiv, mainContent.firstChild);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alertDiv);
                    bsAlert.close();
                }, 5000);
            } else {
                // Fallback to regular alert if we can't find the main content
                alert(message);
            }
        }
    });
    </script>
</body>
</html>
