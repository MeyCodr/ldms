<?php
    include "../../dbconn.php";

    $data = '';
    $data1 = array();
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $currenttime = date("Y-m-d H:i:s");

    if($_POST["action"] == "fetch_ce"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'COST ENGINEERING')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'COST ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'COST ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'ACCOUNT PAYABLE') {
                        $department = 'AP';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'COST ENGINEERING')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'COST ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'COST ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'ACCOUNT PAYABLE') {
                        $department = 'AP';
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
    }else if($_POST["action"] == "fetch_pmp"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'PROGRAM MANAGEMENT PROTON')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROGRAM MANAGEMENT PROTON' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROGRAM MANAGEMENT PROTON')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'PROGRAM MANAGEMENT PROTON') {
                        $department = 'PMP';
                    }else if($row["section"] == 'QUALITY DEVELOPMENT PROTON') {
                        $department = 'QDP';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'PROGRAM MANAGEMENT PROTON')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROGRAM MANAGEMENT PROTON' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROGRAM MANAGEMENT PROTON')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'PROGRAM MANAGEMENT PROTON') {
                        $department = 'PMP';
                    }else if($row["section"] == 'QUALITY DEVELOPMENT PROTON') {
                        $department = 'QDP';
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
    }else if($_POST["action"] == "fetch_esg"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'ESG')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ESG' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ESG')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'APPLICATION') {
                        $department = 'APP';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'ESG')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ESG' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ESG')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'APPLICATION') {
                        $department = 'APP';
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
    }else if($_POST["action"] == "fetch_hms"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'HMS')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'HMS' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'HMS')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'HMS') {
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
            }else if ($mode == 'totalhour') {
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'HMS')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'HMS' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'HMS')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'HMS') {
                        $department = 'HMS';
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
    }else if($_POST["action"] == "fetch_she"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'SHE')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'SHE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'SHE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'SHE') {
                        $department = 'SHE';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'SHE')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'SHE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'SHE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'SHE') {
                        $department = 'SHE';
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