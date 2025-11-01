<?php
echo "Current directory: " . __DIR__ . "<br>";
echo "Trying to require: " . __DIR__ . '/models/Book.php' . "<br>";

$book_path = __DIR__ . '/models/Book.php';
if (file_exists($book_path)) {
    echo "✅ Book.php file EXISTS at: " . $book_path . "<br>";
    require_once $book_path;
    echo "✅ Book class loaded successfully!<br>";
} else {
    echo "❌ Book.php file NOT FOUND at: " . $book_path . "<br>";
    
    // List all files in models directory
    $models_dir = __DIR__ . '/models/';
    if (is_dir($models_dir)) {
        echo "Files in models directory: <br>";
        $files = scandir($models_dir);
        foreach ($files as $file) {
            echo "- " . $file . "<br>";
        }
    } else {
        echo "❌ models directory doesn't exist!<br>";
    }
}
?>