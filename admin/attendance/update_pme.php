<?php
session_start();
include "../../dbconn.php";

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_POST['status']) && isset($_POST['participationid']) && isset($_POST['userid'])) {
    $participationid = (int) $_POST['participationid'];
    $userid = (int) $_POST['userid'];

    // Update the status column in the pme table
    $stmt = $conn->prepare("UPDATE pme SET status = 'completed' WHERE participationid = ? AND userid = ?");
    $stmt->bind_param('ii', $participationid, $userid);

    if ($stmt->execute()) {
        echo "Status updated successfully!";
        header("Location: training.php?userid=" . $userid . "&participationid=" . $participationid);
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
    }
} else {
    echo "Invalid request. Missing data.";
    exit();
}

$conn->close();
?>
