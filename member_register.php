<?php
session_start();
// If member is already logged in, redirect to dashboard
if(isset($_SESSION['member_id'])) {
    header("Location: member_dashboard.php");
    exit;
}

require_once __DIR__ .'config/database.php';

$message = '';
$error = '';

// Handle registration form submission
if(isset($_POST['register'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if(empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check_email_query = "SELECT id FROM members WHERE email = ?";
        $check_stmt = $db->prepare($check_email_query);
        $check_stmt->bindParam(1, $email);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            $error = "This email is already registered. Please use a different email or login.";
        } else {
            // Hash password (using simple method for demo - in production use password_hash())
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new member
            $insert_query = "INSERT INTO members (first_name, last_name, email, phone, password) 
                            VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if($insert_stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password])) {
                $message = "✅ Registration successful! You can now login with your credentials.";
                
                // Clear form
                $_POST = array();
            } else {
                $error = "❌ Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration - Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-width: 500px;
            margin: 2rem auto;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .portal-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        .success-message {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .error-message {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="registration-container fade-in">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
            <h1 style="color: #333; margin-bottom: 0.5rem;">Member Registration</h1>
            <p style="color: #666;">Join our library community</p>
        </div>

        <?php if($message): ?>
            <div class="success-message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           placeholder="Enter your first name">
                </div>

                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           placeholder="Enter your last name">
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                       placeholder="Enter your phone number">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Create a password (min. 6 characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                           placeholder="Confirm your password">
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                <p style="margin: 0; color: #666; font-size: 0.9rem;">
                    🔒 Your password will be securely stored. By registering, you agree to our library terms and conditions.
                </p>
            </div>

            <button type="submit" name="register" class="btn btn-primary" style="width: 100%; justify-content: center;">
                👤 Create My Account
            </button>
        </form>

        <div class="portal-links">
            <p style="color: #666; margin-bottom: 1rem;">Already have an account?</p>
            <a href="member_login.php" class="btn" style="background: #667eea; color: white; margin-bottom: 0.5rem;">
                🔐 Login to Member Portal
            </a>
            <br>
            <a href="index.php" class="btn" style="background: #6c757d; margin-top: 0.5rem;">
                🏢 Admin Portal
            </a>
        </div>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; color: #666; font-size: 0.9rem;">
            <strong>Registration Benefits:</strong><br>
            • Borrow books from our collection<br>
            • Track your reading history<br>
            • Receive due date reminders<br>
            • Access your personal dashboard
        </div>
    </div>

    <script>
        // Real-time password confirmation check
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#dc3545';
            } else {
                confirmPassword.style.borderColor = '#28a745';
            }
        }
        
        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);
    </script>
</body>
</html>