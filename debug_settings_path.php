<?php
echo "<h2>🔍 Debug Settings Path</h2>";

// Test from project root perspective
$paths_to_test = [
    'models/LibrarySettings.php',
    './models/LibrarySettings.php',
    __DIR__ . '/models/LibrarySettings.php',
    '/Applications/MAMP/htdocs/library_project/models/LibrarySettings.php'
];

foreach($paths_to_test as $path) {
    $exists = file_exists($path);
    echo "Path: <strong>$path</strong> → " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "<br>";
    
    if($exists) {
        // Test if we can actually require it
        try {
            require_once $path;
            echo "✅ Can require successfully!<br>";
        } catch(Exception $e) {
            echo "❌ Cannot require: " . $e->getMessage() . "<br>";
        }
    }
    echo "<hr>";
}
?>