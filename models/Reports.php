<?php
require_once __DIR__ . '/../config/database.php';

class Reports {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get borrowing statistics
    public function getBorrowingStats() {
        $query = "SELECT 
                    COUNT(*) as total_borrows,
                    SUM(CASE WHEN return_date IS NULL THEN 1 ELSE 0 END) as active_borrows,
                    SUM(CASE WHEN return_date IS NOT NULL THEN 1 ELSE 0 END) as completed_borrows,
                    AVG(DATEDIFF(COALESCE(return_date, NOW()), borrow_date)) as avg_borrow_days
                  FROM borrow_records";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get most popular books
    public function getPopularBooks($limit = 5) {
        $query = "SELECT b.title, b.author, COUNT(br.id) as borrow_count
                  FROM borrow_records br
                  JOIN books b ON br.book_id = b.id
                  GROUP BY b.id, b.title, b.author
                  ORDER BY borrow_count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Get member activity
    public function getActiveMembers($limit = 5) {
        $query = "SELECT m.first_name, m.last_name, COUNT(br.id) as borrow_count
                  FROM borrow_records br
                  JOIN members m ON br.member_id = m.id
                  GROUP BY m.id, m.first_name, m.last_name
                  ORDER BY borrow_count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Get monthly borrowing trends
    public function getMonthlyTrends() {
        $query = "SELECT 
                    DATE_FORMAT(borrow_date, '%Y-%m') as month,
                    COUNT(*) as borrow_count
                  FROM borrow_records
                  GROUP BY DATE_FORMAT(borrow_date, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 6";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get overdue statistics
    public function getOverdueStats() {
        $query = "SELECT 
                    COUNT(*) as total_overdue,
                    AVG(DATEDIFF(NOW(), due_date)) as avg_days_overdue,
                    SUM(fine_amount) as total_fines_owed
                  FROM borrow_records
                  WHERE return_date IS NULL AND due_date < NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get genre popularity
    public function getGenrePopularity() {
        $query = "SELECT 
                    b.genre,
                    COUNT(br.id) as borrow_count
                  FROM borrow_records br
                  JOIN books b ON br.book_id = b.id
                  WHERE b.genre IS NOT NULL AND b.genre != ''
                  GROUP BY b.genre
                  ORDER BY borrow_count DESC
                  LIMIT 8";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>