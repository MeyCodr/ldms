<?php
include '../../../db_connect.php'; // Ensure correct DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['training_id'])) {
    $training_id = $_POST['training_id'];

    // Update evaluation status in DB
    $query = "UPDATE training_records SET evaluate_status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $training_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>
