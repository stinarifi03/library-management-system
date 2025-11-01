<?php
session_start();
require_once '../config/database.php';

class MemberAuthController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($email, $password) {
        $query = "SELECT * FROM members WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $member_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // For demo purposes, we'll check both methods
            // First try password_verify, then fallback to demo password
            $login_success = false;
            
            // Method 1: Check if password is hashed and verify
            if(isset($member_data['password'])) {
                if(password_verify($password, $member_data['password'])) {
                    $login_success = true;
                }
                // Method 2: Check for demo password (for existing members)
                elseif($password === "member123") {
                    $login_success = true;
                }
            }
            
            if($login_success) {
                $_SESSION['member_id'] = $member_data['id'];
                $_SESSION['member_name'] = $member_data['first_name'] . ' ' . $member_data['last_name'];
                $_SESSION['member_email'] = $member_data['email'];
                
                // Update last login
                $update_query = "UPDATE members SET last_login = NOW() WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $member_data['id']);
                $update_stmt->execute();
                
                return true;
            }
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: ../member_login.php");
        exit;
    }
}

// Handle member login form submission
if(isset($_POST['member_login'])) {
    $auth = new MemberAuthController();
    if($auth->login($_POST['email'], $_POST['password'])) {
        header("Location: ../member_dashboard.php");
        exit;
    } else {
        header("Location: ../member_login.php?error=1");
        exit;
    }
}

// Handle member logout
if(isset($_GET['action']) && $_GET['action'] == 'logout') {
    $auth = new MemberAuthController();
    $auth->logout();
}
?>