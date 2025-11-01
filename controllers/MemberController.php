<?php
session_start();
require_once '../models/Member.php';

// Handle different member actions
if(isset($_POST['create_member'])) {
    // CREATE new member
    $member = new Member();
    
    $member->first_name = $_POST['first_name'];
    $member->last_name = $_POST['last_name'];
    $member->email = $_POST['email'];
    $member->phone = $_POST['phone'];
    $member->address = $_POST['address'];

    if($member->create()) {
        header("Location: ../views/members/list.php?message=Member added successfully!");
    } else {
        header("Location: ../views/members/create.php?error=Failed to add member");
    }
    exit;
}

elseif(isset($_POST['update_member'])) {
    // UPDATE member
    $member = new Member();
    
    $member->id = $_POST['id'];
    $member->first_name = $_POST['first_name'];
    $member->last_name = $_POST['last_name'];
    $member->email = $_POST['email'];
    $member->phone = $_POST['phone'];
    $member->address = $_POST['address'];

    if($member->update()) {
        header("Location: ../views/members/list.php?message=Member updated successfully!");
    } else {
        header("Location: ../views/members/edit.php?id=" . $member->id . "&error=Failed to update member");
    }
    exit;
}

elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
    // DELETE member
    $member = new Member();
    $member->id = $_GET['id'];

    if($member->delete()) {
        header("Location: ../views/members/list.php?message=Member deleted successfully!");
    } else {
        header("Location: ../views/members/list.php?error=Failed to delete member");
    }
    exit;
}
?>