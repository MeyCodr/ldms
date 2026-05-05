<?php
include "../../dbconn.php";

$data = '';
$data1 = array();
date_default_timezone_set("Asia/Kuala_Lumpur");
$currenttime = date("Y-m-d H:i:s");

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
                while($row = mysqli_fetch_assoc($result)) {
                    $label = trim((string) $row['shortname']);
                    $dbLabels[$row['name']] = $label !== '' ? $label : $row['name'];
                }
            }
        }
    }

    if (isset($dbLabels[$departmentName])) {
        return $dbLabels[$departmentName];
    }

    return $departmentName;
}

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

        $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totaldays*totalhours*totalman),0) as sumtotalhours, sum(totalman) as totalmans from (select (datediff(startdate, enddate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,tablea.totalman from ojt join (select ojtid,sum(totalman) as totalman from participateojt where clerkid = '$clerkid' group by ojtid)tablea on ojt.id = ojtid where startdate between '$startdate' and '$enddate')tableb;";
		$query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totalday = $row['totalday'];
            $totalhour = $row['sumtotalhours'];
			$totaluser = $row['totalmans'];
        }
    }
    // else if ($_POST["startdate"] == ''){
    //     $sql = "select ifnull(count(*),0) as totaltraining from ojt where id in (select distinct(ojtid) as id from participateojt where clerkid = '$clerkid');";
    //     $query = mysqli_query($conn,$sql);
    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $totaltraining = $row['totaltraining'];
    //     }

    //     $sql = "select ifnull(sum(totalman),0) as totalman from participateojt where clerkid = '$clerkid';";
    //     $query = mysqli_query($conn,$sql);
    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $totaluser = $row['totalman'];
    //     }

    //     $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours from ojt where id in (select distinct(ojtid) as id from participateojt where clerkid = '$clerkid'))tablea;";
    //     $query = mysqli_query($conn,$sql);
    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $totalday = $row['totalday'];
    //         $totalhour = $row['sumtotalhours'];
    //     }
    // }

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
        $data = array();
        while($row = mysqli_fetch_assoc($query)){
            if ($row["type"] == "PUBLIC") {
                $data1[] = array(
                    'status'		=>	$row["type"],
                    'totalstatus' =>	$row["sumtotalhours"],
                    'colorstatus' =>	"#FF8800"
                );
            }else if ($row["type"] == "OJT") {
                $data1[] = array(
                    'status'		=>	$row["type"],
                    'totalstatus' =>	$row["sumtotalhours"],
                    'colorstatus' =>	"#2832C2"
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
        $sql = "select tablea.id,tablea.staffno,tablea.staffname,ifnull(tableb.trainertotalhour,0) as trainertotalhour from (select id,staffno,staffname from user)tablea left join (select trainer,sum(totalhours) as trainertotalhour from (select trainer,round((datediff(enddate,startdate) + 1) * (TIME_TO_SEC(timediff(endtime,starttime))/3600),2) as totalhours from training where startdate between '$startdate' and '$enddate' union all select trainername as trainer,round((datediff(enddate,startdate) + 1) * (TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600),2) as totalhours from ojt where startdate between '$startdate' and '$enddate' group by trainername,startdate,enddate)tablec group by trainer)tableb on tablea.staffname = tableb.trainer order by trainertotalhour desc limit 5;";
        $query = mysqli_query($conn,$sql);

        while($row = mysqli_fetch_assoc($query))
        {
            $output[]= array(
                'id' => $row['id'],
                'staffno' => $row['staffno'],
                'staffname' => $row['staffname'],
                'trainertotalhour' => $row['trainertotalhour']
            );
        }
    }
	echo json_encode($output);
}else if($_POST["action"] == "fetch_top10"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by sumtotalhour desc;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	  =>	getDepartmentChartLabel($row["department"]),
                'totalsend' =>	$row["sumtotalhours"],
                'colorplant' =>	'#' . rand(100000, 999999) . ''
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_business"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'BUSINESS DEVELOPMENT & STRATEGY')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_dhmsb"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'DHMSB OPERATIONS')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_finance"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'FINANCE')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_human"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'HUMAN CAPITAL & ESG')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_operation"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION MANAGEMENT')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_transform"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION TRANSFORMATION')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_quality"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'QUALITY MANAGEMENT')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_rnd"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case when dateresign is null or cast(dateresign as char) = '' or cast(dateresign as char) = '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'ENGINEERING AND R&D')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            $data1[] = array(
                'category'	    =>	getDepartmentChartLabel($row["department"]),
                'totalsend'     =>	$row["sumtotalhours"],
                'totalsend1'    =>	$row["avghours"],
                'colorplant'    =>	'#' . rand(100000, 999999) . '',
                'colorplant1'   =>	'#FF0000'
            );
        }
    }
	
	echo json_encode($data1);
}

?>
