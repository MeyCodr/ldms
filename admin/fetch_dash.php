<?php
// Return strictly JSON to DataTables and avoid PHP notices in output
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

include "../dbconn.php";
include_once __DIR__ . '/../division_department_section.php';

$data = '';
$data1 = array();
$output = array();
date_default_timezone_set("Asia/Kuala_Lumpur");
$currenttime = date("Y-m-d H:i:s");

function getDepartmentChartLabel($departmentName)
{
    static $dbLabels = null;
    global $conn;

    if (!hasChartDepartment($departmentName)) {
        return null;
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

function hasChartDepartment($departmentName)
{
    return $departmentName !== null && trim((string) $departmentName) !== '';
}

if (!isset($_POST["action"])) {
    echo json_encode([]);
    exit;
}

if($_POST["action"] == 'fetch_overview'){
    $output= array();
    if ($_POST["startdate"] != '') {
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];

        $sql = "select sum(totaltraining) as totaltraining from (select 'public' as traintype,ifnull(count(*),0) as totaltraining from training where startdate between '$startdate' and '$enddate' union select 'ojt' as traintype,ifnull(count(*),0) as totaltraining from ojt where startdate between '$startdate' and '$enddate')tableall;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totaltraining = $row['totaltraining'];
        }

        $sql = "SELECT COUNT(DISTINCT userid) AS totaluser
                FROM (
                    SELECT p.userid
                    FROM training t
                    JOIN participation p ON t.id = p.trainingid
                    JOIN user u ON p.userid = u.id
                    WHERE t.startdate BETWEEN '$startdate' AND '$enddate'
                      AND p.attendance = 'COMPLETED'
                      AND UPPER(COALESCE(u.status, '')) <> 'RESIGN'

                    UNION

                    SELECT po.userid
                    FROM ojt o
                    JOIN participateojt po ON o.id = po.ojtid
                    JOIN user u ON po.userid = u.id
                    WHERE o.startdate BETWEEN '$startdate' AND '$enddate'
                      AND po.attendance = 'COMPLETEDOJT'
                      AND UPPER(COALESCE(u.status, '')) <> 'RESIGN'
                ) unique_participants;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totaluser = $row['totaluser'];
        }

        $sql = "select ifnull(sum(totaldays),0) as totalday,ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,id from training where startdate between '$startdate' and '$enddate' union select (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,concat(id,'/ojt') as id from ojt where startdate between '$startdate' and '$enddate')tablea;";
		$query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $totalday = $row['totalday'];
            $totalhour = $row['sumtotalhours'];
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
        $sql = "select 'PUBLIC' as type, ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,id from training where startdate between '$startdate' and '$enddate')tablea union select 'OJT' as type, ifnull(sum(totaldays*totalhours),0) as sumtotalhours from (select (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,id from ojt where startdate between '$startdate' and '$enddate')tableb;";
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
        $sql = "SELECT u.id,u.staffno,u.staffname,IFNULL(x.trainertotalhour,0) AS trainertotalhour
                FROM user u
                LEFT JOIN (
                    SELECT trainer, SUM(totaldays*totalhours) AS trainertotalhour
                    FROM (
                        SELECT trainer, (DATEDIFF(enddate,startdate))+1 AS totaldays, ROUND(TIME_TO_SEC(TIMEDIFF(endtime,starttime))/3600,2) AS totalhours
                        FROM training
                        WHERE startdate BETWEEN '$startdate' AND '$enddate'
                        UNION ALL
                        SELECT trainername AS trainer, (DATEDIFF(enddate,startdate))+1 AS totaldays, ROUND(TIME_TO_SEC(TIMEDIFF(endtime,starttime))/3600,2) AS totalhours
                        FROM ojt
                        WHERE startdate BETWEEN '$startdate' AND '$enddate'
                    ) t
                    GROUP BY trainer
                ) x ON u.staffname = x.trainer
                WHERE UPPER(COALESCE(u.status, '')) <> 'RESIGN'
                ORDER BY x.trainertotalhour DESC
                LIMIT 5";
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
    // else {
    //     $sql = "select tablea.*,ifnull(tableb.trainertotalhour,0) as trainertotalhour from (select id,staffno,staffname from user)tablea join (select tablea.id,sum(totalhours) as trainertotalhour from (select * from user)tablea join (select id,(datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,trainer from training union select id,(datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600,2) as totalhours,trainername as trainer from ojt group by trainername,startdate,enddate)tableb on tablea.staffname = tableb.trainer group by tablea.id)tableb on tablea.id = tableb.id order by trainertotalhour desc limit 5;";
    //     $query = mysqli_query($conn,$sql);

    //     while($row = mysqli_fetch_assoc($query))
    //     {
    //         $output[]= array(
    //             'id' => $row['id'],
    //             'staffno' => $row['staffno'],
    //             'staffname' => $row['staffname'],
    //             'trainertotalhour' => $row['trainertotalhour']
    //         );
    //     }
    // }
	echo json_encode($output);
}else if($_POST["action"] == "fetch_top10"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where status != 'RESIGN')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.status != 'RESIGN' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.status != 'RESIGN')tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by sumtotalhour desc;";
			$query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
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

                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where status != 'RESIGN')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' and user.status != 'RESIGN' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT') and user.status != 'RESIGN')tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by avghour desc;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid)tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by sumtotalhour desc;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'BUSINESS DEVELOPMENT') {
    //                 $department = 'BD';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
    //                 $department = 'PM1';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
    //                 $department = 'PM2';
    //             }else if($row["department"] == 'QUALITY DEVELOPMENT') {
    //                 $department = 'QD';
    //             }else if($row["department"] == 'OPERATION AND PROGRAM MANAGEMENT') {
    //                 $department = 'O&PM';
    //             }else if($row["department"] == 'MANUFACTURING AND SCM') {
    //                 $department = 'M&S';
    //             }else if($row["department"] == 'FINANCE') {
    //                 $department = 'FIN';
    //             }else if($row["department"] == 'PROCUREMENT & VENDOR DEV') {
    //                 $department = 'PVD';
    //             }else if($row["department"] == 'HUMAN CAPITAL OPERATION AND ADMIN') {
    //                 $department = 'HCO&A';
    //             }else if($row["department"] == 'TALENT AND CULTURE TRANSFORMATION') {
    //                 $department = 'T&CT';
    //             }else if($row["department"] == 'ASSEMBLY PEKAN') {
    //                 $department = 'AP';
    //             }else if($row["department"] == 'DIES MAINTENANCE') {
    //                 $department = 'DM';
    //             }else if($row["department"] == 'OPERATION - BUKIT-BERUNTUNG') {
    //                 $department = 'OBB';
    //             }else if($row["department"] == 'OPERATION - PEGOH') {
    //                 $department = 'OPG';
    //             }else if($row["department"] == 'OPERATION BB TGM 1 AND TGM 2') {
    //                 $department = 'OBBTGM1TGM2';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 1') {
    //                 $department = 'OSA1';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 2') {
    //                 $department = 'OSA2';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1') {
    //                 $department = 'SCMSA1';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 2 AND NON AUTO') {
    //                 $department = 'SCMSA2&NA';
    //             }else if($row["department"] == 'TANJUNG MALIM 1') {
    //                 $department = 'TM1';
    //             }else if($row["department"] == 'TANJUNG MALIM 2') {
    //                 $department = 'TM2';
    //             }else if($row["department"] == 'TOOLING AND PROCESS IMPROVEMENT') {
    //                 $department = 'T&PI';
    //             }else if($row["department"] == 'COST ENGINEERING') {
    //                 $department = 'CE';
    //             }else if($row["department"] == 'INFORMATION TECHNOLOGY') {
    //                 $department = 'IT';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
    //                 $department = 'PMP';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
    //                 $department = 'QMS';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT BB') {
    //                 $department = 'QMBB';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT MELAKA AND PEKAN') {
    //                 $department = 'QMM&P';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 1') {
    //                 $department = 'QMSA1';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 2') {
    //                 $department = 'QMSA2';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT TM1 AND TM2') {
    //                 $department = 'QMTM1&TM2';
    //             }else if($row["department"] == 'ENGINEERING MANAGEMENT') {
    //                 $department = 'EM';
    //             }else if($row["department"] == 'FACILITY MANAGEMENT') {
    //                 $department = 'FM';
    //             }else if($row["department"] == 'PLANT ENGINEERING 1') {
    //                 $department = 'PE1';
    //             }else if($row["department"] == 'PLANT ENGINEERING 2') {
    //                 $department = 'PE2';
    //             }else if($row["department"] == 'PROCESS ENGINEERING') {
    //                 $department = 'PE';
    //             }else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
    //                 $department = 'R&D';
    //             }else if($row["department"] == 'SHEM') {
    //                 $department = 'SHEM';
    //             }else if($row["department"] == 'TOOLING ENGINEERING') {
    //                 $department = 'TE';
    //             }else {
    //                 $department = '-';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid)tablea group by department)tableb on tablea.department = tableb.department where sumtotalhour != '0.00' order by avghour desc;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'BUSINESS DEVELOPMENT') {
    //                 $department = 'BD';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
    //                 $department = 'PM1';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
    //                 $department = 'PM2';
    //             }else if($row["department"] == 'QUALITY DEVELOPMENT') {
    //                 $department = 'QD';
    //             }else if($row["department"] == 'OPERATION AND PROGRAM MANAGEMENT') {
    //                 $department = 'O&PM';
    //             }else if($row["department"] == 'MANUFACTURING AND SCM') {
    //                 $department = 'M&S';
    //             }else if($row["department"] == 'FINANCE') {
    //                 $department = 'FIN';
    //             }else if($row["department"] == 'PROCUREMENT & VENDOR DEV') {
    //                 $department = 'PVD';
    //             }else if($row["department"] == 'HUMAN CAPITAL OPERATION AND ADMIN') {
    //                 $department = 'HCO&A';
    //             }else if($row["department"] == 'TALENT AND CULTURE TRANSFORMATION') {
    //                 $department = 'T&CT';
    //             }else if($row["department"] == 'ASSEMBLY PEKAN') {
    //                 $department = 'AP';
    //             }else if($row["department"] == 'DIES MAINTENANCE') {
    //                 $department = 'DM';
    //             }else if($row["department"] == 'OPERATION - BUKIT-BERUNTUNG') {
    //                 $department = 'OBB';
    //             }else if($row["department"] == 'OPERATION - PEGOH') {
    //                 $department = 'OPG';
    //             }else if($row["department"] == 'OPERATION BB TGM 1 AND TGM 2') {
    //                 $department = 'OBBTGM1TGM2';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 1') {
    //                 $department = 'OSA1';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 2') {
    //                 $department = 'OSA2';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1') {
    //                 $department = 'SCMSA1';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 2 AND NON AUTO') {
    //                 $department = 'SCMSA2&NA';
    //             }else if($row["department"] == 'TANJUNG MALIM 1') {
    //                 $department = 'TM1';
    //             }else if($row["department"] == 'TANJUNG MALIM 2') {
    //                 $department = 'TM2';
    //             }else if($row["department"] == 'TOOLING AND PROCESS IMPROVEMENT') {
    //                 $department = 'T&PI';
    //             }else if($row["department"] == 'COST ENGINEERING') {
    //                 $department = 'CE';
    //             }else if($row["department"] == 'INFORMATION TECHNOLOGY') {
    //                 $department = 'IT';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
    //                 $department = 'PMP';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
    //                 $department = 'QMS';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT BB') {
    //                 $department = 'QMBB';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT MELAKA AND PEKAN') {
    //                 $department = 'QMM&P';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 1') {
    //                 $department = 'QMSA1';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 2') {
    //                 $department = 'QMSA2';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT TM1 AND TM2') {
    //                 $department = 'QMTM1&TM2';
    //             }else if($row["department"] == 'ENGINEERING MANAGEMENT') {
    //                 $department = 'EM';
    //             }else if($row["department"] == 'FACILITY MANAGEMENT') {
    //                 $department = 'FM';
    //             }else if($row["department"] == 'PLANT ENGINEERING 1') {
    //                 $department = 'PE1';
    //             }else if($row["department"] == 'PLANT ENGINEERING 2') {
    //                 $department = 'PE2';
    //             }else if($row["department"] == 'PROCESS ENGINEERING') {
    //                 $department = 'PE';
    //             }else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
    //                 $department = 'R&D';
    //             }else if($row["department"] == 'SHEM') {
    //                 $department = 'SHEM';
    //             }else if($row["department"] == 'TOOLING ENGINEERING') {
    //                 $department = 'TE';
    //             }else {
    //                 $department = '-';
    //             }

    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_business"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'BUSINESS DEVELOPMENT & STRATEGY')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'BUSINESS DEVELOPMENT & STRATEGY') {
                    $department = 'BUSINESS DEVELOPMENT & STRATEGY';
                }else if($row["department"] == 'BUSINESS DEVELOPMENT') {
                    $department = 'BD';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
                    $department = 'PM1';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
                    $department = 'PM2';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 3') {
                    $department = 'PM3';
                }else if($row["department"] == 'COSTING & COMMERCIAL') {
                    $department = 'C&C';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'BUSINESS DEVELOPMENT & STRATEGY')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'BUSINESS DEVELOPMENT & STRATEGY') {
                    $department = 'BUSINESS DEVELOPMENT & STRATEGY';
                }else if($row["department"] == 'BUSINESS DEVELOPMENT') {
                    $department = 'BD';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
                    $department = 'PM1';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
                    $department = 'PM2';
                }else if($row["department"] == 'PROGRAM MANAGEMENT 3') {
                    $department = 'PM3';
                }else if($row["department"] == 'COSTING & COMMERCIAL') {
                    $department = 'C&C';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'BUSINESS DEVELOPMENT')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'BUSINESS DEVELOPMENT') {
    //                 $department = 'BD';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
    //                 $department = 'PM1';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
    //                 $department = 'PM2';
    //             }else if($row["department"] == 'QUALITY DEVELOPMENT') {
    //                 $department = 'QD';
    //             }else {
    //                 $department = '-';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'BUSINESS DEVELOPMENT' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'BUSINESS DEVELOPMENT') {
    //                 $department = 'BD';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 1') {
    //                 $department = 'PM1';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT 2') {
    //                 $department = 'PM2';
    //             }else if($row["department"] == 'QUALITY DEVELOPMENT') {
    //                 $department = 'QD';
    //             }else {
    //                 $department = '-';
    //             }

    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_dhmsb"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'DHMSB OPERATIONS')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'MANUFACTURING & SCM (DHMSB)') {
                    $department = 'MAN. & SCM';
                }else if($row["department"] == 'HICOM INTELLIGENT MOBILITY') {
                    $department = 'HIM';
                }else if($row["department"] == 'OPERATION IV') {
                    $department = 'OP IV';
                }else {
					$department = getDepartmentChartLabel($row["department"]);
				}

                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'DHMSB OPERATIONS')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'MANUFACTURING & SCM (DHMSB)') {
                    $department = 'MAN. & SCM';
                }else if($row["department"] == 'HICOM INTELLIGENT MOBILITY') {
                    $department = 'HIM';
                }else if($row["department"] == 'OPERATION IV') {
                    $department = 'OP IV';
                }else {
					$department = getDepartmentChartLabel($row["department"]);
				}

                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["avghour"],
                    'colorplant' =>	$color
                );
            }
        }
    }
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'DHMSB AND SUBANG')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'OPERATION AND PROGRAM MANAGEMENT') {
    //                 $department = 'O&PM';
    //             }else if($row["department"] == 'MANUFACTURING AND SCM') {
    //                 $department = 'M&S';
    //             }
        
    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'DHMSB AND SUBANG' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'OPERATION AND PROGRAM MANAGEMENT') {
    //                 $department = 'O&PM';
    //             }else if($row["department"] == 'MANUFACTURING AND SCM') {
    //                 $department = 'M&S';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	
	echo json_encode($data1);
}
// else if($_POST["action"] == "fetch_director"){
//     $startdate = $_POST["startdate"];
//     $enddate = $_POST["enddate"];
//     if ($_POST["startdate"] != '') {
//         $sql = "select tablea.department,ifnull(sum(tableb.totaldays*tableb.totalhours),0) as sumtotalhours from (select * from user)tablea left join (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,participation.userid from training join participation on training.id = trainingid where startdate between '$startdate' and '$enddate' union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,userid from ojt where startdate between '$startdate' and '$enddate')tableb on tablea.id = tableb.userid where tablea.department in (select department from user where division = 'EXECUTIVE DIRECTOR') group by tablea.department;";
//         $query = mysqli_query($conn,$sql);
//         while($row = mysqli_fetch_assoc($query)){
//             if($row["department"] == 'CHIEF OPERATING OFFICER') {
//                 $department = 'COO';
//             }
    
//             $data1[] = array(
//                 'category'	  =>	$department,
//                 'totalsend' =>	$row["sumtotalhours"],
//                 'colorplant' =>	'#' . rand(100000, 999999) . ''
//             );
//         }
//     }else {
//         $sql = "select tablea.department,ifnull(sum(tableb.totaldays*tableb.totalhours),0) as sumtotalhours from (select * from user)tablea left join (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,participation.userid from training join participation on training.id = trainingid union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,userid from ojt)tableb on tablea.id = tableb.userid where tablea.department in (select department from user where division = 'EXECUTIVE DIRECTOR') group by tablea.department;";
//         $query = mysqli_query($conn,$sql);
//         while($row = mysqli_fetch_assoc($query)){
//             if($row["department"] == 'CHIEF OPERATING OFFICER') {
//                 $department = 'COO';
//             }
    
//             $data1[] = array(
//                 'category'	  =>	$department,
//                 'totalsend' =>	$row["sumtotalhours"],
//                 'colorplant' =>	'#' . rand(100000, 999999) . ''
//             );
//         }
//     }
	
// 	echo json_encode($data1);
// }
else if($_POST["action"] == "fetch_finance"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'FINANCE')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'IT & DIGITALISATION') {
                    $department = 'IT';
                }else if($row["department"] == 'FINANCE') {
                    $department = 'FIN';
                }else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                    $department = 'PVD';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }
        
                $data1[] = array(
                    'category'	    =>	$department,
                    'totalsend'     =>	$row["sumtotalhours"],
                    'colorplant'    =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'FINANCE')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'IT & DIGITALISATION') {
                    $department = 'IT';
                }else if($row["department"] == 'FINANCE') {
                    $department = 'FIN';
                }else if($row["department"] == 'PROCUREMENT & VENDOR DEVELOPMENT') {
                    $department = 'PVD';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'FINANCE AND PROCUREMENT')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'FINANCE') {
    //                 $department = 'FIN';
    //             }else if($row["department"] == 'PROCUREMENT & VENDOR DEV') {
    //                 $department = 'PVD';
    //             }else {
    //                 $department = '-';
    //             }
        
    //             $data1[] = array(
    //                 'category'	    =>	$department,
    //                 'totalsend'     =>	$row["sumtotalhours"],
    //                 'colorplant'    =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'FINANCE AND PROCUREMENT' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'FINANCE') {
    //                 $department = 'FIN';
    //             }else if($row["department"] == 'PROCUREMENT & VENDOR DEV') {
    //                 $department = 'PVD';
    //             }else {
    //                 $department = '-';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_human"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'HUMAN CAPITAL & ESG')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'REWARDS & ADMIN') {
                    $department = 'R&A';
                }else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
                    $department = 'C&TM';
                }else if($row["department"] == 'ESG, HEALTH AND SAFETY') {
                    $department = 'ESG';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }
        
                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'HUMAN CAPITAL & ESG')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'REWARDS & ADMIN') {
                    $department = 'R&A';
                }else if($row["department"] == 'CULTURE & TALENT MANAGEMENT') {
                    $department = 'C&TM';
                }else if($row["department"] == 'ESG, HEALTH AND SAFETY') {
                    $department = 'ESG';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'HUMAN CAPITAL AND ADMIN')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'HUMAN CAPITAL OPERATION AND ADMIN') {
    //                 $department = 'HCO&A';
    //             }else if($row["department"] == 'TALENT AND CULTURE TRANSFORMATION') {
    //                 $department = 'T&CT';
    //             }
        
    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'HUMAN CAPITAL AND ADMIN' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'HUMAN CAPITAL OPERATION AND ADMIN') {
    //                 $department = 'HCO&A';
    //             }else if($row["department"] == 'TALENT AND CULTURE TRANSFORMATION') {
    //                 $department = 'T&CT';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_operation"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'OPERATION MANAGEMENT' and department is not null and trim(department) <> '')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if ($row["department"] === null || trim($row["department"]) === '') {
                    continue;
                }
                if($row["department"] == 'MANUFACTURING & SCM PEKAN') {
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
                }else if($row["department"] == 'MANUFACTURING & SCM TM1 (FIF) TM1 (FIF)') {
                    $department = 'MFG TM1';
                }else if($row["department"] == 'MANUFACTURING & SCM TM2 (OSI)') {
                    $department = 'MFG TM2';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }

                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION MANAGEMENT' and department is not null and trim(department) <> '')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if ($row["department"] === null || trim($row["department"]) === '') {
                    continue;
                }
                if($row["department"] == 'MANUFACTURING & SCM PEKAN') {
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
                }else if($row["department"] == 'MANUFACTURING & SCM TM1 (FIF) TM1 (FIF)') {
                    $department = 'MFG TM1';
                }else if($row["department"] == 'MANUFACTURING & SCM TM2 (OSI)') {
                    $department = 'MFG TM2';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'OPERATION')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'ASSEMBLY PEKAN') {
    //                 $department = 'AP';
    //             }else if($row["department"] == 'DIES MAINTENANCE') {
    //                 $department = 'DM';
    //             }else if($row["department"] == 'OPERATION - BUKIT-BERUNTUNG') {
    //                 $department = 'OBB';
    //             }else if($row["department"] == 'OPERATION - PEGOH') {
    //                 $department = 'OPG';
    //             }else if($row["department"] == 'OPERATION BB TGM 1 AND TGM 2') {
    //                 $department = 'OBBTGM1TGM2';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 1') {
    //                 $department = 'OSA1';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 2') {
    //                 $department = 'OSA2';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1') {
    //                 $department = 'SCMSA1';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 2 AND NON AUTO') {
    //                 $department = 'SCMSA2&NA';
    //             }else if($row["department"] == 'TANJUNG MALIM 1') {
    //                 $department = 'TM1';
    //             }else if($row["department"] == 'TANJUNG MALIM 2') {
    //                 $department = 'TM2';
    //             }else if($row["department"] == 'TOOLING AND PROCESS IMPROVEMENT') {
    //                 $department = 'T&PI';
    //             }else {
    //                 $department = '-';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'OPERATION' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'ASSEMBLY PEKAN') {
    //                 $department = 'AP';
    //             }else if($row["department"] == 'DIES MAINTENANCE') {
    //                 $department = 'DM';
    //             }else if($row["department"] == 'OPERATION - BUKIT-BERUNTUNG') {
    //                 $department = 'OBB';
    //             }else if($row["department"] == 'OPERATION - PEGOH') {
    //                 $department = 'OPG';
    //             }else if($row["department"] == 'OPERATION BB TGM 1 AND TGM 2') {
    //                 $department = 'OBBTGM1TGM2';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 1') {
    //                 $department = 'OSA1';
    //             }else if($row["department"] == 'OPERATION SHAH ALAM 2') {
    //                 $department = 'OSA2';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 1') {
    //                 $department = 'SCMSA1';
    //             }else if($row["department"] == 'SUPPLY CHAIN MANAGEMENT SA 2 AND NON AUTO') {
    //                 $department = 'SCMSA2&NA';
    //             }else if($row["department"] == 'TANJUNG MALIM 1') {
    //                 $department = 'TM1';
    //             }else if($row["department"] == 'TANJUNG MALIM 2') {
    //                 $department = 'TM2';
    //             }else if($row["department"] == 'TOOLING AND PROCESS IMPROVEMENT') {
    //                 $department = 'T&PI';
    //             }else {
    //                 $department = '-';
    //             }

    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }

echo json_encode($data1);
}else if($_POST["action"] == "fetch_transform"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'OPERATION TRANSFORMATION')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'COST ENGINEERING') {
                    $department = 'CE';
                }else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
                    $department = 'PMP';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }
        
                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'OPERATION TRANSFORMATION')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
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
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'OPERATION TRANSFORMATION')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'COST ENGINEERING') {
    //                 $department = 'CE';
    //             }else if($row["department"] == 'INFORMATION TECHNOLOGY') {
    //                 $department = 'IT';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
    //                 $department = 'PMP';
    //             }else {
    //                 $department = '-';
    //             }
        
    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'OPERATION TRANSFORMATION' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'COST ENGINEERING') {
    //                 $department = 'CE';
    //             }else if($row["department"] == 'INFORMATION TECHNOLOGY') {
    //                 $department = 'IT';
    //             }else if($row["department"] == 'PROGRAM MANAGEMENT PROTON') {
    //                 $department = 'PMP';
    //             }else {
    //                 $department = '-';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_quality"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'QUALITY MANAGEMENT')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
				if($row["department"] == 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)') {
					$department = 'QA&C2';
				}else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)') {
					$department = 'QA&C3';
				}else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)') {
					$department = 'QA&C1';
                }else if($row["department"] == 'QUALITY DEVELOPMENT') {
					$department = 'QD';
                }else if($row["department"] == 'QUALITY SYSTEM & BCM') {
					$department = 'QS';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
                }
        
                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'QUALITY MANAGEMENT')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
				if($row["department"] == 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)') {
					$department = 'QA&C2';
				}else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)') {
					$department = 'QA&C3';
				}else if($row["department"] == 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)') {
					$department = 'QA&C1';
                }else if($row["department"] == 'QUALITY DEVELOPMENT') {
					$department = 'QD';
                }else if($row["department"] == 'QUALITY SYSTEM & BCM') {
					$department = 'QS';
                }else {
                    $department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'QUALITY MANAGEMENT')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
    //                 $department = 'QMS';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT BB') {
    //                 $department = 'QMBB';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT MLK AND PKN') {
    //                 $department = 'QMM&P';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 1') {
    //                 $department = 'QMSA1';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 2') {
    //                 $department = 'QMSA2';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT TM 1 AND TM 2') {
    //                 $department = 'QMTM1&TM2';
    //             }
        
    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'QUALITY MANAGEMENT' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'QUALITY MANAGEMENT SYSTEM') {
    //                 $department = 'QMS';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT BB') {
    //                 $department = 'QMBB';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT MLK AND PKN') {
    //                 $department = 'QMM&P';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 1') {
    //                 $department = 'QMSA1';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT SA 2') {
    //                 $department = 'QMSA2';
    //             }else if($row["department"] == 'QUALITY MANAGEMENT TM 1 AND TM 2') {
    //                 $department = 'QMTM1&TM2';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_rnd"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $mode = $_POST["mode"];
    if ($_POST["startdate"] != '') {
        if ($mode == 'manhour') {
            $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'ENGINEERING AND R&D')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
				if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
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
                }else {
					$department = getDepartmentChartLabel($row["department"]);
                }
        
                $data1[] = array(
                    'category'	  =>	$department,
                    'totalsend' =>	$row["sumtotalhours"],
                    'colorplant' =>	'#' . rand(100000, 999999) . ''
                );
            }
        }else if ($mode == 'totalhour') {
            $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from (select department,case dateresign when '0000-00-00' then month('$enddate') else month(dateresign) end as dateresign from user where division = 'ENGINEERING AND R&D')tableresign where dateresign >= month('$enddate') group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(enddate,startdate)) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,user.department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance in ('COMPLETEDOJT'))tablea group by department)tableb on tablea.department = tableb.department order by department;";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query)){
                if (!hasChartDepartment($row["department"])) {
                    continue;
                }
                if($row["department"] == 'ENGINEERING MANAGEMENT 1') {
                    $department = 'EM1';
                }else if($row["department"] == 'ENGINEERING MANAGEMENT 2') {
                    $department = 'EM2';
                }else if($row["department"] == 'ENERGY & FACILITY MANAGEMENT') {
                    $department = 'EFM';
                }else if($row["department"] == 'EQUIPMENT MAINTENANCE 1 (SA1,BB/RASA & PGH)') {
                    $department = 'EMTC1';
                }else if($row["department"] == 'EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)') {
                    $department = 'PE2';
                }else if($row["department"] == 'PROCESS ENGINEERING') {
                    $department = 'PE';
                }else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
                    $department = 'R&D';
                }else if($row["department"] == 'TOOLING DESIGN & DEVELOPMENT') {
                    $department = 'TE';
                }else {
					$department = getDepartmentChartLabel($row["department"]);
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
    // else {
    //     if ($mode == 'manhour') {
    //         $sql = "select tablea.department,ifnull(sumtotalhour,0) as sumtotalhours from (select distinct(department) from user where division = 'R&D,ENGINEERING & SAFETY')tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'ENGINEERING MANAGEMENT') {
    //                 $department = 'EM';
    //             }else if($row["department"] == 'FACILITY MANAGEMENT') {
    //                 $department = 'FM';
    //             }else if($row["department"] == 'PLANT ENGINEERING 1') {
    //                 $department = 'PE1';
    //             }else if($row["department"] == 'PLANT ENGINEERING 2') {
    //                 $department = 'PE2';
    //             }else if($row["department"] == 'PROCESS ENGINEERING') {
    //                 $department = 'PE';
    //             }else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
    //                 $department = 'R&D';
    //             }else if($row["department"] == 'SHEM') {
    //                 $department = 'SHEM';
    //             }else if($row["department"] == 'TOOLING ENGINEERING') {
    //                 $department = 'TE';
    //             }
        
    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["sumtotalhours"],
    //                 'colorplant' =>	'#' . rand(100000, 999999) . ''
    //             );
    //         }
    //     }else if ($mode == 'totalhour') {
    //         $sql = "select tablea.department,ifnull(ROUND(sumtotalhour/tablea.totaluser,2),0) as avghour from (select department,count(*) as totaluser from user where division = 'R&D,ENGINEERING & SAFETY' group by department)tablea left join (select department,sum(totaldays*totalhours*totalman) as sumtotalhour from (select training.id as trainingid,participation.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,1 as totalman from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED' union select ojt.id as trainingid,participateojt.id as partid, (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,department,participateojt.totalman from ojt join participateojt on ojt.id = ojtid where attendance = 'COMPLETED')tablea group by department)tableb on tablea.department = tableb.department order by department;";
    //         $query = mysqli_query($conn,$sql);
    //         while($row = mysqli_fetch_assoc($query)){
    //             if($row["department"] == 'ENGINEERING MANAGEMENT') {
    //                 $department = 'EM';
    //             }else if($row["department"] == 'FACILITY MANAGEMENT') {
    //                 $department = 'FM';
    //             }else if($row["department"] == 'PLANT ENGINEERING 1') {
    //                 $department = 'PE1';
    //             }else if($row["department"] == 'PLANT ENGINEERING 2') {
    //                 $department = 'PE2';
    //             }else if($row["department"] == 'PROCESS ENGINEERING') {
    //                 $department = 'PE';
    //             }else if($row["department"] == 'RESEARCH AND DEVELOPMENT') {
    //                 $department = 'R&D';
    //             }else if($row["department"] == 'SHEM') {
    //                 $department = 'SHEM';
    //             }else if($row["department"] == 'TOOLING ENGINEERING') {
    //                 $department = 'TE';
    //             }
        
    //             if ($row["avghour"] < 4) {
    //                 $color = '#FF0000';
    //             }else if ($row["avghour"] > 4) {
    //                 $color = '#00FF00';
    //             }

    //             $data1[] = array(
    //                 'category'	  =>	$department,
    //                 'totalsend' =>	$row["avghour"],
    //                 'colorplant' =>	$color
    //             );
    //         }
    //     }
    // }
	
	echo json_encode($data1);
}else if($_POST["action"] == "fetch_cost"){
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    $sql = "select month(startdate) as month,sum(cost) as totalcost from training where startdate between '$startdate' and '$enddate' group by month(startdate);";
    $query = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_assoc($query)){
        if($row["month"] == '1') {
            $month = 'JAN';
        }else if($row["month"] == '2') {
            $month = 'FEB';
        }else if($row["month"] == '3') {
            $month = 'MAC';
        }else if($row["month"] == '4') {
            $month = 'APR';
        }else if($row["month"] == '5') {
            $month = 'MAY';
        }else if($row["month"] == '6') {
            $month = 'JUNE';
        }else if($row["month"] == '7') {
            $month = 'JULY';
        }else if($row["month"] == '8') {
            $month = 'AUG';
        }else if($row["month"] == '9') {
            $month = 'SEP';
        }else if($row["month"] == '10') {
            $month = 'OCT';
        }else if($row["month"] == '11') {
            $month = 'NOV';
        }else if($row["month"] == '12') {
            $month = 'DEC';
        }

        $data1[] = array(
            'category'	    =>  $month,
            'totalsend'     =>	$row["totalcost"],
            'colorplant'    =>	'#' . rand(100000, 999999) . ''
        );
    }
	
	echo json_encode($data1);
}

?>
