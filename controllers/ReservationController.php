<?php
session_start();
require_once '../models/Reservation.php';
require_once '../models/Book.php';

class ReservationController {
    private $reservation;
    private $book;

    public function __construct() {
        $this->reservation = new Reservation();
        $this->book = new Book();
    }

    // Create reservation
    public function createReservation($book_id, $member_id) {
        $this->reservation->book_id = $book_id;
        $this->reservation->member_id = $member_id;
        
        return $this->reservation->create();
    }

    // Cancel reservation
    public function cancelReservation($reservation_id, $member_id) {
        return $this->reservation->cancel($reservation_id, $member_id);
    }

    // Get member reservations
    public function getMemberReservations($member_id) {
        return $this->reservation->getMemberReservations($member_id);
    }
}

// Handle reservation requests
if(isset($_POST['create_reservation'])) {
    $controller = new ReservationController();
    $result = $controller->createReservation($_POST['book_id'], $_SESSION['member_id']);
    
    switch($result) {
        case 'success':
            header("Location: ../member_dashboard.php?message=Book reserved successfully! You are in position #" . $controller->reservation->position_in_queue);
            break;
        case 'already_reserved':
            header("Location: ../member_dashboard.php?error=You already have a reservation for this book!");
            break;
        default:
            header("Location: ../member_dashboard.php?error=Failed to create reservation");
    }
    exit;
}

if(isset($_POST['cancel_reservation'])) {
    $controller = new ReservationController();
    if($controller->cancelReservation($_POST['reservation_id'], $_SESSION['member_id'])) {
        header("Location: ../member_dashboard.php?message=Reservation cancelled successfully!");
    } else {
        header("Location: ../member_dashboard.php?error=Failed to cancel reservation");
    }
    exit;
}
?>