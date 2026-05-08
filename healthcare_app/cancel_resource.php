<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';

$booking_id = $_GET['id'] ?? 0;

// Permanently delete the resource booking
$stmt = $conn->prepare("DELETE FROM resource_bookings WHERE id = ? AND user_id = ? AND status = 'booked'");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);

if($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: my_appointments.php?msg=deleted");
} else {
    header("Location: my_appointments.php?msg=error");
}
$stmt->close();
$conn->close();
exit();
?>