<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container fade-in">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
            <h1 style="color: #333; margin-bottom: 0.5rem;">Library Management</h1>
            <p style="color: #666;">Admin Portal</p>
        </div>
        
        <form action="controllers/AuthController.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required 
                       placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; justify-content: center;">
                🔐 Login to Dashboard
            </button>
        </form>
        
        <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div style="background: linear-gradient(135deg, #dc3545, #e83e8c); color: white; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                ⚠️ Invalid username or password!
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; color: #666; font-size: 0.9rem;">
            <strong>Demo Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>password</code>
        </div>
    </div>
        <div class="portal-switch" style="text-align: center; margin-top: 2rem;">
        <a href="member_login.php" class="btn" style="background: #28a745; color: white;">
            👤 Member Portal Login
        </a>
    </div>
</body>
</html>