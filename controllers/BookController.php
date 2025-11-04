<?php
session_start();
require_once '../models/Book.php';

function handleImageUpload($file) {
    if($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Check file type and size
        if(in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $upload_path = '../uploads/' . $filename;
            
            // Move uploaded file
            if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                return $filename;
            }
        }
    }
    return null;
}

// Handle different book actions
if(isset($_POST['create_book'])) {
    // CREATE new book
    $book = new Book();
    
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->isbn = $_POST['isbn'];
    $book->genre = $_POST['genre'];
    $book->total_copies = $_POST['total_copies'];
    $book->available_copies = $_POST['available_copies'];
    
    // Handle image upload
    if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $book->cover_image = handleImageUpload($_FILES['cover_image']);
    }

    if($book->create()) {
        header("Location: ../views/books/list.php?message=Book added successfully!");
    } else {
        header("Location: ../views/books/create.php?error=Failed to add book");
    }
    exit;
}

elseif(isset($_POST['update_book'])) {
    // UPDATE book
    $book = new Book();
    
    $book->id = $_POST['id'];
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->isbn = $_POST['isbn'];
    $book->genre = $_POST['genre'];
    $book->total_copies = $_POST['total_copies'];
    $book->available_copies = $_POST['available_copies'];
    
    // Handle image upload
    if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $book->cover_image = handleImageUpload($_FILES['cover_image']);
    } else {
        // Keep existing image if no new one uploaded
        $book->cover_image = $_POST['existing_cover'] ?? null;
    }

    if($book->update()) {
        header("Location: ../views/books/list.php?message=Book updated successfully!");
    } else {
        header("Location: ../views/books/edit.php?id=" . $book->id . "&error=Failed to update book");
    }
    exit;
}

elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
    // DELETE book
    $book = new Book();
    $book->id = $_GET['id'];

    if($book->delete()) {
        header("Location: ../views/books/list.php?message=Book deleted successfully!");
    } else {
        header("Location: ../views/books/list.php?error=Failed to delete book");
    }
    exit;
}
?>