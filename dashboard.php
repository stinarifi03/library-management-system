<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'models/Book.php';
require_once 'models/Member.php';
require_once 'models/BorrowRecord.php';

// Get real statistics
$book = new Book();
$member = new Member();
$borrowRecord = new BorrowRecord();

$total_books = $book->count();
$total_members = $member->count();
$borrowed_books = $borrowRecord->getBorrowedCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .welcome {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .nav-links a {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav-links a:hover {
            background: #764ba2;
        }
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="welcome">
            <h1>Welcome, <?php echo $_SESSION['username']; ?>! 👋</h1>
            <p>Library Management System Dashboard</p>
            <a href="logout.php" style="color: #667eea; float: right;">Logout</a>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1rem;">
            <a href="member_login.php" class="btn" style="background: #28a745;">
                👤 Switch to Member Portal
            </a>
            <a href="logout.php" class="btn" style="background: #6c757d;">Logout</a>
        </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Books</h3>
                <p style="font-size: 2rem; color: #667eea;"><?php echo $total_books; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Members</h3>
                <p style="font-size: 2rem; color: #667eea;"><?php echo $total_members; ?></p>
            </div>
            <div class="stat-card">
                <h3>Books Borrowed</h3>
                <p style="font-size: 2rem; color: #667eea;"><?php echo $borrowed_books; ?></p>
            </div>
        </div>

        <div class="nav-links">
            <a href="views/books/list.php">📚 Manage Books</a>
            <a href="views/members/list.php">👥 Manage Members</a>
            <a href="views/books/search.php">🔍 Search Books</a>
            <a href="views/borrow/borrow.php">📖 Borrow Book</a>
            <a href="views/borrow/return.php">↩️ Return Book</a>
            <a href="views/fines/list.php">💰 Manage Fines</a>
            <a href="views/email/notifications.php">📧 Email Notifications</a>
            <a href="views/reports/dashboard.php">📊 Analytics Reports</a>
            <a href="views/export/manager.php">📤 Export Data</a>
        </div>

        <div class="quick-actions">
            <h3>Quick Stats</h3>
            <p>📊 Your library currently has <strong><?php echo $total_books; ?> books</strong> and <strong><?php echo $total_members; ?> members</strong>.</p>
            <p>📖 There are <strong><?php echo $borrowed_books; ?> books</strong> currently borrowed.</p>
            <?php if($borrowed_books > 0): ?>
                <p>💡 Don't forget to check the <a href="views/borrow/return.php">Return Book</a> page for overdue books!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>