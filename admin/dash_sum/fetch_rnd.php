<?php
    include "../../dbconn.php";

    $data = '';
    $data1 = array();
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $currenttime = date("Y-m-d H:i:s");

    if($_POST["action"] == "fetch_em1"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'ENGINEERING MANAGEMENT 1')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ENGINEERING MANAGEMENT 1' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ENGINEERING MANAGEMENT 1')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'TREASURY & SALES') {
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'ENGINEERING MANAGEMENT 1')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ENGINEERING MANAGEMENT 1' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ENGINEERING MANAGEMENT 1')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'TREASURY & SALES') {
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
    }else if($_POST["action"] == "fetch_em2"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'ENGINEERING MANAGEMENT 2')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ENGINEERING MANAGEMENT 2' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ENGINEERING MANAGEMENT 2')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'TREASURY & SALES') {
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'ENGINEERING MANAGEMENT 2')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'ENGINEERING MANAGEMENT 2' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'ENGINEERING MANAGEMENT 2')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'TREASURY & SALES') {
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
    }else if($_POST["action"] == "fetch_fm"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'FACILITY MANAGEMENT')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'FACILITY MANAGEMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'FACILITY MANAGEMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'FACILITY & PLANT MAINTENANCE') {
                        $department = 'F&PM';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'FACILITY MANAGEMENT')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'FACILITY MANAGEMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'FACILITY MANAGEMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'FACILITY & PLANT MAINTENANCE') {
                        $department = 'F&PM';
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
    }else if($_POST["action"] == "fetch_pe1"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- FIF') {
                        $department = 'EMAF';
                    }else if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- PHN 1') {
                        $department = 'EMAP1';
                    }else if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- TGM') {
                        $department = 'EMAT';
                    }else if($row["section"] == 'EQUIPMENT MNTC STAMPING- SA1') {
                        $department = 'EMSS1';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PLANT ENGINEERING 1 (SA 1, BB & TM)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- FIF') {
                        $department = 'EMAF';
                    }else if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- PHN 1') {
                        $department = 'EMAP1';
                    }else if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- TGM') {
                        $department = 'EMAT';
                    }else if($row["section"] == 'EQUIPMENT MNTC STAMPING- SA1') {
                        $department = 'EMSS1';
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
    }else if($_POST["action"] == "fetch_pe2"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- PHN 2') {
                        $department = 'EMAP2';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PLANT ENGINEERING 2 (SA 2, MLK & SBG)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'EQUIPMENT MNTC ASSEMBLY- PHN 2') {
                        $department = 'EMAP2';
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
    }else if($_POST["action"] == "fetch_pnie"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'PROCESS & INDUSTRIAL ENGINEERING')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROCESS & INDUSTRIAL ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROCESS & INDUSTRIAL ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'INDUSTRIAL ENGINEERING') {
                        $department = 'IE';
                    }else if($row["section"] == 'PROCESS DEVELOPMENT') {
                        $department = 'PD';
                    }else if($row["section"] == 'PROCESS IMPROVEMENT') {
                        $department = 'PI';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'PROCESS & INDUSTRIAL ENGINEERING')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'PROCESS & INDUSTRIAL ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'PROCESS & INDUSTRIAL ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'INDUSTRIAL ENGINEERING') {
                        $department = 'IE';
                    }else if($row["section"] == 'PROCESS DEVELOPMENT') {
                        $department = 'PD';
                    }else if($row["section"] == 'PROCESS IMPROVEMENT') {
                        $department = 'PI';
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
    }else if($_POST["action"] == "fetch_rnd"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'RESEARCH AND DEVELOPMENT')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'RESEARCH AND DEVELOPMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'RESEARCH AND DEVELOPMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'COMPUTER AIDED ENGINEERING') {
                        $department = 'CAE';
                    }else if($row["section"] == 'DESIGN') {
                        $department = 'DSG';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'RESEARCH AND DEVELOPMENT')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'RESEARCH AND DEVELOPMENT' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'RESEARCH AND DEVELOPMENT')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'COMPUTER AIDED ENGINEERING') {
                        $department = 'CAE';
                    }else if($row["section"] == 'DESIGN') {
                        $department = 'DSG';
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
    }else if($_POST["action"] == "fetch_te"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'TOOLING ENGINEERING')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'TOOLING ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'TOOLING ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'BIW PROJECT MANAGEMENT') {
                        $department = 'BPM';
                    }else if($row["section"] == 'JIG MAKING') {
                        $department = 'JM';
                    }else if($row["section"] == 'MFG. PROCESS PLANNING') {
                        $department = 'MPP';
                    }else if($row["section"] == 'TE - DIES MAKING') {
                        $department = 'TDM';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'TOOLING ENGINEERING')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'TOOLING ENGINEERING' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'TOOLING ENGINEERING')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'BIW PROJECT MANAGEMENT') {
                        $department = 'BPM';
                    }else if($row["section"] == 'JIG MAKING') {
                        $department = 'JM';
                    }else if($row["section"] == 'MFG. PROCESS PLANNING') {
                        $department = 'MPP';
                    }else if($row["section"] == 'TE - DIES MAKING') {
                        $department = 'TDM';
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
    }else if($_POST["action"] == "fetch_tm"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'TOOLING MAINTENANCE')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'TOOLING MAINTENANCE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'TOOLING MAINTENANCE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'DIES MAINTENANCE') {
                        $department = 'DM';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'TOOLING MAINTENANCE')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'TOOLING MAINTENANCE' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'TOOLING MAINTENANCE')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'DIES MAINTENANCE') {
                        $department = 'DM';
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