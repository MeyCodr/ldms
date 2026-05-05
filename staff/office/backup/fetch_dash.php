<?php
include "../../dbconn.php";

$data = '';
$data1 = array();
date_default_timezone_set("Asia/Kuala_Lumpur");
$currenttime = date("Y-m-d H:i:s");

if($_POST["action"] == 'fetch_overview'){
    $userid = $_POST["userid"];
    $output= array();
    if ($_POST["startdate"] != '') {
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];

        $sql = "select sum(totaltraining) as totaltraining from (select ifnull(count(*),0) as totaltraining from training join participation on training.id = trainingid where userid = '$userid' and startdate between '$startdate' and '$enddate' union select ifnull(count(*),0) as totaltraining from ojt join participateojt on ojt.id = ojtid where userid = '$userid' and startdate between '$startdate' and '$enddate')tableall;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totaltraining = $row['totaltraining'];
        }

        $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate) + 1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,training.id from training join participation on training.id = trainingid where userid = '$userid' and startdate between '$startdate' and '$enddate' union select (datediff(enddate,startdate) + 1) as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.id from ojt join participateojt on ojt.id = ojtid where userid = '$userid' and startdate between '$startdate' and '$enddate')tablea;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totalday = $row['totalday'];
            $totalhour = $row['sumtotalhours'];
        }
    }
    // else if ($_POST["startdate"] == ''){
    //     $sql = "select sum(totaltraining) as totaltraining from (select ifnull(count(*),0) as totaltraining from training join participation on training.id = trainingid where userid = '$userid' union select ifnull(count(*),0) as totaltraining from ojt join participateojt on ojt.id = ojtid where userid = '$userid')tableall;";
    //     $query = mysqli_query($conn,$sql);
    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $totaltraining = $row['totaltraining'];
    //     }

    //     $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,training.id from training join participation on training.id = trainingid where userid = '$userid' union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.id from ojt join participateojt on ojt.id = ojtid where userid = '$userid')tablea;";
    //     $query = mysqli_query($conn,$sql);
    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $totalday = $row['totalday'];
    //         $totalhour = $row['sumtotalhours'];
    //     }
    // }

    $output[]= array(
        'totaltraining' => $totaltraining,
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
        $sql = "select tablea.*,ifnull(tableb.trainertotalhour,0) as trainertotalhour from (select id,staffno,staffname from user)tablea join (select tablea.id,sum(totalhours) as trainertotalhour from (select * from user)tablea join (select id,(datediff(enddate,startdate) + 1) as totaldays,(datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,trainer from training where startdate between '$startdate' and '$enddate' union select id,(datediff(enddate,startdate) + 1) as totaldays,(datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600,2) as totalhours,trainername as trainer from ojt where startdate between '$startdate' and '$enddate' group by trainername,startdate,enddate)tableb on tablea.staffname = tableb.trainer group by tablea.id)tableb on tablea.id = tableb.id order by trainertotalhour desc limit 5;";
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
            if($row["department"] == 'BUSINESS DEVELOPMENT') {
				$department = 'BD';
			}else if($row["department"] == 'INFORMATION TECHNOLOGY') {
				$department = 'IT';
			}else if($row["department"] == 'MANAGEMENT ACCOUNTING') {
				$department = 'MA';
			}else if($row["department"] == 'MANUFACTURING & SCM') {
				$department = 'M&S';
			}else if($row["department"] == 'OPERATION & PROGRAM MANAGEMENT') {
				$department = 'O&PM';
			}else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
				$department = 'PVD';
			}else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
				$department = 'PM1';
			}else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
				$department = 'PM2';
			}else if($row["department"] == 'QUALITY DEVELOPMENT') {
				$department = 'QD';
			}else if($row["department"] == 'HUMAN CAPITAL & ADMIN') {
				$department = 'HC&A';
			}else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
				$department = 'C&TM';
			}else if($row["department"] == 'ASSEMBLY PEKAN') {
				$department = 'AP';
			}else if($row["department"] == 'BUKIT BERUNTUNG') {
				$department = 'BB';
			}else if($row["department"] == 'PEGOH') {
				$department = 'PG';
			}else if($row["department"] == 'SHAH ALAM 1') {
				$department = 'SA1';
			}else if($row["department"] == 'SHAH ALAM 2') {
				$department = 'SA2';
			}else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1 & SA 2') {
				$department = 'SCMSA1SA2';
			}else if($row["department"] == 'TANJUNG MALIM 1 (FIF)') {
				$department = 'TM1';
			}else if($row["department"] == 'TANJUNG MALIM 2 (OSI)') {
				$department = 'TM2';
			}else if($row["department"] == 'COST ENGINEERING') {
				$department = 'CE';
			}else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
				$department = 'PMP';
			}else if($row["department"] == 'ESG') {
				$department = 'ESG';
			}else if($row["department"] == 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)') {
				$department = 'QMBBTM1TM2';
			}else if($row["department"] == 'QUALITY MANAGEMENT (MLK & PKN)') {
				$department = 'QMM&P';
			}else if($row["department"] == 'QUALITY MANAGEMENT (SA 1 & SA 2)') {
				$department = 'QMSA1SA2';
			}else if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
				$department = 'QMS';
			}else if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
				$department = 'EM1';
			}else if($row["department"] == 'ENGINEERING MANAGEMENT 2') {
				$department = 'EM2';
			}else if($row["department"] == 'FACILITY MANAGEMENT') {
				$department = 'FM';
			}else if($row["department"] == 'PLANT ENGINEERING 1 (SA 1 & BB)') {
				$department = 'PE1';
			}else if($row["department"] == 'PLANT ENGINEERING 2 (SA 2, TM1 & TM2)') {
				$department = 'PE2';
			}else if($row["department"] == 'PROCESS & INDUSTRIAL ENGINEERING') {
				$department = 'P&IE';
			}else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
				$department = 'R&D';
			}else if($row["department"] == 'TOOLING ENGINEERING') {
				$department = 'TE';
			}else if($row["department"] == 'TOOLING MAINTENANCE') {
				$department = 'TM';
			}else if($row["department"] == 'SHE') {
				$department = 'SHE';
			}else if($row["department"] == 'HMS') {
				$department = 'HMS';
			}else {
				$department = '-';
			}

            $data1[] = array(
                'category'	  =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'BUSINESS DEVELOPMENT')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'BUSINESS DEVELOPMENT') {
				$department = 'BD';
			}else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
				$department = 'PM1';
			}else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
				$department = 'PM2';
			}else if($row["department"] == 'QUALITY DEVELOPMENT') {
				$department = 'QD';
			}else {
				$department = '-';
			}

            // if ($row["avghours"] < 4.00) {
            //     $coloravg = '#FF0000';
            // }else {
            //     $coloravg = '#008000';
            // }

            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'DHMSB/SUBANG')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'OPERATION & PROGRAM MANAGEMENT') {
                    $department = 'O&PM';
                }else if($row["department"] == 'MANUFACTURING & SCM') {
                    $department = 'M&S';
                }else if($row["department"] == 'OPERATION & PROGRAM MANAGEMENT (DHMSB)') {
                    $department = 'O&PM DHMSB';
                }else if($row["department"] == 'MANUFACTURING & SCM (DHMSB)') {
                    $department = 'M&S DHMSB';
                }else {
					$department = '-';
				}

            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'FINANCE')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'INFORMATION TECHNOLOGY') {
				$department = 'IT';
			}else if($row["department"] == 'MANAGEMENT ACCOUNTING') {
				$department = 'MA';
			}else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
				$department = 'PVD';
			}else {
				$department = '-';
			}
    
            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'HUMAN CAPITAL')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'HUMAN CAPITAL & ADMIN') {
				$department = 'HC&A';
			}else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
				$department = 'C&TM';
			}
    
            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'ASSEMBLY PEKAN') {
				$department = 'AP';
			}else if($row["department"] == 'BUKIT BERUNTUNG') {
				$department = 'BB';
			}else if($row["department"] == 'PEGOH') {
				$department = 'PG';
			}else if($row["department"] == 'SHAH ALAM 1') {
				$department = 'SA1';
			}else if($row["department"] == 'SHAH ALAM 2') {
				$department = 'SA2';
			}else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1 & SA 2') {
				$department = 'SCMSA1SA2';
			}else if($row["department"] == 'TANJUNG MALIM 1 (FIF)') {
				$department = 'TM1';
			}else if($row["department"] == 'TANJUNG MALIM 2 (OSI)') {
				$department = 'TM2';
			}else {
				$department = '-';
			}

            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION TRANSFORMATION')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'COST ENGINEERING') {
				$department = 'CE';
			}else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
				$department = 'PMP';
			}else if($row["department"] == 'ESG') {
				$department = 'ESG';
			}else if($row["department"] == 'SHE') {
				$department = 'SHE';
			}else if($row["department"] == 'HMS') {
				$department = 'HMS';
			}else {
				$department = '-';
			}
    
            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'QUALITY MANAGEMENT')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)') {
				$department = 'QMBBTM1TM2';
			}else if($row["department"] == 'QUALITY MANAGEMENT (MLK & PKN)') {
				$department = 'QMM&P';
			}else if($row["department"] == 'QUALITY MANAGEMENT (SA 1 & SA 2)') {
				$department = 'QMSA1SA2';
			}else if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
				$department = 'QMS';
			}else {
				$department = '-';
			}
    
            $data1[] = array(
                'category'	    =>	$department,
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
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'R&D AND ENGINEERING')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
				$department = 'EM1';
			}else if($row["department"] == 'ENGINEERING MANAGEMENT 2') {
				$department = 'EM2';
			}else if($row["department"] == 'FACILITY MANAGEMENT') {
				$department = 'FM';
			}else if($row["department"] == 'PLANT ENGINEERING 1 (SA 1 & BB)') {
				$department = 'PE1';
			}else if($row["department"] == 'PLANT ENGINEERING 2 (SA 2, TM1 & TM2)') {
				$department = 'PE2';
			}else if($row["department"] == 'PROCESS & INDUSTRIAL ENGINEERING') {
				$department = 'P&IE';
			}else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
				$department = 'R&D';
			}else if($row["department"] == 'TOOLING ENGINEERING') {
				$department = 'TE';
			}else if($row["department"] == 'TOOLING MAINTENANCE') {
				$department = 'TM';
			}else {
				$department = '-';
			}
    
            $data1[] = array(
                'category'	    =>	$department,
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
