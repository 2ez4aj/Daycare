<!DOCTYPE html>
<html>
<head>
    <title>Root Test</title>
</head>
<body>
    <h1>Root Directory Test</h1>
    <p>This is the root directory of NewDaycare.</p>
    <p>Current directory: <?php echo __DIR__; ?></p>
    <p>Files in this directory:</p>
    <ul>
        <?php 
        $files = scandir(__DIR__);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "<li>" . htmlspecialchars($file) . "</li>";
            }
        }
        ?>
    </ul>
    <p><a href="public_html/">Go to public_html</a></p>
</body>
</html>
