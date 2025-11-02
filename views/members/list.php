<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ .'../../models/Member.php';

$member = new Member();
$stmt = $member->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn { background: #667eea; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #764ba2; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f5f5f5; }
        .actions { display: flex; gap: 0.5rem; }
        .btn-edit { background: #28a745; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 3px; font-size: 0.9rem; }
        .btn-delete { background: #dc3545; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 3px; font-size: 0.9rem; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Manage Members</h1>
            <a href="create.php" class="btn">+ Add New Member</a>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($stmt->rowCount() > 0): ?>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                            <a href="../../controllers/MemberController.php?action=delete&id=<?php echo $row['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No members found. <a href="create.php">Add your first member!</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>