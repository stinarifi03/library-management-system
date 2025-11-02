<?php
session_start();
require_once __DIR__ .'../models/BorrowRecord.php';

// Handle borrowing a book
if(isset($_POST['borrow_book'])) {
    $borrowRecord = new BorrowRecord();
    
    $borrowRecord->member_id = $_POST['member_id'];
    $borrowRecord->book_id = $_POST['book_id'];
    $borrowRecord->borrow_date = date('Y-m-d H:i:s');
    $borrowRecord->due_date = $_POST['due_date'] . ' 23:59:59';

    if($borrowRecord->borrow()) {
        header("Location: ../views/borrow/borrow.php?message=Book borrowed successfully!");
    } else {
        header("Location: ../views/borrow/borrow.php?error=Failed to borrow book. Book may not be available.");
    }
    exit;
}

// Handle returning a book
if(isset($_POST['return_book'])) {
    $borrowRecord = new BorrowRecord();
    
    $borrowRecord->id = $_POST['borrow_id'];
    $borrowRecord->return_date = date('Y-m-d H:i:s');

    if($borrowRecord->returnBook()) {
        header("Location: ../views/borrow/return.php?message=Book returned successfully!");
    } else {
        header("Location: ../views/borrow/return.php?error=Failed to return book.");
    }
    exit;
}
?>