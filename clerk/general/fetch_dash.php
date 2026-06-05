<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

include "../../dbconn.php";

$data = '';
$data1 = array();

function getDepartmentChartLabel($departmentName)
{
    static $dbLabels = null;
    global $conn;

    if ($departmentName === null || trim((string) $departmentName) === '') {
        return $departmentName;
    }

    if ($dbLabels === null) {
        $dbLabels = array();
        $columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM departments LIKE 'shortname'");
        if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
            $result = mysqli_query($conn, "SELECT name, shortname FROM departments");
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $label = trim((string) $row['shortname']);
                    $dbLabels[$row['name']] = $label !== '' ? $label : $row['name'];
                }
            }
        }
    }

    return isset($dbLabels[$departmentName]) ? $dbLabels[$departmentName] : $departmentName;
}

date_default_timezone_set("Asia/Kuala_Lumpur");
$currenttime = date("Y-m-d H:i:s");

if($_POST["action"] == 'fetch_overview'){
    $output= array();
    $clerkid = $_POST["clerkid"];
    if ($_POST["startdate"] != '') {
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];

        $sql = "select ifnull(count(*),0) as totaltraining from ojt where id in (select distinct(ojtid) as id from participateojt where clerkid = '$clerkid') and startdate between '$startdate' and '$enddate';";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totaltraining = $row['totaltraining'];
        }

        $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totalhours*totalman),0) as sumtotalhours, ifnull(sum(totalman),0) as totalmans from (select (datediff(enddate,startdate) + 1) as totaldays,(datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,tablea.totalman from ojt join (select ojtid,sum(totalman) as totalman from participateojt where clerkid = '$clerkid' group by ojtid)tablea on ojt.id = ojtid where startdate between '$startdate' and '$enddate')tableb;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totalday = $row['totalday'];
            $totalhour = $row['sumtotalhours'];
            $totaluser = $row['totalmans'];
        }
    }

    $output[]= array(
        'totaltraining' => $totaltraining,
        'totaluser' => $totaluser,
        'totalday' => $totalday,
        'totalhour' => $totalhour,
    );

    echo json_encode($output);

}else if($_POST["action"] == "fetch_publicojt") {
    if ($_POST["startdate"] != '') {
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $sql = "select 'PUBLIC' as type, ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate) + 1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,id from training where startdate between '$startdate' and '$enddate')tablea union select 'OJT' as type, ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate) + 1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,id from ojt where startdate between '$startdate' and '$enddate')tableb;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if ($row["type"] == "PUBLIC") {
                $data1[] = array(
                    'status'      => $row["type"],
                    'totalstatus' => $row["sumtotalhours"],
                    'colorstatus' => "#FF8800"
                );
            }else if ($row["type"] == "OJT") {
                $data1[] = array(
                    'status'      => $row["type"],
                    'totalstatus' => $row["sumtotalhours"],
                    'colorstatus' => "#2832C2"
                );
            }
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "load_top5"){
    $output= array();
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.*,ifnull(tableb.trainertotalhour,0) as trainertotalhour from (select id,staffno,staffname from user)tablea join (select tablea.id,sum(totalhours) as trainertotalhour from (select id,staffname from user)tablea join (select (datediff(enddate,startdate) + 1) as totaldays,(datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,trainer from training where startdate between '$startdate' and '$enddate' union select (datediff(enddate,startdate) + 1) as totaldays,(datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600,2) as totalhours,trainername as trainer from ojt where startdate between '$startdate' and '$enddate' group by trainername,startdate,enddate)tableb on tablea.staffname = tableb.trainer group by tablea.id)tableb on tablea.id = tableb.id order by trainertotalhour desc limit 5;";
        $query = mysqli_query($conn,$sql);
        if ($query) {
            while($row = mysqli_fetch_assoc($query))
            {
                $output[]= array(
                    'id'               => $row['id'],
                    'staffno'          => $row['staffno'],
                    'staffname'        => $row['staffname'],
                    'trainertotalhour' => $row['trainertotalhour']
                );
            }
        }
    }
    echo json_encode($output);

}else if($_POST["action"] == "fetch_top10"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select a.department,ifnull(b.sumtotalhour,0) as sumtotalhours,ifnull(round(b.sumtotalhour/a.totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user group by department)a left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid,(datediff(enddate,startdate)+1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid,(datediff(enddate,startdate)+1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)b on a.department = b.department where b.sumtotalhour is not null and b.sumtotalhour != 0 order by b.sumtotalhour desc;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_business"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'BUSINESS DEVELOPMENT & STRATEGY' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_dhmsb"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'DHMSB OPERATIONS' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_finance"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'FINANCE' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_human"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'HUMAN CAPITAL & ESG' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_operation"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'OPERATION MANAGEMENT' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_quality"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'QUALITY MANAGEMENT' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);

}else if($_POST["action"] == "fetch_rnd"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'ENGINEERING AND R&D' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tabletraining group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'    => getDepartmentChartLabel($row["department"]),
                'totalsend'   => $row["sumtotalhours"],
                'totalsend1'  => $row["avghours"],
                'colorplant'  => '#' . rand(100000, 999999) . '',
                'colorplant1' => '#FF0000'
            );
        }
    }
    echo json_encode($data1);
}
