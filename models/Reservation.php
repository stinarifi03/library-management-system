<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Notification.php';

class Reservation {
    private $conn;
    private $table_name = "book_reservations";

    public $id;
    public $book_id;
    public $member_id;
    public $reservation_date;
    public $status;
    public $notification_sent;
    public $expiry_date;
    public $position_in_queue;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create reservation
    public function create() {
        // Check if already reserved by this member
        $check_query = "SELECT id FROM " . $this->table_name . " 
                       WHERE book_id = ? AND member_id = ? AND status IN ('pending', 'available')";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->book_id);
        $check_stmt->bindParam(2, $this->member_id);
        $check_stmt->execute();

        if($check_stmt->rowCount() > 0) {
            return "already_reserved";
        }

        // Get position in queue
        $position_query = "SELECT COUNT(*) as queue_position FROM " . $this->table_name . " 
                          WHERE book_id = ? AND status = 'pending'";
        $position_stmt = $this->conn->prepare($position_query);
        $position_stmt->bindParam(1, $this->book_id);
        $position_stmt->execute();
        $position = $position_stmt->fetch(PDO::FETCH_ASSOC)['queue_position'] + 1;

        $this->position_in_queue = $position;
        $this->expiry_date = date('Y-m-d H:i:s', strtotime('+2 days'));

        $query = "INSERT INTO " . $this->table_name . " 
                 SET book_id=:book_id, member_id=:member_id, position_in_queue=:position_in_queue, 
                     expiry_date=:expiry_date";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":position_in_queue", $this->position_in_queue);
        $stmt->bindParam(":expiry_date", $this->expiry_date);

        if($stmt->execute()) {
            // ✅ Send notification about reservation position
            $this->createNotification(
                $this->member_id,
                "Book Reserved",
                "You've reserved a book! You are position #{$this->position_in_queue} in the queue.",
                'reservation'
            );
            
            return "success";
        }
        return "error";
    }

    // Get member's reservations
    public function getMemberReservations($member_id) {
        $query = "SELECT r.*, b.title, b.author, b.cover_image 
                  FROM " . $this->table_name . " r
                  JOIN books b ON r.book_id = b.id
                  WHERE r.member_id = ? 
                  ORDER BY r.reservation_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Get reservations for a book
    public function getBookReservations($book_id) {
        $query = "SELECT r.*, m.first_name, m.last_name, m.email 
                  FROM " . $this->table_name . " r
                  JOIN members m ON r.member_id = m.id
                  WHERE r.book_id = ? AND r.status = 'pending'
                  ORDER BY r.reservation_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $book_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Cancel reservation
    public function cancel($reservation_id, $member_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'cancelled' 
                 WHERE id = ? AND member_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reservation_id);
        $stmt->bindParam(2, $member_id);
        
        if($stmt->execute()) {
            // ✅ Send notification about cancellation
            $this->createNotification(
                $member_id,
                "Reservation Cancelled",
                "Your book reservation has been cancelled successfully.",
                'reservation'
            );
            return true;
        }
        return false;
    }

    // Check and update reservations when book is returned
    public function processBookReturn($book_id) {
        // Get first reservation in queue
        $query = "SELECT r.*, b.title, m.first_name, m.email 
                FROM " . $this->table_name . " r
                JOIN books b ON r.book_id = b.id
                JOIN members m ON r.member_id = m.id
                WHERE r.book_id = ? AND r.status = 'pending' 
                ORDER BY r.reservation_date ASC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $book_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Mark as available for pickup
            $update_query = "UPDATE " . $this->table_name . " 
                            SET status = 'available', 
                                expiry_date = DATE_ADD(NOW(), INTERVAL 2 DAY),
                                notification_sent = FALSE
                            WHERE id = ?";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(1, $reservation['id']);
            $update_stmt->execute();

            // ✅ CREATE NOTIFICATION for the member
            $this->createNotification(
                $reservation['member_id'],
                "Book Ready for Pickup!",
                "Your reserved book '{$reservation['title']}' is now available! Pick it up within 2 days.",
                'reservation'
            );

            return $reservation;
        }
        return false;
    }

    private function createNotification($member_id, $title, $message, $type = 'reservation') {
        require_once __DIR__ . '/Notification.php';
        $notification = new Notification();
        $notification->member_id = $member_id;
        $notification->title = $title;
        $notification->message = $message;
        $notification->type = $type;
        return $notification->create();
    }

    // Get connection for other operations
    public function getConnection() {
        return $this->conn;
    }
}
?>