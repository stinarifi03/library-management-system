<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check a specific member
$email = 'stiv@epoka'; // Use the email you registered with
$query = "SELECT * FROM members WHERE email = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $email);
$stmt->execute();

echo "<h2>🔍 Debug Member Account</h2>";

if($stmt->rowCount() > 0) {
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($member);
    echo "</pre>";
    
    echo "<h3>Password Verification Test:</h3>";
    $test_password = 'Stiv44'; // Use the password you tried
    $is_valid = password_verify($test_password, $member['password']);
    echo "Password '{$test_password}' is valid: " . ($is_valid ? 'YES' : 'NO');
    
    if(!$is_valid) {
        echo "<br>Stored password hash: " . $member['password'];
        echo "<br>Try logging in with password: 'member123' (demo fallback)";
    }
} else {
    echo "❌ No member found with email: {$email}";
}
?>