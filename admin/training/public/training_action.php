<?php 
    include "../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addtraining') {
            $title = strtoupper($_POST['title1']);
            $venue = strtoupper($_POST['venue']);
            $program = $_POST['program'];
            $cost = $_POST['cost'];
            $hadc = $_POST['hadc'];
            $platform = $_POST['platform'];
            $function = $_POST['function'];
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $externalname = strtoupper($_POST['externalname']);
            $internalname = $_POST['internalname'];
            $year = date("dmy");

            if ($program == 'INTI') {
                $trainer = $internalname;
                $trtype = 'IN';
            }else if ($program == 'EXT') {
                $trainer = $externalname;
                $trtype = 'PB';
            }else if ($program == 'INTX') {
                $trainer = $externalname;
                $trtype = 'IN';
            }

            $sql = "INSERT INTO `training` (`title`,`program`,`cost`,`platform`,`function`,`venue`,`hadc`,`startdate`,`enddate`,`starttime`,`endtime`,`trainer`) values ('$title', '$program', '$cost', '$platform', '$function', '$venue', '$hadc', '$startdate', '$enddate', '$starttime', '$endtime', '$trainer')";
            if(mysqli_query($conn, $sql)){
                $query = "SELECT max(id) as id FROM training";
                $result = mysqli_query($conn, $query);
                while($row = mysqli_fetch_array($result)) {
                    $trainid = $row['id'];
                }

                $code = sprintf("%05d", $trainid);
                $trid = $trtype.$year.$code;

                $sql = "update training set trainingcode = '$trid' where id = '$trainid'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'insert']);
                }
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'edittraining') {
            $title = strtoupper($_POST['title1']);
            $venue = strtoupper($_POST['venue']);
            $program = $_POST['program'];
            $cost = $_POST['cost'];
            $hadc = $_POST['hadc'];
            $platform = $_POST['platform'];
            $function = $_POST['function'];
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $externalname = strtoupper($_POST['externalname']);
            $internalname = $_POST['internalname'];
            $year = date("dmy");
            $id = $_POST['id'];

            if ($program == 'INTI') {
                $trainer = $internalname;
            }else if ($program == 'EXT') {
                $trainer = $externalname;
            }else if ($program == 'INTX') {
                $trainer = $externalname;
            }
            
            $sql = "UPDATE `training` SET `title` = '$title', `venue` = '$venue', `program` = '$program', `cost` = '$cost', `hadc` = '$hadc', `platform` = '$platform', `function` = '$function', `startdate` = '$startdate', `enddate` = '$enddate', `starttime` = '$starttime', `endtime` = '$endtime', `trainer` = '$trainer' WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'update']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'deletetraining') {
            $id = $_POST['id'];
            $sql = "DELETE FROM `training` WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>