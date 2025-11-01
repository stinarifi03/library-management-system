<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Member</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container { max-width: 600px; margin: 0 auto; padding: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea { width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem; }
        .btn { background: #667eea; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #764ba2; }
        .error { color: #dc3545; background: #f8d7da; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Add New Member</h1>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/MemberController.php" method="POST">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>

            <button type="submit" name="create_member" class="btn">Add Member</button>
            <a href="list.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>
    </div>
</body>
</html>