<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $member_id;
    public $title;
    public $message;
    public $type;
    public $is_read;
    public $created_at;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new notification
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET member_id=:member_id, title=:title, message=:message, type=:type";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":type", $this->type);
        
        return $stmt->execute();
    }

    // Get unread notifications for member
    public function getUnreadNotifications($member_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE member_id = ? AND is_read = 0 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Mark notification as read
    public function markAsRead($notification_id, $member_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_read = 1 
                 WHERE id = ? AND member_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $notification_id);
        $stmt->bindParam(2, $member_id);
        
        return $stmt->execute();
    }

    // Mark all notifications as read
    public function markAllAsRead($member_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_read = 1 
                 WHERE member_id = ? AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        
        return $stmt->execute();
    }
}
?>