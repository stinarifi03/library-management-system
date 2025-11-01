<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../models/Book.php';

$book = new Book();
$stmt = $book->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .btn:hover {
            background: #764ba2;
        }
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .book-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .book-cover {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .book-cover .no-image {
            color: #6c757d;
            font-size: 3rem;
        }
        .book-info {
            padding: 1.5rem;
        }
        .book-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .book-author {
            color: #666;
            margin-bottom: 0.5rem;
        }
        .book-details {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .book-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-edit {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .availability {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .available {
            background: #d4edda;
            color: #155724;
        }
        .unavailable {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Manage Books</h1>
            <div>
                <a href="search.php" class="btn" style="background: #17a2b8; margin-right: 1rem;">🔍 Search Books</a>
                <a href="create.php" class="btn">+ Add New Book</a>
            </div>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <div class="books-grid">
            <?php if($stmt->rowCount() > 0): ?>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    $is_available = $row['available_copies'] > 0;
                ?>
                <div class="book-card">
                    <div class="book-cover">
                        <?php if(!empty($row['cover_image'])): ?>
                            <img src="../../uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php else: ?>
                            <div class="no-image">📖</div>
                        <?php endif; ?>
                    </div>
                    <div class="book-info">
                        <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($row['author']); ?></div>
                        <div class="book-details">
                            <span><?php echo htmlspecialchars($row['genre']); ?></span>
                            <span class="availability <?php echo $is_available ? 'available' : 'unavailable'; ?>">
                                <?php echo $is_available ? 'Available' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        <div class="book-details">
                            <span>ISBN: <?php echo htmlspecialchars($row['isbn']); ?></span>
                            <span><?php echo $row['available_copies']; ?>/<?php echo $row['total_copies']; ?> copies</span>
                        </div>
                        <div class="book-actions">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                            <a href="../../controllers/BookController.php?action=delete&id=<?php echo $row['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: white; border-radius: 10px;">
                    <h3>No books found</h3>
                    <p>Get started by adding your first book to the library!</p>
                    <a href="create.php" class="btn">+ Add Your First Book</a>
                </div>
            <?php endif; ?>
        </div>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>