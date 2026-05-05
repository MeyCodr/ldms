<?php 
    include "../../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addparticipant') {
            $trainingid = $_POST['trainingid'];
            $participant = $_POST['participant'];

            $sql = "select id from user where staffno = '$participant'";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)) {
                $participantid = mysqli_real_escape_string($conn, $row['id']);
            }
            
            $sql = "INSERT INTO `participation` (`trainingid`,`userid`) values ('$trainingid','$participantid');";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'deleteparticipant') {
            $id = $_POST['id'];
            // $sql = "DELETE FROM `participation` WHERE `id` = '$id'";
            $sql = "DELETE precord, pmerecord
                    FROM participation precord
                    LEFT JOIN pme pmerecord ON pmerecord.participationid = precord.id
                    WHERE precord.id = '$id'";

            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'absent') {
            $id = $_POST['id'];
            $sql = "update participation set attendance = 'ABSENT' WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>