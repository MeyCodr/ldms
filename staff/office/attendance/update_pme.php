<?php
include "../../../dbconn.php";

if (isset($_POST['status']) && isset($_POST['participationid']) && isset($_POST['userid'])) {
    $participationid = $_POST['participationid'];
    $userid = $_POST['userid'];

    // Update the status column in the pme table
    $update_sql = "UPDATE pme SET status = 'completed' WHERE participationid = '$participationid' AND userid = '$userid'";

    if ($conn->query($update_sql) === TRUE) {
        // echo "Status updated successfully!";
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
