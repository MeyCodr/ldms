<?php
include "../../../../dbconn.php";
session_start(); // Start session to get admin ID

if (isset($_POST['status']) && isset($_POST['trainingid']) && isset($_POST['userid'])) {
    $trainingid = $_POST['trainingid'];
    $userid = $_POST['userid'];

    // Ensure admin ID is available
    if (!isset($_SESSION['id'])) {
        echo "Error: Admin not logged in!";
        exit();
    }

    $admin_id = $_SESSION['id']; // Get logged-in admin ID

    // Fetch necessary values from the pme table
    $query = "SELECT level_percent, level_percent2, behavioral_percent, result_percent FROM pme WHERE trainingid = '$trainingid' AND userid = '$userid'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Calculate Total Mark and Average Mark
        $total_mark = $row['level_percent'] + $row['level_percent2'] + $row['behavioral_percent'] + $row['result_percent'];
        $average_mark = $total_mark / 4;

        // Update the status, total_mark, average_mark, and track admin ID
        $update_sql = "UPDATE pme 
                       SET status = 'verified', 
                           total_mark = '$total_mark', 
                           average_mark = '$average_mark', 
                           verified_by = '$admin_id' 
                       WHERE trainingid = '$trainingid' AND userid = '$userid'";

        if ($conn->query($update_sql) === TRUE) {
            // echo "Verification successful! Admin ID recorded.";
            header("Location: view_pme.php?userid=" . $userid . "&trainingid=" . $trainingid);
            exit();
        } else {
            echo "Error updating records: " . $conn->error;
        }
    } else {
        echo "No matching record found in the PME table.";
    }
} else {
    echo "Invalid request. Missing data.";
    exit();
}

$conn->close();
?>
