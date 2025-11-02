<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ .'../../models/BorrowRecord.php';

$borrowRecord = new BorrowRecord();
$active_borrows = $borrowRecord->getActiveBorrows();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
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
        tr:hover {
            background: #f5f5f5;
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .overdue {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>↩️ Return Book</h1>

        <?php if(isset($_GET['message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if($active_borrows->rowCount() > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $active_borrows->fetch(PDO::FETCH_ASSOC)): 
                        $is_overdue = strtotime($row['due_date']) < time();
                    ?>
                    <tr class="<?php echo $is_overdue ? 'overdue' : ''; ?>">
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['borrow_date'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['due_date'])); ?></td>
                        <td>
                            <form action="../../controllers/BorrowController.php" method="POST" style="display: inline;">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="return_book" class="btn">Return</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No active borrow records found. All books are returned!</p>
        <?php endif; ?>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>