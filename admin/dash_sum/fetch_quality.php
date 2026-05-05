<?php
    include "../../dbconn.php";

    $data = '';
    $data1 = array();
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $currenttime = date("Y-m-d H:i:s");

    if($_POST["action"] == "fetch_qms"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'QUALITY MANAGEMENT SYSTEM')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT SYSTEM' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT SYSTEM')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'gg') {
                        $department = '-';
                    } else {
                        $department = '-';
                    }
    
                    $data1[] = array(
                        'category'	  =>	$department,
                        'totalsend' =>	$row["sumtotalhours"],
                        'colorplant' =>	'#' . rand(100000, 999999) . ''
                    );
                }
            }else if ($mode == 'totalhour') {
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'QUALITY MANAGEMENT SYSTEM')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT SYSTEM' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT SYSTEM')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'gg') {
                        $department = '-';
                    } else {
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
    }else if($_POST["action"] == "fetch_qmbb"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'QUALITY MANAGEMENT- TGM 1 (FIF)') {
                        $department = 'TGM1';
                    }else if($row["section"] == 'QUALITY MANAGEMENT- TGM 2') {
                        $department = 'TGM2';
                    }else if($row["section"] == 'QUALITY PCC') {
                        $department = 'PCC';
                    }else if($row["section"] == 'QUALITY PERODUA (MFG-BB)') {
                        $department = 'MFGBB';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'QUALITY MANAGEMENT- TGM 1 (FIF)') {
                        $department = 'TGM1';
                    }else if($row["section"] == 'QUALITY MANAGEMENT- TGM 2') {
                        $department = 'TGM2';
                    }else if($row["section"] == 'QUALITY PCC') {
                        $department = 'PCC';
                    }else if($row["section"] == 'QUALITY PERODUA (MFG-BB)') {
                        $department = 'MFGBB';
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
    }else if($_POST["action"] == "fetch_qmmlk"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'QUALITY MANAGEMENT (MLK & PKN)')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (MLK & PKN)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (MLK & PKN)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'QUALITY MANAGEMENT (PEGOH)') {
                        $department = 'PGH';
                    }else if($row["section"] == 'QUALITY MANAGEMENT (PEKAN)') {
                        $department = 'PKN';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'QUALITY MANAGEMENT (MLK & PKN)')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (MLK & PKN)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (MLK & PKN)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'QUALITY MANAGEMENT (PEGOH)') {
                        $department = 'PGH';
                    }else if($row["section"] == 'QUALITY MANAGEMENT (PEKAN)') {
                        $department = 'PKN';
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
    }else if($_POST["action"] == "fetch_qmsa"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $mode = $_POST["mode"];
        if ($_POST["startdate"] != '') {
            if ($mode == 'manhour') {
                $sql = "select tablea.section,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(section) from user where department = 'QUALITY MANAGEMENT (SA 1 & SA 2)')tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (SA 1 & SA 2)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (SA 1 & SA 2)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'CUSTOMER SERVICE - SA 1') {
                        $department = 'CSSA1';
                    }else if($row["section"] == 'CUSTOMER SERVICES') {
                        $department = 'CS';
                    }else if($row["section"] == 'QUALITY ASSURANCE - SA 1') {
                        $department = 'QASA1';
                    }else if($row["section"] == 'QUALITY ASSURANCE - SA 2') {
                        $department = 'QASA2';
                    }else if($row["section"] == 'QUALITY CONTROL - SA 1') {
                        $department = 'QCSA1';
                    }else if($row["section"] == 'QUALITY CONTROL - SA 2') {
                        $department = 'QCSA2';
                    }else if($row["section"] == 'QUALITY SUPPLIER - SA 1') {
                        $department = 'QSSA1';
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
                $sql = "select tablea.section,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select section,count(*) as totaluser from (select section,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where department = 'QUALITY MANAGEMENT (SA 1 & SA 2)')tableresign where dateresign >= month('$enddate') group by section)tablea left join (select section,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,section,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.department = 'QUALITY MANAGEMENT (SA 1 & SA 2)' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.section,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.department = 'QUALITY MANAGEMENT (SA 1 & SA 2)')tablea group by section)tableb on tablea.section = tableb.section order by section;";
                $query = mysqli_query($conn,$sql);
                while($row = mysqli_fetch_assoc($query)){
                    if($row["section"] == 'CUSTOMER SERVICE - SA 1') {
                        $department = 'CSSA1';
                    }else if($row["section"] == 'CUSTOMER SERVICES') {
                        $department = 'CS';
                    }else if($row["section"] == 'QUALITY ASSURANCE - SA 1') {
                        $department = 'QASA1';
                    }else if($row["section"] == 'QUALITY ASSURANCE - SA 2') {
                        $department = 'QASA2';
                    }else if($row["section"] == 'QUALITY CONTROL - SA 1') {
                        $department = 'QCSA1';
                    }else if($row["section"] == 'QUALITY CONTROL - SA 2') {
                        $department = 'QCSA2';
                    }else if($row["section"] == 'QUALITY SUPPLIER - SA 1') {
                        $department = 'QSSA1';
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