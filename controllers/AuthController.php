<?php
session_start();
require_once __DIR__ .'../config/database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // For now, we'll use simple password verification
            // In production, use password_verify()
            if($password === "password") { // Our default password
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                return true;
            }
        }
        return false;
    }
}

// Handle form submission
if(isset($_POST['login'])) {
    $auth = new AuthController();
    if($auth->login($_POST['username'], $_POST['password'])) {
        header("Location: ../dashboard.php");
        exit;
    } else {
        header("Location: ../index.php?error=1");
        exit;
    }
}
?>