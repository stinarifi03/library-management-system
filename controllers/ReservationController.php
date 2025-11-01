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

    // Create reservation - FIXED VERSION
    public function createReservation($book_id, $member_id) {
        // Set the properties directly on the reservation object
        $this->reservation->book_id = $book_id;
        $this->reservation->member_id = $member_id;
        
        // Call create without parameters
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
    
    if($result === "success") {
        // Get the position in queue from the reservation object
        $position = $controller->getMemberReservations($_SESSION['member_id'])->fetch(PDO::FETCH_ASSOC)['position_in_queue'];
        header("Location: ../member_dashboard.php?message=Book reserved successfully! You are in position #" . $position);
    } else if($result === "already_reserved") {
        header("Location: ../member_dashboard.php?error=You already have a reservation for this book!");
    } else {
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