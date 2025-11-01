<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../models/BorrowRecord.php';
require_once '../../models/LibrarySettings.php';

$borrowRecord = new BorrowRecord();
$overdue_books = $borrowRecord->getOverdueBooks();
$total_fines = $borrowRecord->getTotalOutstandingFines();

// Handle fine payment
if(isset($_POST['pay_fine'])) {
    if($borrowRecord->payFine($_POST['borrow_id'])) {
        header("Location: list.php?message=Fine marked as paid!");
        exit;
    } else {
        header("Location: list.php?error=Failed to mark fine as paid");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fines</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .fine-amount {
            font-size: 2rem;
            color: #dc3545;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        .btn {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 3px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #218838;
        }
        .overdue-badge {
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .no-fines {
            text-align: center;
            padding: 3rem;
            color: #28a745;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Manage Fines</h1>
            <a href="../settings/library.php" class="btn" style="background: #6c757d;">⚙️ Library Settings</a>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Outstanding Fines</h3>
                <div class="fine-amount">$<?php echo number_format($total_fines, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Overdue Books</h3>
                <p style="font-size: 2rem; color: #667eea;"><?php echo $overdue_books->rowCount(); ?></p>
            </div>
        </div>

        <?php if($overdue_books->rowCount() > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Fine Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $overdue_books->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['due_date'])); ?></td>
                        <td>
                            <span class="overdue-badge"><?php echo $row['days_overdue']; ?> days</span>
                        </td>
                        <td style="color: #dc3545; font-weight: bold;">
                            $<?php echo number_format($row['fine_amount'], 2); ?>
                        </td>
                        <td>
                            <?php echo $row['fine_paid'] ? '<span style="color: #28a745;">Paid</span>' : '<span style="color: #dc3545;">Unpaid</span>'; ?>
                        </td>
                        <td>
                            <?php if(!$row['fine_paid']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="pay_fine" class="btn">Mark Paid</button>
                            </form>
                            <?php else: ?>
                            <span style="color: #28a745;">✓ Paid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-fines">
                <h3>🎉 No Outstanding Fines!</h3>
                <p>All books are returned on time and there are no fines to collect.</p>
            </div>
        <?php endif; ?>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>