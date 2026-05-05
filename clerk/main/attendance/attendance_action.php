<?php 
    include "../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'editattendance') {
            $trainer = '';
            $id = $_POST['id'];
            $ans1 = $_POST['ans1'];
            $ans2 = $_POST['ans2'];
            $ans3 = $_POST['ans3'];
            $ans4 = $_POST['ans4'];
            $ans5 = $_POST['ans5'];
            $ans6 = $_POST['ans6'];
            $ans7 = $_POST['ans7'];
            $ans8 = $_POST['ans8'];
            $ans9 = $_POST['ans9'];
            $ans10 = $_POST['ans10'];
            $ans11 = $_POST['ans11'];
            $ans12 = $_POST['ans12'];
            $ans13 = strtoupper($_POST['ans13']);
            $ans14 = strtoupper($_POST['ans14']);
            $ans15 = strtoupper($_POST['ans15']);
            $ans16 = strtoupper($_POST['ans16']);

            $sql = "update participation set q1 = '$ans1', q2 = '$ans2', q3 = '$ans3', q4 = '$ans4', q5 = '$ans5', q6 = '$ans6', q7 = '$ans7', q8 = '$ans8', q9 = '$ans9', q10 = '$ans10', q11 = '$ans11', q12 = '$ans12', q13 = '$ans13', q14 = '$ans14', q15 = '$ans15', q16 = '$ans16', attendance = 'COMPLETED' where id = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert_public']);
            }else {
                echo json_encode(['message' => 'error']);
            }
        }else if ($_POST['btn_action'] == 'addojt') {
            $trainer = '';
            $userid = $_POST['userid'];
            $title = strtoupper($_POST['titleojt']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = $_POST['trainertype'];
            $externalname = strtoupper($_POST['externalname']);
            $internalname = $_POST['internalstaff'];
            $ans1 = strtoupper($_POST['que1']);
            $ans2 = $_POST['que2'];
            $ans3 = $_POST['que3'];

            if ($trainertype == 'INTERNAL') {
                $trainer = $internalname;
            }else if ($trainertype == 'EXTERNAL') {
                $trainer = $externalname;
            }

            $datetime1 = new DateTime($startdate);
            $datetime2 = new DateTime($enddate);
            $interval = $datetime1->diff($datetime2)->format("%a");
            $woweekends = $interval+1;

            $sql = "select department from user where id = '$userid'";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)) {
                $department = mysqli_real_escape_string($conn, $row['department']);
            }

            $hours =  ((strtotime($endtime) - strtotime($starttime)) / 60) / 60;

            if (strtotime($starttime) >= strtotime($endtime)) {
                echo json_encode(['message' => 'alerttime']);
            }else {
                $sql = "insert into ojt (title, startdate, enddate, starttime, endtime, venue, trainername, totalday, totalhour, totalman, trainertype) values ('$title', '$startdate', '$enddate', '$starttime', '$endtime', '$venue', '$trainer', '$woweekends', '$hours', '1', '$trainertype')";
                if(mysqli_query($conn, $sql)){
                    $sql = "select max(id) as id from ojt";
                    $query = mysqli_query($conn,$sql);
                    while($row = mysqli_fetch_assoc($query)) {
                        $ojtid = mysqli_real_escape_string($conn, $row['id']);
                    }

                    $code = sprintf("%05d", $ojtid);
                    $year = date("dmy");
                    $trid = 'OJ'.$year.$code;

                    $sql = "update ojt set trainingcode = '$trid' where id = '$ojtid'";
                    mysqli_query($conn, $sql);

                    $sql = "insert into participateojt (ojtid, userid, attendance, q1, q2, q3, totalman,department) values ('$ojtid', '$userid', 'COMPLETEDOJT', '$ans1', '$ans2', '$ans3', '1','$department')";
                    if(mysqli_query($conn, $sql)){
                        echo json_encode(['message' => 'insert']);
                    }else {
                        echo json_encode(['message' => 'error']);
                    }
                }else {
                    echo json_encode(['message' => 'error']);
                }
            }
        }else if ($_POST['btn_action'] == 'editojt') {
            $trainer = '';
            $userid = $_POST['userid'];
            $title = strtoupper($_POST['titleojt']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $venue = strtoupper($_POST['venue']);
            $trainertype = $_POST['trainertype'];
            $externalname = strtoupper($_POST['externalname']);
            $internalname = $_POST['internalstaff'];
            $ans1 = strtoupper($_POST['que1']);
            $ans2 = $_POST['que2'];
            $ans3 = $_POST['que3'];
            $ojtid = $_POST['ojtid'];

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

            $sql = "update ojt set title = '$title', startdate = '$startdate', enddate = '$enddate', starttime = '$starttime', endtime = '$endtime', venue = '$venue', trainertype = '$trainertype', trainername = '$trainer', totalday = '$woweekends', totalhour = '$hours', totalman = '1' where id = '$ojtid'";
            if(mysqli_query($conn, $sql)){

                $sql = "update participateojt set q1 = '$ans1', q2 = '$ans2', q3 = '$ans3' where ojtid = '$ojtid' and userid = '$userid'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                }
            }else {
                echo json_encode(['message' => 'error']);
            }
        }else if ($_POST['btn_action'] == 'addojtattendance') {
            $trainer = '';
            $id = $_POST['ojtid'];
            $que1 = $_POST['que1'];
            $que2 = $_POST['que2'];
            $que3 = $_POST['que3'];
            $userid = $_POST['userid'];

            $sql = "update participateojt set q1 = '$que1', q2 = '$que2', q3 = '$que3', attendance = 'COMPLETEDOJT' where ojtid = '$id' and userid = '$userid'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert_ojttrain']);
            }else {
                echo json_encode(['message' => 'error']);
            }
        }else if ($_POST['btn_action'] == 'deleteojt') {
            $id = $_POST['id'];

            $sql = "delete from participateojt where ojtid = '$id'";
            if(mysqli_query($conn, $sql)){
                $sql = "delete from ojt where id = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'deleteojt']);
                }else {
                    echo json_encode(['message' => 'error']);
                }
            }else {
                echo json_encode(['message' => 'error']);
            }
        }
    }
?>