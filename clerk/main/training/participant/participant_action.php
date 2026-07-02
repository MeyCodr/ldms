<?php
    include "../../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'deleteparticipant') {
            $id = $_POST['id'];
            $sql = "DELETE FROM participateojt WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['message' => 'delete']);
            } else {
                echo json_encode(['message' => 'error']);
            }
        } else if ($_POST['btn_action'] == 'absent') {
            $id = $_POST['id'];
            $sql = "UPDATE participateojt SET attendance = 'ABSENT' WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['message' => 'delete']);
            } else {
                echo json_encode(['message' => 'error']);
            }
        }
    }
?>
