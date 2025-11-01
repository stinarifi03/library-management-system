<?php
session_start();
if(isset($_SESSION['member_id'])) {
    header("Location: member_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Member Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-width: 450px;
            margin: 3rem auto;
            text-align: center;
        }
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 5px;
        }
        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        .tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .demo-credentials {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .registration-benefits {
            text-align: left;
            margin-top: 1.5rem;
        }
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #666;
        }
        .benefit-item::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container fade-in">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
            <h1 style="color: #333; margin-bottom: 0.5rem;">Library Member Portal</h1>
            <p style="color: #666;">Access your account or join our library</p>
        </div>

        <!-- Tabs for Login/Register -->
        <div class="tabs">
            <div class="tab active" onclick="showSection('login')">🔐 Login</div>
            <div class="tab" onclick="showSection('register')">👤 Register</div>
        </div>

        <!-- Login Section -->
        <div id="login-section" class="form-section active">
            <form action="controllers/MemberAuthController.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" name="member_login" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    🔐 Login to My Account
                </button>
            </form>

            <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div style="background: linear-gradient(135deg, #dc3545, #e83e8c); color: white; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    ⚠️ Invalid email or password!
                </div>
            <?php endif; ?>

            <div class="demo-credentials">
                <strong>Demo Member Access:</strong><br>
                Use any registered email<br>
                Password: <code>member123</code>
            </div>
        </div>

        <!-- Registration Section -->
        <div id="register-section" class="form-section">
            <form method="POST" action="member_register.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="reg_first_name" class="form-label">First Name *</label>
                        <input type="text" id="reg_first_name" name="first_name" class="form-control" required 
                               placeholder="First name">
                    </div>

                    <div class="form-group">
                        <label for="reg_last_name" class="form-label">Last Name *</label>
                        <input type="text" id="reg_last_name" name="last_name" class="form-control" required 
                               placeholder="Last name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg_email" class="form-label">Email Address *</label>
                    <input type="email" id="reg_email" name="email" class="form-control" required 
                           placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="reg_phone" class="form-label">Phone Number</label>
                    <input type="tel" id="reg_phone" name="phone" class="form-control" 
                           placeholder="(555) 123-4567">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="reg_password" class="form-label">Password *</label>
                        <input type="password" id="reg_password" name="password" class="form-control" required 
                               placeholder="Min. 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="reg_confirm_password" class="form-label">Confirm *</label>
                        <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Confirm password">
                    </div>
                </div>

                <button type="submit" name="register" class="btn" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; width: 100%; justify-content: center;">
                    👤 Create My Library Account
                </button>
            </form>

            <div class="registration-benefits">
                <p style="font-weight: 600; margin-bottom: 0.5rem;">Join our library to enjoy:</p>
                <div class="benefit-item">Borrow from thousands of books</div>
                <div class="benefit-item">Track your reading history</div>
                <div class="benefit-item">Get due date reminders</div>
                <div class="benefit-item">Access personal dashboard</div>
            </div>
        </div>

        <!-- Portal Links -->
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
            <a href="index.php" class="btn" style="background: #667eea; color: white; width: 100%; justify-content: center; margin-bottom: 0.5rem;">
                🏢 Admin Portal Login
            </a>
        </div>

    <script>
        function showSection(section) {
            // Update tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.form-section').forEach(form => {
                form.classList.remove('active');
            });
            
            // Activate selected tab and section
            event.target.classList.add('active');
            document.getElementById(section + '-section').classList.add('active');
        }

        // Password confirmation validation for registration
        const regPassword = document.getElementById('reg_password');
        const regConfirmPassword = document.getElementById('reg_confirm_password');
        
        function validateRegistrationPassword() {
            if (regPassword.value !== regConfirmPassword.value) {
                regConfirmPassword.style.borderColor = '#dc3545';
            } else {
                regConfirmPassword.style.borderColor = '#28a745';
            }
        }
        
        if(regPassword && regConfirmPassword) {
            regPassword.addEventListener('input', validateRegistrationPassword);
            regConfirmPassword.addEventListener('input', validateRegistrationPassword);
        }
    </script>
</body>
</html>