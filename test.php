<?php
// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

// Check if the file exists
if (file_exists("config/database.php")) {
    echo "✅ database.php file found<br>";
    require_once "config/database.php";
} else {
    echo "❌ database.php file NOT found<br>";
    // List files to debug
    echo "Files in config folder: ";
    $files = scandir('config');
    print_r($files);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if($db) {
        echo "✅ Database connected successfully!<br>";
        
        // Test if we can query the users table
        $query = "SELECT COUNT(*) as user_count FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ Users table is accessible. Found: " . $result['user_count'] . " users<br>";
        
        // Test login credentials
        $query = "SELECT * FROM users WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            echo "✅ Admin user exists! Ready to build the login system.";
        } else {
            echo "❌ Admin user not found.";
        }
    }
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>