<?php
include "../../../dbconn.php";

if ($_POST["action"] == "load_trainer") {
    echo loadTrainer();
} else if ($_POST["action"] == "load_department") {
    echo loadDepartment();
} else if ($_POST["action"] == "load_section") {
    $department = $_POST['department'];
    echo loadSection($department);
} else if ($_POST["action"] == "load_staff") {
    echo loadStaff();
} else if ($_POST["action"] == "load_ojt") {
    $output = array();
    $trainid = '';
    $clerkid = '';
    $userid = '';
    $staffname = '';

    if (isset($_POST["department"])) {
        $department = $_POST["department"];
    } else {
        $department = '';
    }

    if (isset($_POST["startdate"])) {
        $startdate = $_POST["startdate"];
    } else {
        $startdate = '';
    }

    if (isset($_POST["enddate"])) {
        $enddate = $_POST["enddate"];
    } else {
        $enddate = '';
    }

    if ($department != '' && $startdate == '') {
        if ($department != 'ALL') {
            // $sql = "select *,totalhour*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where user.department = '$department' and attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
            $sql = "SELECT *, totalhour * totalstaff AS totalmanhour FROM ( SELECT ojt.id, user.department, title, startdate, enddate, starttime, endtime, venue, userid, clerkid, ((DATEDIFF(enddate, startdate)) + 1) AS totalday, ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour, SUM(participateojt.totalman) AS totalstaff FROM ojt JOIN participateojt ON ojt.id = ojtid JOIN user ON userid = user.id WHERE user.department = '$department' AND attendance = 'COMPLETEDOJT' GROUP BY ojt.id, user.department, title, startdate, enddate, starttime, endtime, venue, userid, clerkid ) AS tablea;";
            $query = mysqli_query($conn, $sql);
            if (!$query) {
                die("Query failed: " . mysqli_error($conn));
            }
            while ($row = mysqli_fetch_assoc($query)) {
                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                if (!$result1) {
                    die("Query failed: " . mysqli_error($conn));
                }
                while ($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    } else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
                $result2 = mysqli_query($conn, $query2);
                if (!$result2) {
                    die("Query failed: " . mysqli_error($conn));
                }
                while ($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    } else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete / $totalpeople) * 100;
                } else {
                    $percentage = 0;
                }
                $percentageround = round($percentage, 2);

                if ($clerkid != 0) {
                    $query4 = "select staffname from user where id = '$clerkid'";
                    $result4 = mysqli_query($conn, $query4);
                    if (!$result4) {
                        die("Query failed: " . mysqli_error($conn));
                    }
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                } else {
                    $query4 = "select staffname from user where id = '$userid'";
                    $result4 = mysqli_query($conn, $query4);
                    if (!$result4) {
                        die("Query failed: " . mysqli_error($conn));
                    }
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                }

                $output[] = array(
                    'id' => $row['id'],
                    'department' => $row['department'],
                    'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                    'keyin' => $staffname,
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => date("H:i", strtotime($row['starttime'])),
                    'endtime' => date("H:i", strtotime($row['endtime'])),
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                    'totalmanhour' => $row['totalmanhour'],
                    'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
                );
            }
        } else if ($department == 'ALL') {
            // $sql = "select *,totalhour*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
            // $sql = "SELECT 
            //             *, 
            //             totalhour * totalstaff AS totalmanhour 
            //             FROM (
            //             SELECT 
            //                 ojt.id,
            //                 user.department,
            //                 title,
            //                 startdate,
            //                 enddate,
            //                 starttime,
            //                 endtime,
            //                 venue,
            //                 userid,
            //                 clerkid,
            //                 ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
            //                 ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
            //                 SUM(participateojt.totalman) AS totalstaff
            //             FROM ojt 
            //             JOIN participateojt ON ojt.id = ojtid 
            //             JOIN user ON userid = user.id 
            //             WHERE attendance = 'COMPLETEDOJT'
            //             GROUP BY 
            //                 ojt.id, user.department, title, startdate, enddate, starttime, endtime, venue, userid, clerkid
            //             ) AS tablea;
            //             ";

            // take current month as default and limit to 50 records
            $sql = "SELECT 
                    *, 
                    totalhour * totalstaff AS totalmanhour 
                FROM (
                    SELECT 
                        ojt.id,
                        user.department,
                        title,
                        startdate,
                        enddate,
                        starttime,
                        endtime,
                        venue,
                        userid,
                        clerkid,
                        ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
                        ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
                        SUM(participateojt.totalman) AS totalstaff
                    FROM ojt 
                    JOIN participateojt ON ojt.id = ojtid 
                    JOIN user ON userid = user.id 
                    WHERE 
                        attendance = 'COMPLETEDOJT' AND
                        YEAR(startdate) = YEAR(CURDATE()) AND 
                        MONTH(startdate) = MONTH(CURDATE())
                    GROUP BY 
                        ojt.id, user.department, title, startdate, enddate, starttime, endtime, venue, userid, clerkid
                    ORDER BY startdate DESC
                    LIMIT 50
                ) AS tablea;";


            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                $trainid = $row['id'];
                $clerkid = $row['clerkid'];
                $userid = $row['userid'];

                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                while ($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    } else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
                $result2 = mysqli_query($conn, $query2);
                while ($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    } else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete / $totalpeople) * 100;
                } else {
                    $percentage = 0;
                }
                $percentageround = round($percentage, 2);

                if ($clerkid != 0) {
                    $query4 = "select staffname from user where id = '$clerkid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                } else {
                    $query4 = "select staffname from user where id = '$userid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                }

                $output[] = array(
                    'id' => $row['id'],
                    'department' => $row['department'],
                    'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                    'keyin' => $staffname,
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => date("H:i", strtotime($row['starttime'])),
                    'endtime' => date("H:i", strtotime($row['endtime'])),
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                    'totalmanhour' => $row['totalmanhour'],
                    'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
                );
            }
        }
    } else if ($department == '' && $startdate != '') {
        // $sql = "select *,totalhour*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
        $sql = " SELECT *, totalhour * totalstaff AS totalmanhour 
                    FROM (
                        SELECT 
                            ojt.id,
                            user.department,
                            title,
                            startdate,
                            enddate,
                            starttime,
                            endtime,
                            venue,
                            userid,
                            clerkid,
                            ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
                            ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
                            SUM(participateojt.totalman) AS totalstaff
                        FROM ojt
                        JOIN participateojt ON ojt.id = ojtid
                        JOIN user ON userid = user.id
                        WHERE 
                            startdate BETWEEN '$startdate' AND '$enddate'
                            AND attendance = 'COMPLETEDOJT'
                        GROUP BY 
                            ojt.id,
                            user.department,
                            title,
                            startdate,
                            enddate,
                            starttime,
                            endtime,
                            venue,
                            userid,
                            clerkid
                    ) AS tablea
                    ";

        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $trainid = $row['id'];
            $clerkid = $row['clerkid'];
            $userid = $row['userid'];

            $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
            $result1 = mysqli_query($conn, $query1);
            while ($row1 = mysqli_fetch_array($result1)) {
                if ($row1['totalpeople'] != '') {
                    $totalpeople = $row1['totalpeople'];
                } else {
                    $totalpeople = 0;
                }
            }

            $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
            $result2 = mysqli_query($conn, $query2);
            while ($row2 = mysqli_fetch_array($result2)) {
                if ($row2['totalpeople'] != '') {
                    $totalcomplete = $row2['totalpeople'];
                } else {
                    $totalcomplete = 0;
                }
            }

            if ($totalpeople != '0') {
                $percentage = ($totalcomplete / $totalpeople) * 100;
            } else {
                $percentage = 0;
            }
            $percentageround = round($percentage, 2);

            if ($clerkid != 0) {
                $query4 = "select staffname from user where id = '$clerkid'";
                $result4 = mysqli_query($conn, $query4);
                while ($row4 = mysqli_fetch_array($result4)) {
                    $staffname = $row4['staffname'];
                }
            } else {
                $query4 = "select staffname from user where id = '$userid'";
                $result4 = mysqli_query($conn, $query4);
                while ($row4 = mysqli_fetch_array($result4)) {
                    $staffname = $row4['staffname'];
                }
            }

            $output[] = array(
                'id' => $row['id'],
                'department' => $row['department'],
                'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                'keyin' => $staffname,
                'startdate' => $row['startdate'],
                'enddate' => $row['enddate'],
                'starttime' => date("H:i", strtotime($row['starttime'])),
                'endtime' => date("H:i", strtotime($row['endtime'])),
                'totalday' => $row['totalday'],
                'totalhour' => $row['totalhour'],
                'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                'totalmanhour' => $row['totalmanhour'],
                'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
            );
        }
    } else if ($department != '' && $startdate != '') {
        if ($department != 'ALL') {
            // $sql = "select *,totalhour*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where user.department = '$department' and startdate between '$startdate' and '$enddate' and attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
            $sql = "SELECT *, totalhour * totalstaff AS totalmanhour
                    FROM (
                    SELECT 
                        ojt.id,
                        user.department,
                        title,
                        startdate,
                        enddate,
                        starttime,
                        endtime,
                        venue,
                        userid,
                        clerkid,
                        ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
                        ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
                        SUM(participateojt.totalman) AS totalstaff
                    FROM ojt
                    JOIN participateojt ON ojt.id = ojtid
                    JOIN user ON userid = user.id
                    WHERE 
                        user.department = '$department'
                        AND startdate BETWEEN '$startdate' AND '$enddate'
                        AND attendance = 'COMPLETEDOJT'
                    GROUP BY 
                        ojt.id,
                        user.department,
                        title,
                        startdate,
                        enddate,
                        starttime,
                        endtime,
                        venue,
                        userid,
                        clerkid
                    ) AS tablea;
                    ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                $trainid = $row['id'];
                $clerkid = $row['clerkid'];
                $userid = $row['userid'];

                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                while ($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    } else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
                $result2 = mysqli_query($conn, $query2);
                while ($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    } else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete / $totalpeople) * 100;
                } else {
                    $percentage = 0;
                }
                $percentageround = round($percentage, 2);

                if ($clerkid != 0) {
                    $query4 = "select staffname from user where id = '$clerkid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                } else {
                    $query4 = "select staffname from user where id = '$userid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                }

                $output[] = array(
                    'id' => $row['id'],
                    'department' => $row['department'],
                    'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                    'keyin' => $staffname,
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => date("H:i", strtotime($row['starttime'])),
                    'endtime' => date("H:i", strtotime($row['endtime'])),
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                    'totalmanhour' => $row['totalmanhour'],
                    'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
                );
            }
        } else if ($department == 'ALL') {
            // $sql = "select *,(totalhour*60)*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
            $sql = "SELECT *, (totalhour * 60) * totalstaff AS totalmanhour
                    FROM (
                    SELECT 
                        ojt.id,
                        user.department,
                        title,
                        startdate,
                        enddate,
                        starttime,
                        endtime,
                        venue,
                        userid,
                        clerkid,
                        ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
                        ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
                        SUM(participateojt.totalman) AS totalstaff
                    FROM ojt
                    JOIN participateojt ON ojt.id = ojtid
                    JOIN user ON userid = user.id
                    WHERE 
                        startdate BETWEEN '$startdate' AND '$enddate'
                        AND attendance = 'COMPLETEDOJT'
                    GROUP BY 
                        ojt.id,
                        user.department,
                        title,
                        startdate,
                        enddate,
                        starttime,
                        endtime,
                        venue,
                        userid,
                        clerkid
                    ) AS tablea;
                    ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                $trainid = $row['id'];
                $clerkid = $row['clerkid'];
                $userid = $row['userid'];

                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                while ($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    } else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
                $result2 = mysqli_query($conn, $query2);
                while ($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    } else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete / $totalpeople) * 100;
                } else {
                    $percentage = 0;
                }
                $percentageround = round($percentage, 2);

                if ($clerkid != 0) {
                    $query4 = "select staffname from user where id = '$clerkid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                } else {
                    $query4 = "select staffname from user where id = '$userid'";
                    $result4 = mysqli_query($conn, $query4);
                    while ($row4 = mysqli_fetch_array($result4)) {
                        $staffname = $row4['staffname'];
                    }
                }

                $output[] = array(
                    'id' => $row['id'],
                    'department' => $row['department'],
                    'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                    'keyin' => $staffname,
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => date("H:i", strtotime($row['starttime'])),
                    'endtime' => date("H:i", strtotime($row['endtime'])),
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                    'totalmanhour' => $row['totalmanhour'],
                    'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
                );
            }
        }
    } else {
        $firstdate = date('Y-m-d', strtotime('first day of this month'));
        $lastdate = date('Y-m-d', strtotime('last day of this month'));
        // $sql = "select *,totalhour*totalstaff as totalmanhour from (select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,userid,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,sum(participateojt.totalman) as totalstaff from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where startdate between '$firstdate' and '$lastdate' and attendance = 'COMPLETEDOJT' group by ojt.id)tablea;";
        $sql = " SELECT *, totalhour * totalstaff AS totalmanhour 
                    FROM (
                        SELECT 
                            ojt.id,
                            user.department,
                            title,
                            startdate,
                            enddate,
                            starttime,
                            endtime,
                            venue,
                            userid,
                            clerkid,
                            ((DATEDIFF(enddate, startdate)) + 1) AS totalday,
                            ((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime, starttime)) / 60) / 60, 2) AS totalhour,
                            SUM(participateojt.totalman) AS totalstaff
                        FROM ojt
                        JOIN participateojt ON ojt.id = ojtid
                        JOIN user ON userid = user.id
                        WHERE 
                            startdate BETWEEN '$firstdate' AND '$lastdate'
                            AND attendance = 'COMPLETEDOJT'
                        GROUP BY 
                            ojt.id,
                            user.department,
                            title,
                            startdate,
                            enddate,
                            starttime,
                            endtime,
                            venue,
                            userid,
                            clerkid
                    ) AS tablea
                    ";

        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $trainid = $row['id'];
            $clerkid = $row['clerkid'];
            $userid = $row['userid'];


            $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
            $result1 = mysqli_query($conn, $query1);
            while ($row1 = mysqli_fetch_array($result1)) {
                if ($row1['totalpeople'] != '') {
                    $totalpeople = $row1['totalpeople'];
                } else {
                    $totalpeople = 0;
                }
            }

            $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance in ('COMPLETED','COMPLETEDOJT')";
            $result2 = mysqli_query($conn, $query2);
            while ($row2 = mysqli_fetch_array($result2)) {
                if ($row2['totalpeople'] != '') {
                    $totalcomplete = $row2['totalpeople'];
                } else {
                    $totalcomplete = 0;
                }
            }

            if ($totalpeople != '0') {
                $percentage = ($totalcomplete / $totalpeople) * 100;
            } else {
                $percentage = 0;
            }

            $percentageround = round($percentage, 2);

            if ($clerkid != 0) {
                $query4 = "select staffname from user where id = '$clerkid'";
                $result4 = mysqli_query($conn, $query4);
                while ($row4 = mysqli_fetch_array($result4)) {
                    $staffname = $row4['staffname'];
                }
            } else {
                $query4 = "select staffname from user where id = '$userid'";
                $result4 = mysqli_query($conn, $query4);
                while ($row4 = mysqli_fetch_array($result4)) {
                    $staffname = $row4['staffname'];
                }
            }

            $output[] = array(
                'id' => $row['id'],
                'department' => $row['department'],
                'title' => '<a class="linkparty" id="' . $row['id'] . '">' . $row['title'] . '</a>',
                'keyin' => $staffname,
                'startdate' => $row['startdate'],
                'enddate' => $row['enddate'],
                'starttime' => date("H:i", strtotime($row['starttime'])),
                'endtime' => date("H:i", strtotime($row['endtime'])),
                'totalday' => $row['totalday'],
                'totalhour' => $row['totalhour'],
                'totalman' => $totalcomplete . ' / ' . $totalpeople . '<br>(' . $percentageround . ' %)',
                'totalmanhour' => $row['totalmanhour'],
                'btnmodify' => '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> </button> <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>'
            );
        }
    }

    echo json_encode($output);
} else if ($_POST["action"] == "fetch_ojt") {
    $id = $_POST["id"];
    $sql = "SELECT * from ojt where id = '$id';";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    echo json_encode($row);
} else if ($_POST["action"] == "load_ojtsummary") {
    if (isset($_POST["department"])) {
        $department = $_POST["department"];
    } else {
        $department = '';
    }

    if (isset($_POST["startdate"])) {
        $startdate = $_POST["startdate"];
    } else {
        $startdate = '';
    }

    if (isset($_POST["enddate"])) {
        $enddate = $_POST["enddate"];
    } else {
        $enddate = '';
    }

    if ($department != '' && $startdate == '') {
        if ($department != 'ALL') {
            // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where department = '$department';";
            $sql = "SELECT SUM(participateojt.totalman) AS totalmans
                    FROM ojt
                    JOIN participateojt ON ojt.id = participateojt.ojtid
                    WHERE participateojt.department = '$department';
            ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['totalmans'] != null) {
                    $totalmans = $row['totalmans'];
                } else if ($row['totalmans'] == null) {
                    $totalmans = 0;
                }
            }
        } else if ($department == 'ALL') {
            // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid;";
            $sql = "SELECT SUM(ojt.totalman) AS totalmans
                    FROM ojt
                    JOIN (
                        SELECT ojtid, MAX(department) AS department
                        FROM participateojt
                        GROUP BY ojtid
                    ) AS tablea ON ojt.id = tablea.ojtid;
                    ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['totalmans'] != null) {
                    $totalmans = $row['totalmans'];
                } else if ($row['totalmans'] == null) {
                    $totalmans = 0;
                }
            }
        }
    } else if ($department == '' && $startdate != '') {
        // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where startdate between '$startdate' and '$enddate';";
        $sql = "SELECT SUM(totalman) AS totalmans
                FROM ojt
                JOIN (
                    SELECT DISTINCT ojtid
                    FROM participateojt
                ) AS tablea ON ojt.id = tablea.ojtid
                WHERE startdate BETWEEN '$startdate' AND '$enddate';
                ";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalmans'] != null) {
                $totalmans = $row['totalmans'];
            } else if ($row['totalmans'] == null) {
                $totalmans = 0;
            }
        }
    } else if ($department != '' && $startdate != '') {
        if ($department != 'ALL') {
            // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where department = '$department' and startdate between '$startdate' and '$enddate';";
            $sql = "SELECT SUM(ojt.totalman) AS totalmans
                    FROM ojt
                    JOIN (
                        SELECT ojtid, MAX(department) AS department
                        FROM participateojt
                        GROUP BY ojtid
                    ) AS tablea ON ojt.id = tablea.ojtid
                    WHERE tablea.department = '$department'
                    AND ojt.startdate BETWEEN '$startdate' AND '$enddate';
                    ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['totalmans'] != null) {
                    $totalmans = $row['totalmans'];
                } else if ($row['totalmans'] == null) {
                    $totalmans = 0;
                }
            }
        } else if ($department == 'ALL') {
            // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where startdate between '$startdate' and '$enddate';";
            $sql = "SELECT SUM(totalman) AS totalmans
                    FROM ojt
                    JOIN (
                        SELECT ojtid, MAX(department) AS department
                        FROM participateojt
                        GROUP BY ojtid
                    ) AS tablea ON ojt.id = tablea.ojtid
                    WHERE ojt.startdate BETWEEN '$startdate' AND '$enddate';
                    ";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['totalmans'] != null) {
                    $totalmans = $row['totalmans'];
                } else if ($row['totalmans'] == null) {
                    $totalmans = 0;
                }
            }
        }
    } else {
        // $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid;";
        $sql = "SELECT SUM(totalman) AS totalmans 
                FROM ojt 
                JOIN (
                    SELECT ojtid, MAX(department) AS department 
                    FROM participateojt 
                    GROUP BY ojtid
                ) AS tablea 
                ON ojt.id = tablea.ojtid;
                ";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalmans'] != null) {
                $totalmans = $row['totalmans'];
            } else if ($row['totalmans'] == null) {
                $totalmans = 0;
            }
        }
    }

    $output = ([
        'totalmans' => $totalmans,
    ]);
    echo json_encode($output);
} else if ($_POST["action"] == "load_participant") {
    $trainingid = $_POST['trainingid'];
    $output = array();
    $sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$trainingid' and designation != 'CONTRACT';";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        if ($row['attendance'] == '') {
            $status = '<span class="label label-pill label-warning">PENDING</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        } else if ($row['attendance'] == 'COMPLETEDOJT') {
            $status = '<span class="label label-pill label-success">COMPLETED</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        } else if ($row['attendance'] == 'ABSENT') {
            $status = '<span class="label label-pill label-danger">ABSENT</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        }

        $output[] = array(
            'id' => $row['id'],
            'staffno' => $row['staffno'],
            'staffname' => $row['staffname'],
            'department' => $row['department'],
            'status' => $status,
            'btnedit' => $btnedit,
        );
    }
    echo json_encode($output);
} else if ($_POST["action"] == 'fetch_trainingtitle') {
    $trainingid = $_POST['trainingid'];
    $output = array();
    $sql = "select title from ojt where id = '$trainingid';";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $title = $row['title'];
    }

    $output[] = array(
        'title' => $title,
    );

    echo json_encode($output);
} else if ($_POST["action"] == "load_contract") {
    $trainingid = $_POST['trainingid'];
    $output = array();
    $sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$trainingid' and designation = 'CONTRACT';";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        if ($row['attendance'] == '') {
            $status = '<span class="label label-pill label-warning">PENDING</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        } else if ($row['attendance'] == 'COMPLETEDOJT') {
            $status = '<span class="label label-pill label-success">COMPLETED</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        } else if ($row['attendance'] == 'ABSENT') {
            $status = '<span class="label label-pill label-danger">ABSENT</span>';
            $btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
        }

        $output[] = array(
            'id' => $row['id'],
            'staffno' => $row['staffno'],
            'staffname' => $row['staffname'],
            'department' => $row['department'],
            'status' => $status,
            'btnedit' => $btnedit,
        );
    }
    echo json_encode($output);
}

function loadTrainer()
{
    global $conn;
    $sql = "SELECT staffno,staffname FROM user order by staffno";
    $query = mysqli_query($conn, $sql);
    $options = '<option value="">-- Select Trainer --</option>';
    if (mysqli_num_rows($query) > 0) {
        // output data of each row

        while ($row = mysqli_fetch_assoc($query)) {
            $options .= '<option value="' . $row['staffname'] . '">' . $row['staffno'] . ' - ' . $row['staffname'] . '</option>';
        }
    }
    return $options;
}

function loadDepartment()
{
    global $conn;
    $sql = "SELECT DISTINCT(`department`) AS department FROM `user` ORDER BY department";
    $query = mysqli_query($conn, $sql);
    $options = '<option value="" hidden>-- Select Department --</option><option value="ALL">All Department</option>';
    if (mysqli_num_rows($query) > 0) {
        // output data of each row

        while ($row = mysqli_fetch_assoc($query)) {
            $options .= '<option value="' . $row['department'] . '">' . $row['department'] . '</option>';
        }
    }
    return $options;
}

function loadSection($department)
{
    global $conn;
    $sql = "SELECT DISTINCT(if (section = '','-',section)) as section FROM user where department = '$department' order by section";
    $query = mysqli_query($conn, $sql);
    $options = '<option value="">-- Select Section --</option>';
    if (mysqli_num_rows($query) > 0) {
        // output data of each row

        while ($row = mysqli_fetch_assoc($query)) {
            $options .= '<option value="' . $row['section'] . '">' . $row['section'] . '</option>';
        }
    }
    return $options;
}

function loadStaff()
{
    global $conn;
    $sql = "SELECT staffno,staffname FROM user order by staffno";
    $query = mysqli_query($conn, $sql);
    $options = '<option value="">-- Select Staff --</option>';
    if (mysqli_num_rows($query) > 0) {
        // output data of each row

        while ($row = mysqli_fetch_assoc($query)) {
            $options .= '<option value="' . $row['staffname'] . '">' . $row['staffno'] . ' - ' . $row['staffname'] . '</option>';
        }
    }
    return $options;
}
?>