<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ .'../../models/BorrowRecord.php';

$borrowRecord = new BorrowRecord();
$message = '';

if(isset($_POST['send_overdue_notifications'])) {
    $sent_count = $borrowRecord->sendOverdueNotifications();
    $message = "✅ Sent $sent_count overdue notifications!";
}

if(isset($_POST['send_due_soon_reminders'])) {
    $sent_count = $borrowRecord->sendDueSoonReminders();
    $message = "✅ Sent $sent_count due soon reminders!";
}

// Read email log
$email_log = [];
if(file_exists('../../logs/email_log.txt')) {
    $log_lines = file('../../logs/email_log.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $email_log = array_map('json_decode', $log_lines);
    $email_log = array_reverse($email_log); // Show newest first
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notifications</title>
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
        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.5rem;
        }
        .btn:hover {
            background: #764ba2;
        }
        .btn-overdue {
            background: #dc3545;
        }
        .btn-reminder {
            background: #ffc107;
            color: #000;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .log-entry {
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .log-timestamp {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Email Notifications</h1>
            <a href="../../dashboard.php" class="btn" style="background: #6c757d;">📊 Dashboard</a>
        </div>

        <?php if($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="action-cards">
            <div class="action-card">
                <h3>🔴 Overdue Books</h3>
                <p>Send notifications for books that are past their due date with calculated fines.</p>
                <form method="POST">
                    <button type="submit" name="send_overdue_notifications" class="btn btn-overdue">
                        Send Overdue Notifications
                    </button>
                </form>
            </div>

            <div class="action-card">
                <h3>🟡 Due Soon Reminders</h3>
                <p>Send friendly reminders for books due in the next 2 days.</p>
                <form method="POST">
                    <button type="submit" name="send_due_soon_reminders" class="btn btn-reminder">
                        Send Due Soon Reminders
                    </button>
                </form>
            </div>
        </div>

        <h2>📨 Email Log</h2>
        <?php if(!empty($email_log)): ?>
            <?php foreach($email_log as $log): 
                // Handle both old and new log formats
                $timestamp = $log->timestamp ?? 'Unknown time';
                $to = $log->to ?? 'Unknown recipient';
                $subject = $log->subject ?? 'No subject';
                $message = $log->message ?? 'No message content';
            ?>
            <div class="log-entry">
                <div class="log-timestamp"><?php echo htmlspecialchars($timestamp); ?></div>
                <strong>To:</strong> <?php echo htmlspecialchars($to); ?><br>
                <strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?><br>
                <strong>Message:</strong> <?php echo htmlspecialchars(substr($message, 0, 100)); ?>...
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No emails sent yet. Use the buttons above to send notifications.</p>
        <?php endif; ?>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>