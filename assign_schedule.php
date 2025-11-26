<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get all active schedules
    $schedules = $conn->query("SELECT * FROM schedules WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the student
    $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
    $student = $conn->prepare("SELECT id, first_name, last_name FROM students WHERE id = ?")->execute([$student_id])->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        die("Student not found.");
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
        $schedule_id = (int)$_POST['schedule_id'];
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        
        // First, deactivate any existing schedules for this student
        $conn->prepare("UPDATE student_schedules SET is_active = 0 WHERE student_id = ?")->execute([$student_id]);
        
        // Insert the new schedule
        $stmt = $conn->prepare("
            INSERT INTO student_schedules (student_id, schedule_id, start_date, is_active, created_at, updated_at)
            VALUES (?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$student_id, $schedule_id, $start_date]);
        
        echo "<div class='alert alert-success'>Schedule assigned successfully!</div>";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Schedule - Gumamela Daycare Center</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Assign Schedule to <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($schedules)): ?>
                            <div class="alert alert-warning">No active schedules found. Please create a schedule first.</div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="schedule_id" class="form-label">Select Schedule</label>
                                    <select class="form-select" id="schedule_id" name="schedule_id" required>
                                        <option value="">-- Select Schedule --</option>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <option value="<?php echo $schedule['id']; ?>">
                                                <?php echo htmlspecialchars($schedule['schedule_name'] . ' (' . date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time'])) . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Assign Schedule</button>
                                <a href="students.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
