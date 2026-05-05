<?php 
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 0);
    error_reporting(0);

    include "../../../dbconn.php";
	
	date_default_timezone_set("Asia/Kuala_Lumpur");
	$datetime = "(".date("Ymd/his").")";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addtraining') {
            $titlebef = strtoupper($_POST['title1']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = $_POST['trainertype'];
            $internalname = isset($_POST['internalname']) ? $_POST['internalname'] : '';
            $externalname = isset($_POST['externalname']) ? strtoupper($_POST['externalname']) : '';
            $clerkid = isset($_POST['clerkid']) ? $_POST['clerkid'] : '';
            $partycount = isset($_POST['partycount']) ? (int) $_POST['partycount'] : 0;
            $partyconc = isset($_POST['partyconc']) ? (int) $_POST['partyconc'] : 0;
			
			$title = $titlebef." - ". $datetime;

            if ($trainertype == 'INTERNAL') {
                $trainer = $internalname;
            }else if ($trainertype == 'EXTERNAL') {
                $trainer = $externalname;
            }

            $datetime1 = new DateTime($startdate);
            $datetime2 = new DateTime($enddate);
            $interval = $datetime1->diff($datetime2)->format("%a");
            $woweekends = $interval+1;

            $hours =  ((strtotime($endtime) - strtotime($starttime)) / 60) / 60;

            $sql = "insert into ojt (title, startdate, enddate, starttime, endtime, venue, trainername, totalday, totalhour, trainertype) values ('$title', '$startdate', '$enddate', '$starttime', '$endtime', '$venue', '$trainer', '$woweekends', '$hours', '$trainertype')";
			if (!mysqli_query($conn, $sql)) {
                echo json_encode(['message' => 'error']);
                exit;
            }

            $ojtid = mysqli_insert_id($conn);

            $code = sprintf("%05d", $ojtid);
            $year = date("dmy");
            $trid = 'OJ'.$year.$code;

            $partycount1 = $partycount + 1;
            $partyconc1 = $partyconc + 1;

            for ($i=1;$i<$partycount1;$i++) {
                if (!isset($_POST['participant'.$i]) || $_POST['participant'.$i] === '') {
                    continue;
                }
                $staffname = $_POST['participant'.$i];
                $userid = '';
                $department = '';

                $sql = "select id,department from user where staffname = '$staffname' and designation != 'CONTRACT'";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)) {
                    $userid = mysqli_real_escape_string($conn, $row['id']);
                    $department = mysqli_real_escape_string($conn, $row['department']);
                }

                if ($userid !== '') {
                    $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid) values ('$ojtid','$userid','1','$department','$clerkid')";
                    mysqli_query($conn, $sql);
                }
            }

            for ($ij=1;$ij<$partyconc1;$ij++) {
                if (!isset($_POST['contractstaff'.$ij]) || $_POST['contractstaff'.$ij] === '') {
                    continue;
                }
                $contractname = $_POST['contractstaff'.$ij];
                $userid = '';
                $department = '';

                $sql = "select id,department from user where staffname = '$contractname' and designation = 'CONTRACT'";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)) {
                    $userid = mysqli_real_escape_string($conn, $row['id']);
                    $department = mysqli_real_escape_string($conn, $row['department']);
                }

                if ($userid !== '') {
                    $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid) values ('$ojtid','$userid','1','$department','$clerkid')";
                    mysqli_query($conn, $sql);
                }
            }

            $totaluser = 0;
            $sql = "select sum(totalman) as totaluser from participateojt where ojtid = '$ojtid'";
			$query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)) {
                $totaluser = $row['totaluser'] !== null ? mysqli_real_escape_string($conn, $row['totaluser']) : 0;
            }

            $sql = "update ojt set totalman =  '$totaluser', trainingcode = '$trid' where id = '$ojtid'";
			if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert']);
            }else {
                echo json_encode(['message' => 'error']);
            }
        }else if ($_POST['btn_action'] == 'edittraining') {
            $id = strtoupper($_POST['id']);
            $title = strtoupper($_POST['title1']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = $_POST['trainertype'];
            $internalname = $_POST['internalname'];
            $externalname = strtoupper($_POST['externalname']);
            $clerkid = $_POST['clerkid'];
            $partycount = $_POST['partycount'];
            $partyconc = $_POST['partyconc'];

            if ($trainertype == 'INTERNAL') {
                $trainer = $internalname;
            }else if ($trainertype == 'EXTERNAL') {
                $trainer = $externalname;
            }

            $datetime1 = new DateTime($startdate);
            $datetime2 = new DateTime($enddate);
            $interval = $datetime1->diff($datetime2)->format("%a");
            $woweekends = $interval+1;

            $hours =  ((strtotime($endtime) - strtotime($starttime)) / 60) / 60;

            $sql = "update ojt set title = '$title', startdate = '$startdate', enddate = '$enddate', starttime = '$starttime', endtime = '$endtime', venue = '$venue', trainername = '$trainer', totalday = '$woweekends', totalhour = '$hours', trainertype = '$trainertype' where id = '$id'";
            mysqli_query($conn, $sql);

            // if ($partycount != '') {
            //     $partycount1 = $partycount + 1;
            // }else {
            //     $partycount1 = 1;
            // }

            // if ($partyconc != '') {
            //     $partyconc1 = $partyconc + 1;
            // }else {
            //     $partyconc1 = 1;
            // }

            for ($i=0;$i<=$partycount;$i++) {
                if (isset($_POST['participant'.$i])) {
                    $staffname = $_POST['participant'.$i];
                    $record = '';

                    $sql = "select id,department from user where staffname = '$staffname' and designation != 'CONTRACT'";
					$query = mysqli_query($conn,$sql);
                    while($row = mysqli_fetch_assoc($query)) {
                        $userid = mysqli_real_escape_string($conn, $row['id']);
                        $department = mysqli_real_escape_string($conn, $row['department']);
                    }

                    $sql = "select '1' as record from participateojt where ojtid = '$id' and userid = '$userid'";
					$query = mysqli_query($conn,$sql);
                    while($row = mysqli_fetch_assoc($query)) {
                        $record = mysqli_real_escape_string($conn, $row['record']);
                    }

                    if ($record == 1) {
                        $sql = "update participateojt set operation = '1' where ojtid = '$id' and userid = '$userid'";
                        mysqli_query($conn, $sql);
                    }else if ($record == '') {
                        $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid,operation) values ('$id','$userid','1','$department','$clerkid','1')";
						mysqli_query($conn, $sql);
                    }
                }
            }

            for ($ij=0;$ij<=$partyconc;$ij++) {
                if (isset($_POST['contractstaff'.$ij])) {
                    $contractname = $_POST['contractstaff'.$ij];
                    $record = '';

                    $sql = "select id,department from user where staffname = '$contractname' and designation = 'CONTRACT'";
                    $query = mysqli_query($conn,$sql);
                    while($row = mysqli_fetch_assoc($query)) {
                        $userid = mysqli_real_escape_string($conn, $row['id']);
                        $department = mysqli_real_escape_string($conn, $row['department']);
                    }

                    $sql = "select '1' as record from participateojt where ojtid = '$id' and userid = '$userid'";
                    $query = mysqli_query($conn,$sql);
                    while($row = mysqli_fetch_assoc($query)) {
                        $record = mysqli_real_escape_string($conn, $row['record']);
                    }

                    if ($record == 1) {
                        $sql = "update participateojt set operation = '1' where ojtid = '$id' and userid = '$userid'";
                        mysqli_query($conn, $sql);
                    }else if ($record == ''){
                        $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid,operation) values ('$id','$userid','1','$department','$clerkid','1')";
                        mysqli_query($conn, $sql);
                    }
                }
            }

            $sql = "delete from participateojt where ojtid = '$id' and operation != 1";
            mysqli_query($conn, $sql);

            $sql = "update participateojt set operation = '' where ojtid = '$id' and operation = 1";
            mysqli_query($conn, $sql);

            $sql = "select sum(totalman) as totaluser from participateojt where ojtid = '$id'";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)) {
                $totaluser = mysqli_real_escape_string($conn, $row['totaluser']);
            }

            $sql = "update ojt set totalman =  '$totaluser' where id = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'update']);
            }else {
                echo json_encode(['message' => 'error']);
            }
        }else if ($_POST['btn_action'] == 'deletetraining') {
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
        }
    }
?>
