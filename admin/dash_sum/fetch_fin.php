<?php
    include "../../dbconn.php";

    $data = '';
    $data1 = array();
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $currenttime = date("Y-m-d H:i:s");

    if($_POST["action"] == "fetch_finance"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'FINANCE')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'FINANCE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'FINANCE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'ACCOUNT PAYABLE') {
                        $department = 'AP';
                    }else if($row["section"] == 'ACCOUNT RECEIVABLE') {
                        $department = 'AR';
                    }else if($row["section"] == 'INVENTORY FIXED ASSET MANAGEMENT') {
                        $department = 'IFAM';
                    }else if($row["section"] == 'REPORTING TAXATION') {
                        $department = 'RT';
                    }else if($row["section"] == 'TREASURY & SALES') {
                        $department = 'T&S';
                    }else {
                        $department = '-';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["sumtotalhours"],
                        'colorplant' =>	'#' . rand(100000, 999999) . ''
                    );
                }
            }else if ($mode == 'totalhour') {
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'FINANCE')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'FINANCE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'FINANCE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                echo $sql;
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'ACCOUNT PAYABLE') {
                        $department = 'AP';
                    }else if($row["section"] == 'ACCOUNT RECEIVABLE') {
                        $department = 'AR';
                    }else if($row["section"] == 'INVENTORY FIXED ASSET MANAGEMENT') {
                        $department = 'IFAM';
                    }else if($row["section"] == 'REPORTING TAXATION') {
                        $department = 'RT';
                    }else if($row["section"] == 'TREASURY & SALES') {
                        $department = 'T&S';
                    }else {
                        $department = '-';
                    }
    
                    if ($row["avghour"] < 4) {
                        $color = '#FF0000';
                    }else if ($row["avghour"] > 4) {
                        $color = '#00FF00';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["avghour"],
                        'colorplant' =>	$color
                    );
                }
            }
        }
        echo json_encode($data1);
    }else if($_POST["action"] == "fetch_pvd"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'PROCUREMENT & VENDOR DEVELOPMENT')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROCUREMENT & VENDOR DEVELOPMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROCUREMENT & VENDOR DEVELOPMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'GENERAL PURCHASES') {
                        $department = 'GP';
                    }else if($row["section"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                        $department = 'P&VD';
                    }else if($row["section"] == 'RAW MATERIALS & COMPONENTS') {
                        $department = 'RM&C';
                    }else if($row["section"] == 'VENDOR MANAGEMENT & DEVELOPMENT') {
                        $department = 'VM&D';
                    }else {
                        $department = '-';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["sumtotalhours"],
                        'colorplant' =>	'#' . rand(100000, 999999) . ''
                    );
                }
            }else if ($mode == 'totalhour') {
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'PROCUREMENT & VENDOR DEVELOPMENT')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROCUREMENT & VENDOR DEVELOPMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROCUREMENT & VENDOR DEVELOPMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'GENERAL PURCHASES') {
                        $department = 'GP';
                    }else if($row["section"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                        $department = 'P&VD';
                    }else if($row["section"] == 'RAW MATERIALS & COMPONENTS') {
                        $department = 'RM&C';
                    }else if($row["section"] == 'VENDOR MANAGEMENT & DEVELOPMENT') {
                        $department = 'VM&D';
                    }else {
                        $department = '-';
                    }
    
                    if ($row["avghour"] < 4) {
                        $color = '#FF0000';
                    }else if ($row["avghour"] > 4) {
                        $color = '#00FF00';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["avghour"],
                        'colorplant' =>	$color
                    );
                }
            }
        }
        echo json_encode($data1);
    }else if($_POST["action"] == "fetch_it"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'IT & DIGITALISATION')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'IT & DIGITALISATION' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'IT & DIGITALISATION')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'APPLICATION') {
                        $department = 'APP';
                    }else if($row["section"] == 'PROJECT AND DEVELOPMENT') {
                        $department = 'P&D';
                    }else if($row["section"] == 'SYSTEM ADMINISTRATION') {
                        $department = 'SA';
                    }else {
                        $department = '-';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["sumtotalhours"],
                        'colorplant' =>	'#' . rand(100000, 999999) . ''
                    );
                }
            }else if ($mode == 'totalhour') {
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'IT & DIGITALISATION')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'IT & DIGITALISATION' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'IT & DIGITALISATION')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'APPLICATION') {
                        $department = 'APP';
                    }else if($row["section"] == 'PROJECT AND DEVELOPMENT') {
                        $department = 'P&D';
                    }else if($row["section"] == 'SYSTEM ADMINISTRATION') {
                        $department = 'SA';
                    }else {
                        $department = '-';
                    }
    
                    if ($row["avghour"] < 4) {
                        $color = '#FF0000';
                    }else if ($row["avghour"] > 4) {
                        $color = '#00FF00';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["avghour"],
                        'colorplant' =>	$color
                    );
                }
            }
        }
        echo json_encode($data1);
    }
?>