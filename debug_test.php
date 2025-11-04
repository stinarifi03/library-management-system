<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>📊 Debug: Checking Available Data</h2>";

// Check books
$books_query = "SELECT id, title, available_copies FROM books";
$books_stmt = $db->prepare($books_query);
$books_stmt->execute();

echo "<h3>📚 Available Books:</h3>";
if($books_stmt->rowCount() > 0) {
    while($book = $books_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$book['id']} - {$book['title']} (Available: {$book['available_copies']})<br>";
    }
} else {
    echo "No books found!<br>";
}

// Check members
$members_query = "SELECT id, first_name, last_name FROM members";
$members_stmt = $db->prepare($members_query);
$members_stmt->execute();

echo "<h3>👥 Available Members:</h3>";
if($members_stmt->rowCount() > 0) {
    while($member = $members_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$member['id']} - {$member['first_name']} {$member['last_name']}<br>";
    }
} else {
    echo "No members found!<br>";
}

echo "<hr>";
echo "<h3>🎯 Test Instructions:</h3>";
echo "1. Note down a Book ID that has available_copies > 0<br>";
echo "2. Note down a Member ID<br>";
echo "3. Use those IDs in the test below<br>";
?>

<h3>🧪 Manual Test Overdue Book</h3>
<form method="POST">
    Book ID: <input type="number" name="book_id" required><br>
    Member ID: <input type="number" name="member_id" required><br>
    <button type="submit" name="create_overdue">Create Overdue Book for Testing</button>
</form>

<?php
if(isset($_POST['create_overdue'])) {
    require_once 'models/BorrowRecord.php';
    
    $borrowRecord = new BorrowRecord();
    $borrowRecord->member_id = $_POST['member_id'];
    $borrowRecord->book_id = $_POST['book_id'];
    $borrowRecord->borrow_date = date('Y-m-d H:i:s', strtotime('-15 days'));
    $borrowRecord->due_date = date('Y-m-d H:i:s', strtotime('-1 day'));

    if($borrowRecord->borrow()) {
        echo "<div style='color: green; font-weight: bold;'>✅ Test overdue book created successfully!</div>";
        echo "Book ID: {$_POST['book_id']}, Member ID: {$_POST['member_id']}<br>";
        echo "Due Date: " . date('Y-m-d', strtotime('-1 day')) . " (YESTERDAY)<br>";
        echo "<a href='views/fines/list.php' style='color: blue;'>📊 Go check fines now!</a>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Failed to create test overdue book</div>";
        echo "Possible reasons:<br>";
        echo "- Book might not be available (check available_copies)<br>";
        echo "- Book ID or Member ID doesn't exist<br>";
    }
}
?>