<?php 
    include "../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'deleteojt') {
            $id = $_POST['id'];
            $sql = "DELETE FROM `ojt` WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                $sql = "DELETE FROM `participateojt` WHERE `ojtid` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'delete']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'editojt') {
            $id = $_POST['ojtid'];
            $titleojt = $_POST['titleojt'];
            $startdate1 = $_POST['startdate1'];
            $enddate1 = $_POST['enddate1'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $sql = "update ojt set title = '$titleojt', startdate = '$startdate1', enddate = '$enddate1', starttime = '$starttime', endtime = '$endtime' WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'edit']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>