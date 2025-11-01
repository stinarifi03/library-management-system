<?php
session_start();
require_once 'config/database.php';
require_once 'models/BorrowRecord.php';

// Create a test overdue borrow record
$database = new Database();
$db = $database->getConnection();

// Get first book and member
$book_id = 1; // Use your first book ID
$member_id = 1; // Use your first member ID

$borrowRecord = new BorrowRecord();
$borrowRecord->member_id = $member_id;
$borrowRecord->book_id = $book_id;
$borrowRecord->borrow_date = date('Y-m-d H:i:s', strtotime('-15 days'));
$borrowRecord->due_date = date('Y-m-d H:i:s', strtotime('-1 day'));

if($borrowRecord->borrow()) {
    echo "✅ Test overdue book created successfully!<br>";
    echo "Book ID: $book_id, Member ID: $member_id<br>";
    echo "Due Date: " . date('Y-m-d', strtotime('-1 day')) . " (YESTERDAY)<br>";
    echo "<a href='views/fines/list.php'>Go check fines now!</a>";
} else {
    echo "❌ Failed to create test overdue book";
}
?>