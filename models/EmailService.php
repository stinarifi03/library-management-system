<?php
class EmailService {
    
    public function sendOverdueNotification($member_email, $member_name, $book_title, $due_date, $fine_amount) {
        // For now, we'll log to a file instead of sending actual emails
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'subject' => '📚 Library Book Overdue Notice',
            'message' => "Overdue notice sent to $member_name ($member_email) for book '$book_title' due on $due_date. Fine amount: $$fine_amount",
            'to' => $member_email,
            'type' => 'overdue',
            'member' => $member_name,
            'book' => $book_title
        ];
        
        $log_path = __DIR__ . '/../logs/email_log.txt';
        file_put_contents($log_path, json_encode($log_entry) . PHP_EOL, FILE_APPEND);
        
        return true;
    }
    
    public function sendReturnReminder($member_email, $member_name, $book_title, $due_date) {
        // For now, we'll log to a file instead of sending actual emails
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'subject' => '📚 Book Due Soon Reminder',
            'message' => "Due soon reminder sent to $member_name ($member_email) for book '$book_title' due on $due_date",
            'to' => $member_email,
            'type' => 'reminder',
            'member' => $member_name,
            'book' => $book_title
        ];
        
        $log_path = __DIR__ . '/../logs/email_log.txt';
        file_put_contents($log_path, json_encode($log_entry) . PHP_EOL, FILE_APPEND);
        
        return true;
    }
}
?>