<?php
session_start();
require_once __DIR__ . '/../models/Notification.php';

header('Content-Type: application/json');

if(!isset($_SESSION['member_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$notification = new Notification();
$member_id = $_SESSION['member_id'];

switch($_GET['action'] ?? '') {
    case 'get_unread':
        $notifications_stmt = $notification->getUnreadNotifications($member_id);
        $notifications = [];
        while($notif = $notifications_stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = $notif;
        }
        
        echo json_encode([
            'unread_count' => count($notifications),
            'notifications' => $notifications
        ]);
        break;
        
    case 'mark_all_read':
        if($notification->markAllAsRead($member_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to mark notifications as read']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>