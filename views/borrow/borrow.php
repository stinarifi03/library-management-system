<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ .'../../models/Book.php';
require_once __DIR__ .'../../models/Member.php';

$book = new Book();
$member = new Member();

$available_books = $book->read(); // We'll filter for available ones in the dropdown
$all_members = $member->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #764ba2;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📖 Borrow Book</h1>

        <?php if(isset($_GET['message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/BorrowController.php" method="POST">
            <div class="form-group">
                <label for="member_id">Select Member *</label>
                <select id="member_id" name="member_id" required>
                    <option value="">-- Choose a Member --</option>
                    <?php while ($member_row = $all_members->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $member_row['id']; ?>">
                            <?php echo htmlspecialchars($member_row['first_name'] . ' ' . $member_row['last_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="book_id">Select Book *</label>
                <select id="book_id" name="book_id" required>
                    <option value="">-- Choose a Book --</option>
                    <?php while ($book_row = $available_books->fetch(PDO::FETCH_ASSOC)): ?>
                        <?php if($book_row['available_copies'] > 0): ?>
                            <option value="<?php echo $book_row['id']; ?>">
                                <?php echo htmlspecialchars($book_row['title'] . ' by ' . $book_row['author']); ?>
                                (Available: <?php echo $book_row['available_copies']; ?>)
                            </option>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date *</label>
                <input type="date" id="due_date" name="due_date" required 
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                       value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
            </div>

            <button type="submit" name="borrow_book" class="btn">Borrow Book</button>
            <a href="../../dashboard.php" class="btn" style="background: #6c757d;">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>