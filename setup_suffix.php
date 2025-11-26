<?php
// Bootstrap the application
require_once __DIR__ . '/app/core/bootstrap.php';

// Get database connection
$db = getDBConnection();

// Create controller instance
$controller = new SetupController($db);

// Handle the request
$result = $controller->addSuffixColumn();

// Load the view
$pageTitle = 'Database Update - Add Suffix Column';
$pageStyles = [
    'assets/bootstrap/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];
$pageScripts = [
    'assets/bootstrap/js/bootstrap.bundle.min.js'
];

// Include the header
include __DIR__ . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5>Database Update - Add Suffix Column</h5>
                </div>
                <div class="card-body">
                    <?php include __DIR__ . '/app/views/setup/suffix.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include __DIR__ . '/app/views/includes/footer.php';
?>
