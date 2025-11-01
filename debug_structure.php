<?php
echo "<h2>🔍 Debug: Checking File Structure</h2>";

$base_dir = __DIR__;

function checkFile($path, $description) {
    if (file_exists($path)) {
        echo "✅ <strong>$description:</strong> EXISTS at $path<br>";
        return true;
    } else {
        echo "❌ <strong>$description:</strong> MISSING at $path<br>";
        return false;
    }
}

echo "<h3>📁 Checking Required Model Files:</h3>";
checkFile($base_dir . '/models/LibrarySettings.php', 'LibrarySettings.php');
checkFile($base_dir . '/models/EmailService.php', 'EmailService.php');
checkFile($base_dir . '/models/BorrowRecord.php', 'BorrowRecord.php');

echo "<h3>📁 Checking Required View Files:</h3>";
checkFile($base_dir . '/views/settings/library.php', 'Settings Library Page');
checkFile($base_dir . '/views/email/notifications.php', 'Email Notifications Page');

echo "<h3>📁 Checking Folder Structure:</h3>";
checkFile($base_dir . '/logs/', 'Logs Folder');
checkFile($base_dir . '/uploads/', 'Uploads Folder');

echo "<hr><h3>🎯 Next Steps:</h3>";
echo "Based on what's missing above, we'll create the necessary files.";
?>