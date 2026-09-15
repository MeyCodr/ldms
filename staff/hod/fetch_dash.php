<?php
include "../../dbconn.php";
include_once __DIR__ . '/../../division_department_section.php';

$data = '';
$data1 = array();
date_default_timezone_set("Asia/Kuala_Lumpur");
$currenttime = date("Y-m-d H:i:s");

function hasChartDepartment($departmentName)
{
    return $departmentName !== null && trim((string) $departmentName) !== '';
}

function getDepartmentChartLabel($departmentName)
{
    static $dbLabels = null;
    global $conn;

    if (!hasChartDepartment($departmentName)) {
        return '-';
    }

    if ($dbLabels === null) {
        $dbLabels = [];
        $hasShortname = false;
        $columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM departments LIKE 'shortname'");
        if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
            $hasShortname = true;
        }

        if ($hasShortname) {
            $result = mysqli_query($conn, "SELECT name, shortname FROM departments");
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $label = trim((string) $row['shortname']);
                    $dbLabels[$row['name']] = $label !== '' ? $label : $row['name'];
                }
            }
        }
    }

    if (isset($dbLabels[$departmentName])) {
        return $dbLabels[$departmentName];
    }

    $fallback = getDepartmentShortName($departmentName);
    return $fallback !== null ? $fallback : $departmentName;
}

if($_POST["action"] == 'fetch_overview'){
    $userid = (int) $_POST["userid"];
    $output = array();

    $totaltraining = 0;
    $totaluser = 0;
    $totalmanpower = 0;
    $totalday = 0;
    $totalhour = 0;
    $totalpublichour = 0;
    $totalojthour = 0;

    // HOD's own "department" field is the department they head - see
    // staff/hod/tna/fetch_staff.php for the same lookup pattern.
    $department = '';
    $deptQuery = mysqli_query($conn, "SELECT department FROM user WHERE id = '$userid' LIMIT 1");
    if ($deptQuery && $deptRow = mysqli_fetch_assoc($deptQuery)) {
        $department = (string) $deptRow['department'];
    }
    $departmentEsc = mysqli_real_escape_string($conn, $department);

    if ($department !== '' && $_POST["startdate"] != '') {
        $startdate = mysqli_real_escape_string($conn, $_POST["startdate"]);
        $enddate = mysqli_real_escape_string($conn, $_POST["enddate"]);

        $sql = "select count(distinct trainingid) as cnt
                from participation_all participation
                join training_all training on training.id = participation.trainingid
                join user on user.id = participation.userid
                where user.department = '$departmentEsc'
                  and training.startdate between '$startdate' and '$enddate'
                  and participation.attendance = 'COMPLETED'";
        $query = mysqli_query($conn,$sql);
        $publicTrainingCount = ($query && $row = mysqli_fetch_assoc($query)) ? (int) $row['cnt'] : 0;

        $sql = "select count(distinct ojtid) as cnt
                from participateojt_all participateojt
                join ojt_all ojt on ojt.id = participateojt.ojtid
                join user on user.id = participateojt.userid
                where user.department = '$departmentEsc'
                  and ojt.startdate between '$startdate' and '$enddate'
                  and participateojt.attendance = 'COMPLETEDOJT'";
        $query = mysqli_query($conn,$sql);
        $ojtTrainingCount = ($query && $row = mysqli_fetch_assoc($query)) ? (int) $row['cnt'] : 0;

        $totaltraining = $publicTrainingCount + $ojtTrainingCount;

        $sql = "select count(distinct userid) as totaluser from (
                    select participation.userid
                    from participation_all participation
                    join training_all training on training.id = participation.trainingid
                    join user on user.id = participation.userid
                    where user.department = '$departmentEsc'
                      and training.startdate between '$startdate' and '$enddate'
                      and participation.attendance = 'COMPLETED'
                    union
                    select participateojt.userid
                    from participateojt_all participateojt
                    join ojt_all ojt on ojt.id = participateojt.ojtid
                    join user on user.id = participateojt.userid
                    where user.department = '$departmentEsc'
                      and ojt.startdate between '$startdate' and '$enddate'
                      and participateojt.attendance = 'COMPLETEDOJT'
                )tableusers";
        $query = mysqli_query($conn,$sql);
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $totaluser = (int) $row['totaluser'];
        }

        $sql = "select ifnull(sum(totaldays*totalman),0) as totalday, ifnull(sum(totaldays*totalhours*totalman),0) as sumtotalhours
                from (
                    select training.id as trainingid, participation.id as partid,
                           (datediff(training.enddate,training.startdate) + 1) as totaldays,
                           round(TIME_TO_SEC(timediff(training.endtime,training.starttime))/3600,2) as totalhours,
                           1 as totalman
                    from training_all training
                    join participation_all participation on training.id = participation.trainingid
                    join user on user.id = participation.userid
                    where user.department = '$departmentEsc'
                      and training.startdate between '$startdate' and '$enddate'
                      and participation.attendance = 'COMPLETED'
                    union all
                    select ojt.id as trainingid, participateojt.id as partid,
                           (datediff(ojt.enddate,ojt.startdate) + 1) as totaldays,
                           round(TIME_TO_SEC(timediff(ojt.endtime,ojt.starttime))/3600,2) as totalhours,
                           participateojt.totalman as totalman
                    from ojt_all ojt
                    join participateojt_all participateojt on ojt.id = participateojt.ojtid
                    join user on user.id = participateojt.userid
                    where user.department = '$departmentEsc'
                      and ojt.startdate between '$startdate' and '$enddate'
                      and participateojt.attendance = 'COMPLETEDOJT'
                )tablea";
        $query = mysqli_query($conn,$sql);
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $totalday = $row['totalday'];
            $totalhour = $row['sumtotalhours'];
        }

        $sql = "select ifnull(sum(totaldays*totalhours),0) as sumtotalhours
                from (
                    select (datediff(training.enddate,training.startdate) + 1) as totaldays,
                           round(TIME_TO_SEC(timediff(training.endtime,training.starttime))/3600,2) as totalhours
                    from training_all training
                    join participation_all participation on training.id = participation.trainingid
                    join user on user.id = participation.userid
                    where user.department = '$departmentEsc'
                      and training.startdate between '$startdate' and '$enddate'
                      and participation.attendance = 'COMPLETED'
                )tablea";
        $query = mysqli_query($conn,$sql);
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $totalpublichour = $row['sumtotalhours'];
        }

        $sql = "select ifnull(sum(totaldays*totalhours*totalman),0) as sumtotalhours
                from (
                    select (datediff(ojt.enddate,ojt.startdate) + 1) as totaldays,
                           round(TIME_TO_SEC(timediff(ojt.endtime,ojt.starttime))/3600,2) as totalhours,
                           participateojt.totalman as totalman
                    from ojt_all ojt
                    join participateojt_all participateojt on ojt.id = participateojt.ojtid
                    join user on user.id = participateojt.userid
                    where user.department = '$departmentEsc'
                      and ojt.startdate between '$startdate' and '$enddate'
                      and participateojt.attendance = 'COMPLETEDOJT'
                )tablea";
        $query = mysqli_query($conn,$sql);
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $totalojthour = $row['sumtotalhours'];
        }
    }

    if ($department !== '') {
        $enddateEsc = isset($_POST["enddate"]) && $_POST["enddate"] !== '' ? mysqli_real_escape_string($conn, $_POST["enddate"]) : null;
        if ($enddateEsc !== null) {
            $sql = "SELECT COUNT(*) AS totalmanpower FROM user WHERE department = '$departmentEsc' AND (dateresign IS NULL OR CAST(dateresign AS CHAR) IN ('', '0000-00-00') OR dateresign >= '$enddateEsc')";
        } else {
            $sql = "SELECT COUNT(*) AS totalmanpower FROM user WHERE department = '$departmentEsc' AND (dateresign IS NULL OR CAST(dateresign AS CHAR) IN ('', '0000-00-00'))";
        }
        $query = mysqli_query($conn,$sql);
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $totalmanpower = (int) $row['totalmanpower'];
        }
    }

    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($mode == 'totalhour') {
        $divisor = ($totalmanpower > 0) ? $totalmanpower : 1;
        $attendeeDivisor = ($totaluser > 0) ? $totaluser : 1;

        $totalpublichour = ($totaluser > 0) ? round($totalpublichour / $attendeeDivisor, 2) : 0;
        $totalojthour = ($totaluser > 0) ? round($totalojthour / $attendeeDivisor, 2) : 0;

        $totaltraining = ($totalmanpower > 0) ? round($totaltraining / $divisor, 2) : 0;
        $totaluser = ($totalmanpower > 0) ? round($totaluser / $divisor, 2) : 0;
        $totalday = ($totalmanpower > 0) ? round($totalday / $divisor, 2) : 0;
        $totalhour = ($totalmanpower > 0) ? round($totalhour / $divisor, 2) : 0;
    }

    $output[] = array(
        'totaltraining' => $totaltraining,
        'totaluser' => $totaluser,
        'totalmanpower' => $totalmanpower,
        'totalday' => $totalday,
        'totalhour' => $totalhour,
        'totalpublichour' => $totalpublichour,
        'totalojthour' => $totalojthour,
    );

    echo json_encode($output);
}else if($_POST["action"] == "fetch_publicojt") {
    if ($_POST["startdate"] != '') {
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        $sql = "select 'PUBLIC' as type, ifnull(sum((datediff(t.enddate,t.startdate)+1) * round(TIME_TO_SEC(timediff(t.endtime,t.starttime))/3600,2)),0) as sumtotalhours from training t join participation p on t.id = p.trainingid join user u on p.userid = u.id where t.startdate between '$startdate' and '$enddate' and p.attendance = 'COMPLETED' union select 'OJT' as type, ifnull(sum((datediff(o.enddate,o.startdate)+1) * round(TIME_TO_SEC(timediff(o.endtime,o.starttime))/3600,2) * po.totalman),0) as sumtotalhours from ojt o join participateojt po on o.id = po.ojtid join user u on po.userid = u.id where o.startdate between '$startdate' and '$enddate' and po.attendance = 'COMPLETEDOJT';";
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
        $sql = "select user.id, user.staffno, user.staffname, round(sum(trainer_hours.totalhours), 2) as trainertotalhour
                from user
                join (
                    select trainer, ((datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2)) as totalhours
                    from training_all training
                    where startdate between '$startdate' and '$enddate'
                    union all
                    select trainername as trainer, ((datediff(enddate,startdate) + 1) * round(TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600,2)) as totalhours
                    from ojt_all ojt
                    where startdate between '$startdate' and '$enddate'
                    group by trainername, startdate, enddate
                ) as trainer_hours on user.staffname = trainer_hours.trainer
                group by user.id, user.staffno, user.staffname
                order by trainertotalhour desc
                limit 5;";
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
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by avghour desc;";
        } else {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by sumtotalhour desc;";
        }
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'BUSINESS DEVELOPMENT') {
                $department = 'BD';
            }else if($row["department"] == 'IT & DIGITALISATION') {
                $department = 'IT';
            }else if($row["department"] == 'FINANCE') {
                $department = 'FIN';
            }else if($row["department"] == 'MANUFACTURING & SCM (DHMSB)') {
                $department = 'M&S(DHMSB)';
            }else if($row["department"] == 'OPERATION IV') {
                $department = 'OPIV';
            }else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                $department = 'PVD';
            }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
                $department = 'PM1';
            }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
                $department = 'PM2';
            }else if($row["department"] == 'PROGRAM MANAGEMENT 3') {
                $department = 'PM3';    
            }else if($row["department"] == 'QUALITY DEVELOPMENT') {
                $department = 'QD';
            }else if($row["department"] == 'REWARDS & ADMIN') {
                $department = 'R&A';
            }else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
                $department = 'C&TM';
            }else if($row["department"] == 'MANUFACTURING & SCM PEKAN') {
                $department = 'MFG PKN';
            }else if($row["department"] == 'MANUFACTURING & SCM BB/RASA') {
                $department = 'MFG BB/RASA';
            }else if($row["department"] == 'MANUFACTURING & SCM PEGOH') {
                $department = 'MFG PGH';
            }else if($row["department"] == 'MANUFACTURING & SCM SA1') {
                $department = 'MFG SA1';
            }else if($row["department"] == 'MANUFACTURING & SCM SA2') {
                $department = 'MFG SA2';
            }else if($row["department"] == 'INVENTORY MANAGEMENT PLANNING (IMP)') {
                $department = 'IMP';
            }else if($row["department"] == 'MANUFACTURING & SCM TM1 (FIF)') {
                $department = 'MFG TM1';
            }else if($row["department"] == 'MANUFACTURING & SCM TM2 (OSI)') {
                $department = 'MFG TM2';
            }else if($row["department"] == 'COSTING & COMMERCIAL') {
                $department = 'C&C';
            }else if($row["department"] == 'ESG, HEALTH AND SAFETY') {
                $department = 'ESG';
            }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)') {
                $department = 'QA&C2';
            }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)') {
                $department = 'QA&C3';
            }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)') {
                $department = 'QA&C1';
            }else if($row["department"] == 'QUALITY SYSTEM & BCM') {
                $department = 'QS';
            }else if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
                $department = 'ENG 1';
            }else if($row["department"] == 'ENGINEERING MANAGEMENT 2') {
                $department = 'ENG 2';
            }else if($row["department"] == 'ENERGY & FACILITY MANAGEMENT') {
                $department = 'EFM';
            }else if($row["department"] == 'EQUIPMENT MAINTENANCE 1 (SA1, BB/RASA & PGH)') {
                $department = 'EQM1';
            }else if($row["department"] == 'EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)') {
                $department = 'EQM2';
            }else if($row["department"] == 'PROCESS ENGINEERING') {
                $department = 'PE';
            }else if($row["department"] == 'RESEARCH & DEVELOPMENT') {
                $department = 'R&D';
            }else if($row["department"] == 'TOOLING DESIGN & DEVELOPMENT') {
                $department = 'TE';
            }else if($row["department"] == 'COO OFFICE') {
                $department = 'COO';
            }else if($row["department"] == 'HICOM INTELLIGENT MOBILITY') {
                $department = 'HIM';
            }else {
                $department = getDepartmentChartLabel($row["department"]);
            }

            if ($mode == 'totalhour') {
                $value = $row["avghour"];
                $color = ($row["avghour"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	  =>	$department,
                'totalsend' =>	$value,
                'colorplant' =>	$color
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_business"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'BUSINESS DEVELOPMENT & STRATEGY' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
           if($row["department"] == 'BUSINESS DEVELOPMENT') {
                    $department = 'BD';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
                    $department = 'PM 1';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
                    $department = 'PM 2';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 3') {
                    $department = 'PM 3';
                }else if($row["department"] == 'QUALITY DEVELOPMENT') {
                    $department = 'QD';
                }else if($row["department"] == 'COSTING & COMMERCIAL') {
                    $department = 'C&C';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_dhmsb"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'DHMSB OPERATIONS' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if($row["department"] == 'HICOM INTELLIGENT MOBILITY') {
                    $department = 'HIM';
                }else if($row["department"] == 'MANUFACTURING & SCM (DHMSB)') {
                    $department = 'MAN. & SCM';
                }else {
					$department = getDepartmentChartLabel($row["department"]);
				}

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_finance"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'FINANCE, PROCUREMENT & IT' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
           if($row["department"] == 'IT & DIGITALISATION') {
                    $department = 'IT';
                }else if($row["department"] == 'FINANCE') {
                    $department = 'FIN';
                }else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                    $department = 'PVD';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_human"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'HUMAN CAPITAL' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
           if($row["department"] == 'REWARDS & ADMIN') {
                    $department = 'R&A';
                }else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
                    $department = 'C&TM';
                }else if($row["department"] == 'ESG, HEALTH AND SAFETY') {
                    $department = 'ESG';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_operation"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division in ('OPERATION MANAGEMENT', 'Operation Management') and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if (trim((string) $row["department"]) === '') {
                continue;
            }
            if($row["department"] == 'MANUFACTURING & SCM PEKAN') {
                    $department = 'PKN';
                }else if($row["department"] == 'MANUFACTURING & SCM BB/RASA') {
                    $department = 'BB';
                }else if($row["department"] == 'MANUFACTURING & SCM PEGOH') {
                    $department = 'PGH';
                }else if($row["department"] == 'MANUFACTURING & SCM SA1') {
                    $department = 'SA1';
                }else if($row["department"] == 'MANUFACTURING & SCM SA2') {
                    $department = 'SA2';
                }else if($row["department"] == 'INVENTORY MANAGEMENT PLANNING (IMP)') {
                    $department = 'IMP';
                }else if($row["department"] == 'MANUFACTURING & SCM TM1 (FIF)') {
                    $department = 'TM1';
                }else if($row["department"] == 'MANUFACTURING & SCM TM2 (OSI)') {
                    $department = 'TM2';
                }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)') {
                    $department = 'QA&C1';
                }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)') {
                    $department = 'QA&C2';
                }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)') {
                    $department = 'QA&C3';
                }else if($row["department"] == 'QUALITY SYSTEM & BCM') {
                    $department = 'QS';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_transform"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'OPERATION TRANSFORMATION' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
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
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'QUALITY MANAGEMENT' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if (trim((string) $row["department"]) === '') {
                continue;
            }
             if($row["department"] == 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)') {
                    $department = 'QA&C1';
                }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)') {
                    $department = 'QA&C2';
                }else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)') {
                    $department = 'QA&C3';
                }else if($row["department"] == 'QUALITY DEVELOPMENT') {
                    $department = 'QD';
                }else if($row["department"] == 'QUALITY MANAGEMENT (BB, TM 1 & TM 2)') {
                    $department = 'QMBBTM1TM2';
                }else if($row["department"] == 'QUALITY MANAGEMENT (MLK & PKN)') {
                    $department = 'QMM&P';
                }else if($row["department"] == 'QUALITY MANAGEMENT (SA 1 & SA 2)') {
                    $department = 'QMSA1SA2';
                }else if($row["department"] == 'QUALITY SYSTEM & BCM') {
                    $department = 'QMS';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_rnd"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = isset($_POST["mode"]) ? $_POST["mode"] : 'manhour';
    if ($_POST["startdate"] != '') {
        $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours, ifnull(round(sumtotalhour/totalstaff,2),0) as avghours from (select department,count(*) as totalstaff from user where division = 'ENGINEERING AND R&D' and (dateresign is null or cast(dateresign as char) in ('', '0000-00-00') or dateresign >= '$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training_all training join participation_all participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt_all ojt join participateojt_all participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
           if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
                    $department = 'EM1';
                }else if($row["department"] == 'ENGINEERING MANAGEMENT 2') {
                    $department = 'EM2';
                }else if($row["department"] == 'ENERGY & FACILITY MANAGEMENT') {
                    $department = 'EFM';
                }else if($row["department"] == 'EQUIPMENT MAINTENANCE 1 (SA1, BB/RASA & PGH)') {
                    $department = 'EMTC1';
                }else if($row["department"] == 'EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)') {
                    $department = 'PE2';
                }else if($row["department"] == 'PROCESS ENGINEERING') {
                    $department = 'PE';
                }else if($row["department"] == 'RESEARCH & DEVELOPMENT') {
                    $department = 'R&D';
                }else if($row["department"] == 'TOOLING DESIGN & DEVELOPMENT') {
                    $department = 'TDD';
                }else {
					$department = getDepartmentChartLabel($row["department"]);
                }

            if ($mode == 'totalhour') {
                $value = $row["avghours"];
                $color = ($row["avghours"] < 4) ? '#FF0000' : '#00FF00';
            } else {
                $value = $row["sumtotalhours"];
                $color = '#' . rand(100000, 999999) . '';
            }

            $data1[] = array(
                'category'	    =>	$department,
                'totalsend'     =>	$value,
                'colorplant'    =>	$color
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_cost"){
    // Mirrors admin/fetch_dash.php's fetch_cost (same "Monthly Total Cost"
    // chart), scoped down to the logged-in HOD's own department. Training
    // cost lives on `training`, not per participant, so a DISTINCT on the
    // training id in the inner query is required before summing - without
    // it, a training with 3 of the department's staff attending would have
    // its cost counted 3 times instead of once.
    $userid = (int) $_POST["userid"];
    $year = isset($_POST["year"]) ? $_POST["year"] : '';

    $department = '';
    $deptQuery = mysqli_query($conn, "SELECT department FROM user WHERE id = '$userid' LIMIT 1");
    if ($deptQuery && $deptRow = mysqli_fetch_assoc($deptQuery)) {
        $department = (string) $deptRow['department'];
    }
    $departmentEsc = mysqli_real_escape_string($conn, $department);

    $data1 = array();
    if ($department !== '') {
        if ($year !== '' && preg_match('/^\d{4}$/', $year)) {
            $dateCondition = "YEAR(training.startdate) = '$year'";
        } else {
            $startdate = mysqli_real_escape_string($conn, $_POST["startdate"]);
            $enddate = mysqli_real_escape_string($conn, $_POST["enddate"]);
            $dateCondition = "training.startdate BETWEEN '$startdate' AND '$enddate'";
        }

        $sql = "SELECT month, SUM(cost) AS totalcost
                FROM (
                    SELECT DISTINCT training.id, training.cost, MONTH(training.startdate) AS month
                    FROM training_all training
                    JOIN participation_all participation ON training.id = participation.trainingid
                    JOIN user ON user.id = participation.userid
                    WHERE user.department = '$departmentEsc'
                      AND $dateCondition
                ) dept_trainings
                GROUP BY month
                ORDER BY month";
        $query = mysqli_query($conn,$sql);

        // Fixed Jan-Dec labels, pre-seeded to 0, so a month with no training
        // at all still appears on the chart instead of being skipped (see
        // admin/fetch_dash.php's fetch_cost for the same fix).
        $monthNames = [1=>'JAN',2=>'FEB',3=>'MAC',4=>'APR',5=>'MAY',6=>'JUNE',7=>'JULY',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC'];
        $monthlyCost = array_fill(1, 12, 0);
        while($row = mysqli_fetch_assoc($query)){
            $monthlyCost[(int) $row['month']] = $row['totalcost'];
        }

        foreach ($monthNames as $num => $month) {
            $data1[] = array(
                'category'	    =>  $month,
                'totalsend'     =>	$monthlyCost[$num],
                'colorplant'    =>	'#' . rand(100000, 999999) . ''
            );
        }
    }

	echo json_encode($data1);
}else if($_POST["action"] == "fetch_cost_years"){
    $userid = (int) $_POST["userid"];

    $department = '';
    $deptQuery = mysqli_query($conn, "SELECT department FROM user WHERE id = '$userid' LIMIT 1");
    if ($deptQuery && $deptRow = mysqli_fetch_assoc($deptQuery)) {
        $department = (string) $deptRow['department'];
    }
    $departmentEsc = mysqli_real_escape_string($conn, $department);

    $years = array();
    if ($department !== '') {
        $sql = "SELECT DISTINCT YEAR(training.startdate) AS yr
                FROM training_all training
                JOIN participation_all participation ON training.id = participation.trainingid
                JOIN user ON user.id = participation.userid
                WHERE user.department = '$departmentEsc'
                  AND training.startdate IS NOT NULL
                ORDER BY yr DESC";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query)){
            if ($row['yr'] !== null) {
                $years[] = $row['yr'];
            }
        }
    }
    echo json_encode($years);
}

?>
