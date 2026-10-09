<?php
    session_start();
    include "../../../dbconn.php";

    // Same gate as tni.php; the department always comes from the logged-in
    // HOD, not from the posted userid.
    if (!isset($_SESSION['fullname'], $_SESSION['id'], $_SESSION['usertype']) || $_SESSION['usertype'] != 'HOD') {
        echo json_encode(array());
        exit();
    }

    $output = array();
    // TNI is stored under the planning year (rolls over on 1 October).
    include_once "../../../planning_year.php";
    $tniYear = ldmsPlanningYear();

    if($_POST["action"] == "gettni"){
        $userid = (int) $_SESSION['id'];

        $sql = "select department from user where id = '$userid'";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $department = $row['department'];
        }

        $department = mysqli_real_escape_string($conn, $department);
        $sql1 = "select count(*) as tnirecord from tni where department = '$department' and year = '$tniYear';";
        $query1 = mysqli_query($conn,$sql1);
        while($row1 = mysqli_fetch_assoc($query1))
        {
            $tni = $row1['tnirecord'];
        }

        $sql1 = "select tablea.department,ifnull(sumtotalhour,0) as sumhour from (select department from user where id = '$userid')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where year(startdate) = year(curdate()) and attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query1 = mysqli_query($conn,$sql1);
        while($row1 = mysqli_fetch_assoc($query1))
        {
            $publichour = $row1['sumhour'];
        }

        $sql2 = "select tablea.department,ifnull(sumtotalhour,0) as sumhour from (select department from user where id = '$userid')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where year(startdate) = year(curdate()) and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query2 = mysqli_query($conn,$sql2);
        while($row2 = mysqli_fetch_assoc($query2))
        {
            $ojthour = $row2['sumhour'];
        }

        $output[]= array(
            'tni' => $tni,
            'publichour' => $publichour,
            'ojthour' => $ojthour,
            'totalhour' => $publichour+$ojthour,
        );

        echo json_encode($output);
    }else if($_POST["action"] == "getlisttni"){
        $userid = (int) $_SESSION['id'];

        $sql = "select department from user where id = '$userid'";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $department = $row['department'];
        }
        
        $department = mysqli_real_escape_string($conn, $department);
        $sql = "select * from tni where department = '$department' and year = '$tniYear' order by id;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $output[]= array(
                'training' => $row['training'],
                'expected' => $row['expected'],
                'actual' => $row['actual'],
                'gap' => $row['gap'],
                'method' => $row['method'],
                'cause' => $row['cause'],
                'ask' => $row['ask'],
                'evaluation' => $row['evaluation'],
            );
        }

        echo json_encode($output);
    }
?>