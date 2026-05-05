<?php
include "../../../dbconn.php";

if ($_POST["action"] == "load_staff") {
    $output = array();
    // $sql = "select tablea.*,tableb.trainertotalhour from (select tablea.*,sum(tableb.totaldays*tableb.totalhours) as sumtotalhours from (select * from user where designation = 'CONTRACT')tablea left join (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,participation.userid from training join participation on training.id = trainingid union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,userid from ojt join participateojt on ojt.id = ojtid)tableb on tablea.id = tableb.userid group by tablea.id)tablea left join (select tablea.id,sum(totalhours) as trainertotalhour from (select * from user)tablea join (select id,(datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,trainer from training union select id,(datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(max(endtime),min(starttime)))/3600,2) as totalhours,trainername as trainer from ojt group by trainername,startdate)tableb on tablea.staffname = tableb.trainer group by tablea.id)tableb on tablea.id = tableb.id order by tablea.staffname";
    $sql = "SELECT 
    u.*,
    COALESCE(participant_data.sumtotalhours, 0) AS sumtotalhours,
    COALESCE(trainer_data.trainertotalhour, 0) AS trainertotalhour
FROM 
    user u
LEFT JOIN (
    SELECT 
        userid,
        SUM(totaldays * totalhours) AS sumtotalhours
    FROM (
        SELECT 
            p.userid,
            ((DATEDIFF(t.enddate, t.startdate)) 
            - ((DATEDIFF(t.enddate, t.startdate) * 2) 
            - CASE WHEN DAYNAME(t.startdate) = 'Saturday' THEN 1 ELSE 0 END 
            - CASE WHEN DAYNAME(t.enddate) = 'Sunday' THEN 1 ELSE 0 END) + 1) AS totaldays,
            ROUND(TIME_TO_SEC(TIMEDIFF(t.endtime, t.starttime)) / 3600, 2) AS totalhours
        FROM training t
        JOIN participation p ON t.id = p.trainingid

        UNION ALL

        SELECT 
            po.userid,
            ((DATEDIFF(o.enddate, o.startdate)) 
            - ((DATEDIFF(o.enddate, o.startdate) * 2) 
            - CASE WHEN DAYNAME(o.startdate) = 'Saturday' THEN 1 ELSE 0 END 
            - CASE WHEN DAYNAME(o.enddate) = 'Sunday' THEN 1 ELSE 0 END) + 1) AS totaldays,
            ROUND(TIME_TO_SEC(TIMEDIFF(o.endtime, o.starttime)) / 3600, 2) AS totalhours
        FROM ojt o
        JOIN participateojt po ON o.id = po.ojtid
    ) AS sub_participant
    GROUP BY userid
) AS participant_data ON u.id = participant_data.userid

LEFT JOIN (
    SELECT 
        trainer,
        SUM(totaldays * totalhours) AS trainertotalhour
    FROM (
        SELECT 
            trainer,
            ((DATEDIFF(startdate, enddate)) 
            - ((DATEDIFF(startdate, enddate) * 2) 
            - CASE WHEN DAYNAME(startdate) = 'Saturday' THEN 1 ELSE 0 END 
            - CASE WHEN DAYNAME(enddate) = 'Sunday' THEN 1 ELSE 0 END) + 1) AS totaldays,
            ROUND(TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 3600, 2) AS totalhours
        FROM training

        UNION ALL

        SELECT 
            trainername AS trainer,
            ((DATEDIFF(startdate, enddate)) 
            - ((DATEDIFF(startdate, enddate) * 2) 
            - CASE WHEN DAYNAME(startdate) = 'Saturday' THEN 1 ELSE 0 END 
            - CASE WHEN DAYNAME(enddate) = 'Sunday' THEN 1 ELSE 0 END) + 1) AS totaldays,
            ROUND(TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 3600, 2) AS totalhours
        FROM ojt
    ) AS sub_trainer
    GROUP BY trainer
) AS trainer_data ON u.staffname = trainer_data.trainer

WHERE 
    u.designation = 'CONTRACT'
ORDER BY 
    u.staffname";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        if ($row['status'] == 'RESIGN') {
            $status = '<span class="label label-pill label-danger">NOT ACTIVE</span>';
        } else {
            $status = '<span class="label label-pill label-success">ACTIVE</span>';
        }

        $output[] = array(
            'id' => $row['id'],
            'staffno' => $row['staffno'],
            'staffname' => $row['staffname'],
            'gender' => $row['gender'],
            'department' => $row['department'],
            'section' => $row['section'],
            'division' => $row['division'],
            'sumtotalhours' => $row['sumtotalhours'],
            'trainertotalhour' => $row['trainertotalhour'],
            'status' => $status,
            'btnedit' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>',
        );
    }
    echo json_encode($output);
} else if ($_POST["action"] == "fetch_user") {
    $id = $_POST["id"];
    $sql = "SELECT 
                u.*,
                COALESCE(d.name, u.division) AS division,
                COALESCE(dp.name, u.department) AS department,
                COALESCE(s.name, u.section) AS section
            FROM user u
            LEFT JOIN divisions d ON u.division_id = d.id
            LEFT JOIN departments dp ON u.department_id = dp.id
            LEFT JOIN sections s ON u.section_id = s.id
            WHERE u.id = '$id'";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    echo json_encode($row);
}
?>
