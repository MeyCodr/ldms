<?php
$sname = "localhost";
$unmae = "admin";
$password = "@dminPhn17";  // Ensure this is correct
$db_name = "phnportalenterto_trms";
// $db_name = "trms-backup";

// Establishing the connection
$conn = mysqli_connect($sname, $unmae, $password, $db_name);

// Check if the connection is successful
if (!$conn) {
    // If the connection fails, you can log the error, but do not include this in the response
    die(json_encode(['message' => 'Connection failed', 'error' => mysqli_connect_error()]));
}

// If the connection is successful, no need to send any other data
// Just return null or success when called from another script
?>
