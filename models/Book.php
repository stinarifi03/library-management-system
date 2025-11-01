<?php
require_once __DIR__ . '/../config/database.php';

class Book {
    private $conn;
    private $table_name = "books";

    public $id;
    public $title;
    public $author;
    public $isbn;
    public $genre;
    public $total_copies;
    public $available_copies;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // CREATE - Add new book
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET title=:title, author=:author, isbn=:isbn, genre=:genre, 
                     total_copies=:total_copies, available_copies=:available_copies, 
                     cover_image=:cover_image";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->genre = htmlspecialchars(strip_tags($this->genre));

        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":genre", $this->genre);
        $stmt->bindParam(":total_copies", $this->total_copies);
        $stmt->bindParam(":available_copies", $this->available_copies);

        // After the existing bindParams, add:
        if(!empty($this->cover_image)) {
            $stmt->bindParam(":cover_image", $this->cover_image);
        } else {
            $cover_null = null;
            $stmt->bindParam(":cover_image", $cover_null);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    // Get book details with reservation info
    public function getBookWithReservationInfo($book_id, $member_id = null) {
        $query = "SELECT b.*, 
                         COUNT(CASE WHEN r.status = 'pending' THEN 1 END) as reservation_count,
                         CASE WHEN ? IS NOT NULL THEN 
                             (SELECT COUNT(*) FROM book_reservations 
                              WHERE book_id = b.id AND member_id = ? AND status IN ('pending', 'available'))
                         ELSE 0 END as user_has_reservation
                  FROM books b
                  LEFT JOIN book_reservations r ON b.id = r.book_id AND r.status = 'pending'
                  WHERE b.id = ?
                  GROUP BY b.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->bindParam(2, $member_id);
        $stmt->bindParam(3, $book_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - Get all books
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ - Get single book by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->isbn = $row['isbn'];
            $this->genre = $row['genre'];
            $this->total_copies = $row['total_copies'];
            $this->available_copies = $row['available_copies'];
            return true;
        }
        return false;
    }

    // UPDATE - Update book
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET title=:title, author=:author, isbn=:isbn, genre=:genre, 
                     total_copies=:total_copies, available_copies=:available_copies
                 WHERE id=:id, cover_image=:cover_image";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->genre = htmlspecialchars(strip_tags($this->genre));

        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":genre", $this->genre);
        $stmt->bindParam(":total_copies", $this->total_copies);
        $stmt->bindParam(":available_copies", $this->available_copies);
        $stmt->bindParam(":id", $this->id);

        // After the existing bindParams, add:
        if(!empty($this->cover_image)) {
            $stmt->bindParam(":cover_image", $this->cover_image);
        } else {
            $cover_null = null;
            $stmt->bindParam(":cover_image", $cover_null);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE - Delete book
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Search books
    public function search($search_term) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search 
                 ORDER BY title";
        $stmt = $this->conn->prepare($query);
        
        $search_term = "%{$search_term}%";
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();
        
        return $stmt;
    }

    // Get book count for dashboard
    public function count() {
        $query = "SELECT COUNT(*) as total_books FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_books'];
    }
        // ADVANCED SEARCH with multiple filters
    public function advancedSearch($filters) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];

        // Title/ISBN search
        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE :search OR isbn LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Author search
        if (!empty($filters['author'])) {
            $query .= " AND author LIKE :author";
            $params[':author'] = '%' . $filters['author'] . '%';
        }

        // Genre filter
        if (!empty($filters['genre'])) {
            $query .= " AND genre = :genre";
            $params[':genre'] = $filters['genre'];
        }

        // Availability filter
        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $query .= " AND available_copies > 0";
            } elseif ($filters['availability'] === 'unavailable') {
                $query .= " AND available_copies = 0";
            }
        }
        

        $query .= " ORDER BY title ASC";

        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }
}
?>