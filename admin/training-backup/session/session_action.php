<?php 
    include "../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addsession') {
            $description = strtoupper($_POST['description']);
            $date1 = $_POST['date1'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = strtoupper($_POST['trainertype']);
            $externalname = strtoupper($_POST['externalname']);
            $internalname = strtoupper($_POST['internalname']);
            $trainingid = $_POST['trainingid'];

            $query = "SELECT staffname FROM user where id = '$internalname'";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_array($result)) {
                $staffname = $row['staffname'];
            }

            if ($trainertype == 'EXTERNAL') {
                $sql = "INSERT INTO `session` (`description`,`date`,`starttime`,`endtime`,`venue`,`trainer`,`trainername`,`trainingid`) values ('$description', '$date1', '$starttime', '$endtime', '$venue', '$trainertype', '$externalname', '$trainingid')";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'insert']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }else if ($trainertype == 'INTERNAL') {
                $sql = "INSERT INTO `session` (`description`,`date`,`starttime`,`endtime`,`venue`,`trainer`,`trainername`,`trainingid`) values ('$description', '$date1', '$starttime', '$endtime', '$venue', '$trainertype', '$staffname', '$trainingid')";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'insert']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }
        }else if ($_POST['btn_action'] == 'editsession') {
            $description = strtoupper($_POST['description']);
            $date1 = $_POST['date1'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = strtoupper($_POST['trainertype']);
            $externalname = strtoupper($_POST['externalname']);
            $internalname = strtoupper($_POST['internalname']);
            $id = $_POST['id'];
            $staffname = '';

            $query = "SELECT staffname FROM user where staffname = '$internalname'";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_array($result)) {
                $staffname = $row['staffname'];
            }

            if ($trainertype == 'EXTERNAL') {
                $sql = "UPDATE `session` SET `description` = '$description', `date` = '$date1', `starttime` = '$starttime', `endtime` = '$endtime', `venue` = '$venue', `trainer` = '$trainertype', `trainername` = '$externalname' WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }else if ($trainertype == 'INTERNAL') {
                $sql = "UPDATE `session` SET `description` = '$description', `date` = '$date1', `starttime` = '$starttime', `endtime` = '$endtime', `venue` = '$venue', `trainer` = '$trainertype', `trainername` = '$staffname' WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }
        }else if ($_POST['btn_action'] == 'deletesession') {
            $id = $_POST['id'];
            $sql = "DELETE FROM `session` WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>