<?php
session_start();
require_once 'config/database.php';
require_once 'config/upload.php';

// Debug file upload functionality
echo "<h2>File Upload Debug Information</h2>";

// Check if uploads directory exists and is writable
$uploadsDir = __DIR__ . '/uploads';
$studentsDir = __DIR__ . '/uploads/students';

echo "<h3>Directory Status:</h3>";
echo "Base uploads directory: " . $uploadsDir . "<br>";
echo "Exists: " . (is_dir($uploadsDir) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . "<br><br>";

echo "Students directory: " . $studentsDir . "<br>";
echo "Exists: " . (is_dir($studentsDir) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($studentsDir) ? 'YES' : 'NO') . "<br><br>";

// Test FileUpload class
echo "<h3>FileUpload Class Test:</h3>";
if (class_exists('FileUpload')) {
    echo "FileUpload class exists: YES<br>";
    
    // Test with correct absolute path
    $uploader = new FileUpload(__DIR__ . '/uploads/');
    echo "FileUpload instance created successfully<br>";
} else {
    echo "FileUpload class exists: NO<br>";
}

// Check recent enrollments with file paths
echo "<h3>Recent Enrollments with File Paths:</h3>";
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, first_name, last_name, photo_path, birth_certificate_path, enrollment_date FROM students ORDER BY enrollment_date DESC LIMIT 5");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        echo "No students found in database.<br>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Photo Path</th><th>Certificate Path</th><th>Enrollment Date</th></tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . $student['id'] . "</td>";
            echo "<td>" . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "</td>";
            echo "<td>" . ($student['photo_path'] ? htmlspecialchars($student['photo_path']) : 'NULL') . "</td>";
            echo "<td>" . ($student['birth_certificate_path'] ? htmlspecialchars($student['birth_certificate_path']) : 'NULL') . "</td>";
            echo "<td>" . $student['enrollment_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}

// Test file upload simulation
echo "<h3>File Upload Simulation:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    if (class_exists('FileUpload')) {
        $uploader = new FileUpload(__DIR__ . '/uploads/');
        $result = $uploader->uploadFile($_FILES['test_file'], 'students');
        
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">';
    echo '<input type="file" name="test_file" accept="image/*,.pdf">';
    echo '<button type="submit">Test Upload</button>';
    echo '</form>';
}

// Check PHP upload settings
echo "<h3>PHP Upload Settings:</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "<br>";
?>
