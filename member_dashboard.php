<?php
session_start();
if(!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit;
}
$member_id = $_SESSION['member_id'];

require_once 'config/database.php';
require_once 'models/Reservation.php';

// Get unread notifications count
require_once 'models/Notification.php';
$notification = new Notification();
$unread_notifications = $notification->getUnreadNotifications($member_id);
$unread_count = $unread_notifications->rowCount();

$database = new Database();
$db = $database->getConnection();

// Initialize models
$reservation = new Reservation();

// Get member's current borrows
$current_borrows_query = "SELECT br.*, b.title, b.author, b.cover_image 
                         FROM borrow_records br
                         JOIN books b ON br.book_id = b.id
                         WHERE br.member_id = ? AND br.return_date IS NULL
                         ORDER BY br.due_date ASC";
$current_borrows_stmt = $db->prepare($current_borrows_query);
$current_borrows_stmt->bindParam(1, $member_id);
$current_borrows_stmt->execute();

// Get member's borrow history
$borrow_history_query = "SELECT br.*, b.title, b.author 
                        FROM borrow_records br
                        JOIN books b ON br.book_id = b.id
                        WHERE br.member_id = ? AND br.return_date IS NOT NULL
                        ORDER BY br.borrow_date DESC
                        LIMIT 10";
$borrow_history_stmt = $db->prepare($borrow_history_query);
$borrow_history_stmt->bindParam(1, $member_id);
$borrow_history_stmt->execute();

// Get member's reservations
$member_reservations = $reservation->getMemberReservations($member_id);

// Get available books for borrowing
$available_books_query = "SELECT * FROM books WHERE available_copies > 0 ORDER BY title LIMIT 12";
$available_books_stmt = $db->prepare($available_books_query);
$available_books_stmt->execute();

// Get unavailable books that can be reserved
$unavailable_books_query = "SELECT b.*, 
                           (SELECT COUNT(*) FROM book_reservations 
                            WHERE book_id = b.id AND status = 'pending') as reservation_count
                           FROM books b 
                           WHERE b.available_copies = 0 
                           ORDER BY b.title LIMIT 12";
$unavailable_books_stmt = $db->prepare($unavailable_books_query);
$unavailable_books_stmt->execute();

// Handle book borrowing
$borrow_message = '';
if(isset($_POST['borrow_book'])) {
    $book_id = $_POST['book_id'];
    $due_date = $_POST['due_date'];
    
    // Check if book is still available
    $check_availability_query = "SELECT available_copies FROM books WHERE id = ? AND available_copies > 0";
    $check_stmt = $db->prepare($check_availability_query);
    $check_stmt->bindParam(1, $book_id);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0) {
        // Borrow the book
        $borrow_query = "INSERT INTO borrow_records (book_id, member_id, borrow_date, due_date) 
                        VALUES (?, ?, NOW(), ?)";
        $borrow_stmt = $db->prepare($borrow_query);
        
        if($borrow_stmt->execute([$book_id, $member_id, $due_date])) {
            // Update available copies
            $update_query = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(1, $book_id);
            $update_stmt->execute();
            
            $borrow_message = "✅ Book borrowed successfully! Due date: " . date('M j, Y', strtotime($due_date));
            
            // Refresh available books
            $available_books_stmt->execute();
            $unavailable_books_stmt->execute();
        } else {
            $borrow_message = "❌ Failed to borrow book. Please try again.";
        }
    } else {
        $borrow_message = "❌ Sorry, this book is no longer available.";
    }
}

// Handle reservation cancellation
if(isset($_POST['cancel_reservation'])) {
    if($reservation->cancel($_POST['reservation_id'], $member_id)) {
        $borrow_message = "✅ Reservation cancelled successfully!";
        // Refresh reservations
        $member_reservations = $reservation->getMemberReservations($member_id);
    } else {
        $borrow_message = "❌ Failed to cancel reservation.";
    }
}

// Get stats
$current_borrows_count = $current_borrows_stmt->rowCount();
$borrow_history_count = $borrow_history_stmt->rowCount();
$reservations_count = $member_reservations->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .member-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .book-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        .book-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .overdue {
            border-left: 4px solid #dc3545;
        }
        .due-soon {
            border-left: 4px solid #ffc107;
        }
        .reserved {
            border-left: 4px solid #17a2b8;
        }
        .borrow-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .success-message {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .error-message {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .reservation-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #17a2b8;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .queue-position {
            background: #6c757d;
            color: white;
            padding: 0.5rem;
            border-radius: 5px;
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
        .status-available {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .tab-container {
            margin-bottom: 1rem;
        }
        .tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 1rem;
        }
        .tab {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Member Header -->
        <div class="member-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>👤 Welcome, <?php echo $_SESSION['member_name']; ?>!</h1>
                    <p>Library Member Dashboard</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <!-- Notification Bell -->
                    <div class="notification-container" style="position: relative;">
                        <button id="notificationBell" class="notification-bell" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.75rem; border-radius: 50%; cursor: pointer; font-size: 1.5rem;">
                            🔔
                            <?php if($unread_count > 0): ?>
                            <span class="notification-badge" style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">
                                <?php echo $unread_count; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="notification-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; color: #333; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 350px; max-height: 400px; overflow-y: auto; z-index: 1000; margin-top: 0.5rem;">
                            <div style="padding: 1rem; border-bottom: 1px solid #eee; display: flex; justify-content: between; align-items: center;">
                                <strong>Notifications (<?php echo $unread_count; ?> unread)</strong>
                                <?php if($unread_count > 0): ?>
                                <button id="markAllRead" style="background: #667eea; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">
                                    Mark all read
                                </button>
                                <?php endif; ?>
                            </div>
                            <div id="notificationList">
                                <!-- Notifications will be loaded here via JavaScript -->
                                <?php 
                                if($unread_count > 0) {
                                    while($notif = $unread_notifications->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<div class="notification-item" data-id="'.$notif['id'].'" style="padding: 1rem; border-bottom: 1px solid #f8f9fa; cursor: pointer;">';
                                        echo '<div style="font-weight: bold; color: #667eea;">'.$notif['title'].'</div>';
                                        echo '<div style="font-size: 0.9rem; color: #666;">'.$notif['message'].'</div>';
                                        echo '<div style="font-size: 0.7rem; color: #999; margin-top: 0.5rem;">'.date('M j, g:i A', strtotime($notif['created_at'])).'</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div style="padding: 2rem; text-align: center; color: #666;">';
                                    echo 'No new notifications';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <a href="index.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        🏢 Switch to Admin Portal
                    </a>
                    <a href="controllers/MemberAuthController.php?action=logout" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if($borrow_message): ?>
            <div class="success-message">
                <?php echo $borrow_message; ?>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $current_borrows_count; ?></div>
                <div>Currently Borrowed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $reservations_count; ?></div>
                <div>Active Reservations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $borrow_history_count; ?></div>
                <div>Borrow History</div>
            </div>
        </div>

        <!-- Available Books for Borrowing -->
        <div class="section">
            <h2>📚 Available Books to Borrow</h2>
            <p>Browse and borrow from our available collection:</p>
            
            <?php if($available_books_stmt->rowCount() > 0): ?>
                <div class="books-grid">
                    <?php while($book = $available_books_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="book-card">
                        <div class="book-cover" style="height: 150px;">
                            <?php if(!empty($book['cover_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                <div style="color: #6c757d; font-size: 2rem; display: flex; align-items: center; justify-content: center; height: 100%;">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info" style="padding: 1rem;">
                            <div class="book-title" style="font-size: 1rem;"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author" style="font-size: 0.9rem;">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #28a745;">
                                ✅ Available (<?php echo $book['available_copies']; ?> copies)
                            </div>
                            
                            <!-- Borrow Form -->
                            <form method="POST" class="borrow-form">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                
                                <div style="margin-bottom: 0.5rem;">
                                    <label style="font-size: 0.8rem; font-weight: bold;">Due Date:</label>
                                    <input type="date" name="due_date" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>"
                                           style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.8rem;">
                                </div>
                                
                                <button type="submit" name="borrow_book" class="btn" style="background: #28a745; color: white; width: 100%; padding: 0.5rem; font-size: 0.8rem;">
                                    📖 Borrow This Book
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="views/books/search.php" class="btn btn-primary">🔍 Search More Books</a>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                    <p>No books available for borrowing at the moment.</p>
                    <p>Check the reservations section for books you can reserve!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Books Available for Reservation -->
        <div class="section">
            <h2>⏳ Books Available for Reservation</h2>
            <p>Reserve books that are currently borrowed. You'll be notified when they become available!</p>
            
            <?php if($unavailable_books_stmt->rowCount() > 0): ?>
                <div class="books-grid">
                    <?php while($book = $unavailable_books_stmt->fetch(PDO::FETCH_ASSOC)): 
                        // Check if member already reserved this book
                        $check_reservation_query = "SELECT id FROM book_reservations 
                                                   WHERE book_id = ? AND member_id = ? AND status IN ('pending', 'available')";
                        $check_stmt = $db->prepare($check_reservation_query);
                        $check_stmt->bindParam(1, $book['id']);
                        $check_stmt->bindParam(2, $member_id);
                        $check_stmt->execute();
                        $already_reserved = $check_stmt->rowCount() > 0;
                    ?>
                    <div class="book-card">
                        <?php if($already_reserved): ?>
                            <div class="reservation-badge">⭐ Reserved</div>
                        <?php endif; ?>
                        
                        <div class="book-cover" style="height: 150px;">
                            <?php if(!empty($book['cover_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                <div style="color: #6c757d; font-size: 2rem; display: flex; align-items: center; justify-content: center; height: 100%;">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info" style="padding: 1rem;">
                            <div class="book-title" style="font-size: 1rem;"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author" style="font-size: 0.9rem;">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #ffc107;">
                                ⏳ Currently Borrowed
                            </div>
                            <div style="margin-top: 0.25rem; font-size: 0.7rem; color: #6c757d;">
                                <?php echo $book['reservation_count']; ?> people in reservation queue
                            </div>
                            
                            <?php if($already_reserved): ?>
                                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #e7f3ff; border-radius: 5px; text-align: center;">
                                    <span style="color: #17a2b8; font-size: 0.8rem;">✅ You have reserved this book</span>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="controllers/ReservationController.php" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="create_reservation" class="btn" style="background: #17a2b8; color: white; width: 100%; padding: 0.5rem; font-size: 0.8rem;">
                                        ⭐ Reserve This Book
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">⏳</div>
                    <p>All books are currently available for borrowing!</p>
                    <p>Check the available books section above.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Reservations Section -->
        <div class="section">
            <h2>⭐ My Reservations</h2>
            
            <?php if($reservations_count > 0): ?>
                <div class="books-grid">
                    <?php while($reservation = $member_reservations->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="book-card reserved">
                        <div class="book-cover" style="height: 150px;">
                            <?php if(!empty($reservation['cover_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($reservation['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($reservation['title']); ?>">
                            <?php else: ?>
                                <div style="color: #6c757d; font-size: 2rem; display: flex; align-items: center; justify-content: center; height: 100%;">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info" style="padding: 1rem;">
                            <div class="book-title" style="font-size: 1rem;"><?php echo htmlspecialchars($reservation['title']); ?></div>
                            <div class="book-author" style="font-size: 0.9rem;">by <?php echo htmlspecialchars($reservation['author']); ?></div>
                            
                            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                <div><strong>Reserved:</strong> <?php echo date('M j, Y', strtotime($reservation['reservation_date'])); ?></div>
                                <div>
                                    <strong>Status:</strong> 
                                    <span class="status-<?php echo $reservation['status']; ?>">
                                        <?php 
                                        switch($reservation['status']) {
                                            case 'pending': echo '⏳ Waiting in queue'; break;
                                            case 'available': echo '✅ Ready for pickup!'; break;
                                            case 'cancelled': echo '❌ Cancelled'; break;
                                            default: echo ucfirst($reservation['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <?php if($reservation['status'] == 'pending'): ?>
                                    <div class="queue-position">
                                        Position in queue: #<?php echo $reservation['position_in_queue']; ?>
                                    </div>
                                <?php elseif($reservation['status'] == 'available'): ?>
                                    <div style="color: #28a745; font-weight: bold; margin-top: 0.5rem;">
                                        🎉 Book is ready! Pick up within 2 days.
                                    </div>
                                    <div style="font-size: 0.7rem; color: #666;">
                                        Expires: <?php echo date('M j, Y', strtotime($reservation['expiry_date'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($reservation['status'] == 'pending' || $reservation['status'] == 'available'): ?>
                                <form method="POST" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" name="cancel_reservation" class="btn" style="background: #dc3545; color: white; width: 100%; padding: 0.5rem; font-size: 0.8rem;"
                                            onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                        🗑️ Cancel Reservation
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                    <p>You don't have any active reservations.</p>
                    <p>Reserve books from the section above to see them here!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Currently Borrowed Books -->
        <div class="section">
            <h2>📖 Currently Borrowed Books</h2>
            <?php if($current_borrows_count > 0): ?>
                <div class="books-grid">
                    <?php while($borrow = $current_borrows_stmt->fetch(PDO::FETCH_ASSOC)): 
                        $is_overdue = strtotime($borrow['due_date']) < time();
                        $is_due_soon = strtotime($borrow['due_date']) < strtotime('+3 days');
                        $card_class = $is_overdue ? 'overdue' : ($is_due_soon ? 'due-soon' : '');
                    ?>
                    <div class="book-card <?php echo $card_class; ?>">
                        <div class="book-cover" style="height: 150px;">
                            <?php if(!empty($borrow['cover_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($borrow['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($borrow['title']); ?>">
                            <?php else: ?>
                                <div style="color: #6c757d; font-size: 2rem; display: flex; align-items: center; justify-content: center; height: 100%;">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info" style="padding: 1rem;">
                            <div class="book-title" style="font-size: 1rem;"><?php echo htmlspecialchars($borrow['title']); ?></div>
                            <div class="book-author" style="font-size: 0.9rem;">by <?php echo htmlspecialchars($borrow['author']); ?></div>
                            <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                <div><strong>Borrowed:</strong> <?php echo date('M j, Y', strtotime($borrow['borrow_date'])); ?></div>
                                <div style="color: <?php echo $is_overdue ? '#dc3545' : ($is_due_soon ? '#ffc107' : '#28a745'); ?>;">
                                    <strong>Due:</strong> <?php echo date('M j, Y', strtotime($borrow['due_date'])); ?>
                                    <?php if($is_overdue): ?> ⚠️ Overdue<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No books currently borrowed.</p>
            <?php endif; ?>
        </div>

        <!-- Borrow History -->
        <div class="section">
            <h2>📋 Borrow History</h2>
            <?php if($borrow_history_count > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($history = $borrow_history_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['title']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($history['borrow_date'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($history['return_date'])); ?></td>
                            <td><span style="color: #28a745;">✅ Returned</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No borrow history yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tab functionality for future enhancements
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }
    </script>

    <script>
        // Notification system - SIMPLIFIED VERSION
        let notificationCheckInterval;

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
                loadNotifications(); // Refresh when opening
            }
        }

        function loadNotifications() {
            console.log('🔔 Loading notifications...');
            
            fetch('controllers/NotificationController.php?action=get_unread')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('📨 Notifications data:', data);
                    updateNotificationUI(data);
                })
                .catch(error => {
                    console.error('❌ Error loading notifications:', error);
                });
        }

        function updateNotificationUI(data) {
            console.log('🎨 Updating UI with:', data);
            
            // Update badge
            const bell = document.getElementById('notificationBell');
            let badge = bell.querySelector('.notification-badge');
            
            if (data.unread_count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'notification-badge';
                    badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;';
                    bell.appendChild(badge);
                }
                badge.textContent = data.unread_count;
            } else if (badge) {
                badge.remove();
            }
            
            // Update notification list
            const list = document.getElementById('notificationList');
            const countElement = document.querySelector('#notificationDropdown strong');
            
            if (data.notifications && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item" data-id="${notif.id}" style="padding: 1rem; border-bottom: 1px solid #f8f9fa; cursor: pointer;">
                        <div style="font-weight: bold; color: #667eea;">${notif.title}</div>
                        <div style="font-size: 0.9rem; color: #666;">${notif.message}</div>
                        <div style="font-size: 0.7rem; color: #999; margin-top: 0.5rem;">
                            ${new Date(notif.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })}
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div style="padding: 2rem; text-align: center; color: #666;">No new notifications</div>';
            }
            
            // Update count in dropdown header
            if (countElement) {
                countElement.textContent = `Notifications (${data.unread_count || 0} unread)`;
            }
        }

        // Start polling for notifications
        function startNotificationPolling() {
            // Check every 10 seconds for testing (change to 30000 later)
            notificationCheckInterval = setInterval(loadNotifications, 10000);
            console.log('🔄 Started notification polling (10 seconds)');
        }

        // Initialize notification system
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Initializing notification system...');
            
            const bell = document.getElementById('notificationBell');
            if (bell) {
                bell.addEventListener('click', toggleNotifications);
                console.log('✅ Notification bell found and attached');
            } else {
                console.error('❌ Notification bell not found!');
            }
            
            // Mark all as read button
            const markAllRead = document.getElementById('markAllRead');
            if (markAllRead) {
                markAllRead.addEventListener('click', function() {
                    fetch('controllers/NotificationController.php?action=mark_all_read', { 
                        method: 'POST' 
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadNotifications(); // Refresh the list
                        }
                    });
                });
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('notificationDropdown');
                const bell = document.getElementById('notificationBell');
                
                if (dropdown && bell && !dropdown.contains(event.target) && !bell.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });
            
            // Start polling
            startNotificationPolling();
            
            // Load notifications immediately
            loadNotifications();
        });
    </script>
</body>
</html>