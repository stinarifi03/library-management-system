<?php
require_once __DIR__ .'../config/database.php';
require_once __DIR__ .'../models/BorrowRecord.php';

$borrowRecord = new BorrowRecord();

// Send due soon reminders
$due_soon_count = $borrowRecord->sendDueSoonReminders();

// Send overdue notifications  
$overdue_count = $borrowRecord->sendOverdueNotifications();

echo "Sent $due_soon_count due soon reminders and $overdue_count overdue notifications.";
?>