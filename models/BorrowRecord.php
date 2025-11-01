<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/LibrarySettings.php';
require_once __DIR__ . '/EmailService.php';

class BorrowRecord {
    private $conn;
    private $table_name = "borrow_records";

    public $id;
    public $book_id;
    public $member_id;
    public $borrow_date;
    public $due_date;
    public $return_date;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
        // Add this method to get database connection
    public function getConnection() {
        return $this->conn;
    }

    // BORROW a book
    public function borrow() {
        // First, check if book is available
        $book_query = "SELECT available_copies FROM books WHERE id = ?";
        $book_stmt = $this->conn->prepare($book_query);
        $book_stmt->bindParam(1, $this->book_id);
        $book_stmt->execute();
        $book = $book_stmt->fetch(PDO::FETCH_ASSOC);

        if($book && $book['available_copies'] > 0) {
            // Create borrow record
            $query = "INSERT INTO " . $this->table_name . " 
                     SET book_id=:book_id, member_id=:member_id, 
                         borrow_date=:borrow_date, due_date=:due_date";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":book_id", $this->book_id);
            $stmt->bindParam(":member_id", $this->member_id);
            $stmt->bindParam(":borrow_date", $this->borrow_date);
            $stmt->bindParam(":due_date", $this->due_date);

            if($stmt->execute()) {
                // Update book available copies
                $update_query = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $this->book_id);
                $update_stmt->execute();

                return true;
            }
        }
        return false;
    }

    // RETURN a book
    public function returnBook() {
        $query = "UPDATE " . $this->table_name . " 
                 SET return_date=:return_date 
                 WHERE id=:id AND return_date IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":return_date", $this->return_date);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute() && $stmt->rowCount() > 0) {
            // Get book_id to update available copies
            $get_book_query = "SELECT book_id FROM borrow_records WHERE id = ?";
            $get_book_stmt = $this->conn->prepare($get_book_query);
            $get_book_stmt->bindParam(1, $this->id);
            $get_book_stmt->execute();
            $record = $get_book_stmt->fetch(PDO::FETCH_ASSOC);

            if($record) {
                // Update book available copies
                $update_query = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $record['book_id']);
                $update_stmt->execute();
            }

            return true;
        }
        return false;
    }

    // Get active borrow records (not returned)
    public function getActiveBorrows() {
        $query = "SELECT br.*, b.title, b.author, m.first_name, m.last_name 
                  FROM " . $this->table_name . " br
                  JOIN books b ON br.book_id = b.id
                  JOIN members m ON br.member_id = m.id
                  WHERE br.return_date IS NULL 
                  ORDER BY br.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get borrow history
    public function getBorrowHistory() {
        $query = "SELECT br.*, b.title, b.author, m.first_name, m.last_name 
                  FROM " . $this->table_name . " br
                  JOIN books b ON br.book_id = b.id
                  JOIN members m ON br.member_id = m.id
                  ORDER BY br.borrow_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get count of currently borrowed books
    public function getBorrowedCount() {
        $query = "SELECT COUNT(*) as borrowed_count FROM " . $this->table_name . " WHERE return_date IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['borrowed_count'];
    }
        // Calculate and update fines for overdue books
    public function calculateFines() {
        $settings = new LibrarySettings();
        $fine_per_day = $settings->getFinePerDay();
        
        $query = "UPDATE " . $this->table_name . " 
                 SET fine_amount = GREATEST(0, DATEDIFF(NOW(), due_date)) * ?,
                     fine_notes = CASE 
                         WHEN DATEDIFF(NOW(), due_date) > 0 THEN CONCAT('Overdue by ', DATEDIFF(NOW(), due_date), ' days')
                         ELSE NULL 
                     END
                 WHERE return_date IS NULL AND due_date < NOW() AND fine_paid = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fine_per_day);
        return $stmt->execute();
    }

    // Get overdue books with fines
    public function getOverdueBooks() {
        $this->calculateFines(); // Update fines first
        
        $query = "SELECT br.*, b.title, b.author, m.first_name, m.last_name, m.email,
                         DATEDIFF(NOW(), br.due_date) as days_overdue
                  FROM " . $this->table_name . " br
                  JOIN books b ON br.book_id = b.id
                  JOIN members m ON br.member_id = m.id
                  WHERE br.return_date IS NULL 
                  AND br.due_date < NOW()
                  ORDER BY br.due_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Mark fine as paid
    public function payFine($borrow_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET fine_paid = 1, 
                     fine_notes = CONCAT(COALESCE(fine_notes, ''), ' - Paid on ', NOW())
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $borrow_id);
        return $stmt->execute();
    }

    // Get total outstanding fines
    public function getTotalOutstandingFines() {
        $this->calculateFines(); // Update fines first
        
        $query = "SELECT SUM(fine_amount) as total_fines 
                  FROM " . $this->table_name . " 
                  WHERE fine_paid = 0 AND fine_amount > 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_fines'] ? floatval($row['total_fines']) : 0;
    }
        // Send overdue notifications
    public function sendOverdueNotifications() {
        $query = "SELECT br.*, m.first_name, m.email, b.title,
                     DATEDIFF(NOW(), br.due_date) as days_overdue,
                     (DATEDIFF(NOW(), br.due_date) * 0.50) as fine_amount
              FROM borrow_records br
              JOIN members m ON br.member_id = m.id
              JOIN books b ON br.book_id = b.id
              WHERE br.return_date IS NULL 
              AND br.due_date < NOW()";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $sent_count = 0;
        require_once 'EmailService.php';
        $emailService = new EmailService();
        
        while($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Send email (your existing code)
            $emailService->sendOverdueNotification(
                $record['email'],
                $record['first_name'],
                $record['title'],
                $record['due_date'],
                $record['fine_amount']
            );
            
            // ✅ CREATE IN-APP NOTIFICATION
            $this->createNotification(
                $record['member_id'],
                "🔴 Book Overdue!",
                "Your book '{$record['title']}' is {$record['days_overdue']} days overdue. Fine: \${$record['fine_amount']}",
                'overdue'
            );
            
            $sent_count++;
        }
        
        return $sent_count;
    }

    private function createNotification($member_id, $title, $message, $type = 'general') {
        require_once 'Notification.php';
        $notification = new Notification();
        $notification->member_id = $member_id;
        $notification->title = $title;
        $notification->message = $message;
        $notification->type = $type;
        return $notification->create();
    }
    
    // Send due soon reminders (books due in 2 days)
    public function sendDueSoonReminders() {
        $query = "SELECT br.*, m.first_name, m.email, b.title 
              FROM borrow_records br
              JOIN members m ON br.member_id = m.id
              JOIN books b ON br.book_id = b.id
              WHERE br.return_date IS NULL 
              AND br.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $sent_count = 0;
        require_once 'EmailService.php';
        $emailService = new EmailService();
        
        while($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Send email (your existing code)
            $emailService->sendReturnReminder(
                $record['email'],
                $record['first_name'],
                $record['title'],
                $record['due_date']
            );
            
            // ✅ CREATE IN-APP NOTIFICATION
            $this->createNotification(
                $record['member_id'],
                "⏰ Book Due Soon",
                "Your book '{$record['title']}' is due on " . date('M j, Y', strtotime($record['due_date'])),
                'reminder'
            );
            
            $sent_count++;
        }
        
        return $sent_count;
    }
}
?>