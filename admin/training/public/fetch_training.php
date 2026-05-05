<?php
include "../../../dbconn.php";

if ($_POST["action"] == "load_trainer") {
    echo loadTrainer();
} else if ($_POST["action"] == "load_department") {
    echo loadDepartment();
} else if ($_POST["action"] == "load_staff") {
    echo loadStaff();
} else if ($_POST["action"] == "load_training") {
    $output = array();
    $trainid = '';
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];
    if ($_POST["startdate"] != '') {
        $sql = "select *,totalday*totalhour as sumhour from (select *,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training where startdate between '$startdate' and '$enddate')tablea;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $trainid = $row['id'];

            $query6 = "select count(*) as totalabsent from participation where trainingid = '$trainid' and attendance = 'ABSENT'";
            $result6 = mysqli_query($conn, $query6);
            while ($row6 = mysqli_fetch_array($result6)) {
                if ($row6['totalabsent'] != '') {
                    $totalabsent = $row6['totalabsent'];
                } else {
                    $totalabsent = 0;
                }
            }

            $query5 = "select count(*) as totalpeople from participation where trainingid = '$trainid'";
            $result5 = mysqli_query($conn, $query5);
            while ($row5 = mysqli_fetch_array($result5)) {
                if ($row5['totalpeople'] != '') {
                    $totalpeople = $row5['totalpeople'];
                } else {
                    $totalpeople = 0;
                }
            }

            $query3 = "select count(*) as totalpeople from participation where trainingid = '$trainid' and attendance = 'COMPLETED'";
            $result3 = mysqli_query($conn, $query3);
            while ($row3 = mysqli_fetch_array($result3)) {
                if ($row3['totalpeople'] != '') {
                    $totalcomplete = $row3['totalpeople'];
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

            $formatcost = number_format($row['cost']);

            $pmeData = getPMEData($conn, $trainid);
            $total_complete = $pmeData['complete'];
            $total_pending = $pmeData['pending'];
            $total_pme = $pmeData['total'];
            $percentage_pme = $pmeData['percentage'];



            $pme_check_sql = "SELECT COUNT(*) AS total_pme FROM pme 
                INNER JOIN training ON pme.trainingid = training.id
                WHERE pme.trainingid = ? AND (pme.designation = 'Executive' OR pme.designation = 'MANAGER (AM/HOS & ABOVE)')";

            $pme_stmt = $conn->prepare($pme_check_sql);
            $pme_stmt->bind_param("i", $row['id']);
            $pme_stmt->execute();
            $pme_result = $pme_stmt->get_result();
            $pme_data = $pme_result->fetch_assoc();
            $total_pme = (int) $pme_data['total_pme'];

            $buttons = '<div class="button-container">
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm viewparticipant"><i class="fa fa-search"></i></button>
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit"><i class="fa fa-edit"></i></button>
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i></button>';

            if ($total_pme > 0) {
                $buttons .= '<button type="submit" id="' . $row['id'] . '" class="btn btn-primary btn-sm viewpme"><i class="fa fa-eye"></i></button>';
            }

            $buttons .= '</div>'; // Close button container

            $pme_data_output = ($total_pme > 0)
                ? ('Comp: ' . $total_complete . '<br>Pend: ' . ($total_pending - $totalabsent).
                    '<br>Abs: ' . $totalabsent .
                    '<br>Per: ' . $percentage_pme . ' %')
                : '';

            $linkCert = '<a href="/ldms/admin/training/public/certificate/certificate.php?id=' . $row['id'] . '" id="' . $row['id'] . '">' . $row['title'] . '</a>';


            $output[] = array(
                'id' => $trainid,
                'trainingcode' => $row['trainingcode'],
                // 'title' => $row['title'],
                'title' => $linkCert,
                'program' => $row['program'],
                'startdate' => $row['startdate'],
                'enddate' => $row['enddate'],
                'starttime' => date("H:i", strtotime($row['starttime'])),
                'endtime' => date("H:i", strtotime($row['endtime'])),
                'cost' => $formatcost,
                'hadc' => $row['hadc'],
                'platform' => $row['platform'],
                'function' => $row['function'],
                'participant' => 'Par: ' . $totalpeople . '<br>Comp: ' . $totalcomplete .
                    '<br>Pend: ' . ($totalpeople - $totalcomplete - $totalabsent) .
                    '<br>Abs: ' . $totalabsent . '<br>Per: ' . $percentageround . ' %',
                'pme' => $pme_data_output, // Hide PME data if no records
                'totalday' => $row['totalday'],
                'totalhour' => $row['sumhour'],
                'totalmanhour' => $row['totalday'] * $row['totalhour'] * $totalcomplete,
                'btnedit' => $buttons, // Use dynamically generated buttons
            );


        }
    } else {
        $percentage = '';
        $sql = "select *,totalday*totalhour as sumhour from (select *,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training)tablea;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $trainid = $row['id'];

            $query6 = "select count(*) as totalabsent from participation where trainingid = '$trainid' and attendance = 'ABSENT'";
            $result6 = mysqli_query($conn, $query6);
            while ($row6 = mysqli_fetch_array($result6)) {
                if ($row6['totalabsent'] != '') {
                    $totalabsent = $row6['totalabsent'];
                } else {
                    $totalabsent = 0;
                }
            }

            $query5 = "select count(*) as totalpeople from participation where trainingid = '$trainid'";
            $result5 = mysqli_query($conn, $query5);
            while ($row5 = mysqli_fetch_array($result5)) {
                if ($row5['totalpeople'] != '') {
                    $totalpeople = $row5['totalpeople'];
                } else {
                    $totalpeople = 0;
                }
            }

            $query3 = "select count(*) as totalpeople from participation where trainingid = '$trainid' and attendance = 'COMPLETED'";
            $result3 = mysqli_query($conn, $query3);
            while ($row3 = mysqli_fetch_array($result3)) {
                if ($row3['totalpeople'] != '') {
                    $totalcomplete = $row3['totalpeople'];
                } else {
                    $totalcomplete = 0;
                }
            }

            if ($totalpeople == 0) {
                $percentage = 0;
            } else {
                $percentage = ($totalcomplete / $totalpeople) * 100;
            }

            $percentageround = round($percentage, 2);

            $formatcost = number_format($row['cost']);

            $pmeData = getPMEData($conn, $trainid);
            $total_complete = $pmeData['complete'];

            //Enhance calculations - AMIR ANWAR
            $total_pending = $pmeData['pending'] - $totalabsent;
            $total_pme = $pmeData['total'];
            $percentage_pme = $pmeData['percentage'];

            $pme_check_sql = "SELECT COUNT(*) AS total_pme FROM pme 
                INNER JOIN training ON pme.trainingid = training.id
                WHERE pme.trainingid = ? AND (pme.designation = 'Executive' OR pme.designation = 'MANAGER (AM/HOS & ABOVE)')";

            $pme_stmt = $conn->prepare($pme_check_sql);
            $pme_stmt->bind_param("i", $row['id']);
            $pme_stmt->execute();
            $pme_result = $pme_stmt->get_result();
            $pme_data = $pme_result->fetch_assoc();
            $total_pme = (int) $pme_data['total_pme'];

            $buttons = '<div class="button-container">
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm viewparticipant"><i class="fa fa-search"></i></button>
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm edit"><i class="fa fa-edit"></i></button>
                            <button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i></button>';


            if ($total_pme > 0) {
                $buttons .= '<button type="submit" id="' . $row['id'] . '" class="btn btn-primary btn-sm viewpme"><i class="fa fa-eye"></i></button>';
            }

            $buttons .= '</div>'; // Close button container
            $linkCert = '<a href="/ldms/admin/training/public/certificate/certificate.php?id=' . $row['id'] . '" id="' . $row['id'] . '">' . $row['title'] . '</a>';

            $pme_data_output = ($total_pme > 0)
                ? ('Comp: ' . $total_complete . '<br>Pend: ' . $total_pending .
                    '<br>Per: ' . $percentage_pme . ' %')
                : '';

            // $pme_data_output = if(($total_pme > 0) && ($total)){}

            $output[] = array(
                'id' => $trainid,
                'trainingcode' => $row['trainingcode'],
                // 'title' => $row['title'],
                'title' => $linkCert,
                'program' => $row['program'],
                'startdate' => $row['startdate'],
                'enddate' => $row['enddate'],
                'starttime' => date("H:i", strtotime($row['starttime'])),
                'endtime' => date("H:i", strtotime($row['endtime'])),
                'cost' => $formatcost,
                'hadc' => $row['hadc'],
                'platform' => $row['platform'],
                'function' => $row['function'],
                'participant' => 'Par: ' . $totalpeople . '<br>Comp: ' . $totalcomplete .
                    '<br>Pend: ' . ($totalpeople - $totalcomplete - $totalabsent) .
                    '<br>Abs: ' . $totalabsent . '<br>Per: ' . $percentageround . ' %',
                'pme' => $pme_data_output, // Hide PME data if no records
                'totalday' => $row['totalday'],
                'totalhour' => $row['sumhour'],
                'totalmanhour' => $row['totalday'] * $row['totalhour'] * $totalcomplete,
                'btnedit' => $buttons, // Use dynamically generated buttons
            );


        }
    }

    echo json_encode($output);
} else if ($_POST["action"] == "fetch_training") {
    $id = $_POST["id"];
    $sql = "SELECT * from training where id = '$id';";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    echo json_encode($row);
} else if ($_POST["action"] == "load_totalsummary") {
    $startdate = $_POST["startdate"];
    $enddate = $_POST["enddate"];

    if ($_POST["startdate"] == '') {
        $sql = "select sum(cost) as totalcost from training;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalcost'] != null) {
                $totalcost = number_format($row['totalcost']);
            } else if ($row['totalcost'] == null) {
                $totalcost = 0;
            }
        }

        $sql = "select sum(totalday) as totaldays from (select (DATEDIFF(enddate, startdate)+1) as totalday from training)tablea;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totaldays'] != null) {
                $totaldays = $row['totaldays'];
            } else if ($row['totaldays'] == null) {
                $totaldays = 0;
            }
        }

        $sql = "select sum(totalall) as totalhours from (select totalday*totalhour as totalall from (select (DATEDIFF(enddate, startdate)+1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training)tablea)tableb;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalhours'] != null) {
                $totalhours = $row['totalhours'];
            } else if ($row['totalhours'] == null) {
                $totalhours = 0;
            }
        }

        $sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid where attendance = 'COMPLETED' group by trainingid)tablea;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalmans'] != null) {
                $totalmans = $row['totalmans'];
            } else if ($row['totalmans'] == null) {
                $totalmans = 0;
            }
        }
    } else {
        $sql = "select sum(cost) as totalcost from training where startdate between '$startdate' and '$enddate';";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalcost'] != null) {
                $totalcost = number_format($row['totalcost']);
            } else if ($row['totalcost'] == null) {
                $totalcost = 0;
            }
        }

        $sql = "select sum(totalday) as totaldays from (select (DATEDIFF(enddate, startdate)+1) as totalday from training where startdate between '$startdate' and '$enddate')tablea;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totaldays'] != null) {
                $totaldays = $row['totaldays'];
            } else if ($row['totaldays'] == null) {
                $totaldays = 0;
            }
        }

        $sql = "select sum(totalall) as totalhours from (select totalday*totalhour as totalall from (select (DATEDIFF(enddate, startdate)+1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training where startdate between '$startdate' and '$enddate')tablea)tableb;";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['totalhours'] != null) {
                $totalhours = $row['totalhours'];
            } else if ($row['totalhours'] == null) {
                $totalhours = 0;
            }
        }

        $sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid where startdate between '$startdate' and '$enddate' and attendance = 'COMPLETED' group by trainingid)tablea;";
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
        'totalcost' => $totalcost,
        'totaldays' => $totaldays,
        'totalhours' => $totalhours,
        'totalmans' => $totalmans,
    ]);
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

function getPMEData($conn, $trainid)
{
    // Debugging: Log trainid
    error_log("getPMEData called with trainid: $trainid");

    // SQL query to count verified as complete, and pending, approved, completed as pending
    $query_pme = "SELECT 
                        SUM(CASE WHEN LOWER(status) = 'verified' THEN 1 ELSE 0 END) AS complete,
                        SUM(CASE WHEN LOWER(status) IN ('pending', 'approved', 'completed') THEN 1 ELSE 0 END) AS pending,
                        COUNT(*) AS total
                      FROM pme 
                      WHERE trainingid = '$trainid' AND (pme.designation = 'Executive' OR pme.designation = 'MANAGER (AM/HOS & ABOVE)')";

    // Debugging: Log executed query
    error_log("Executing Query: $query_pme");

    $result_pme = mysqli_query($conn, $query_pme);

    // Handle SQL errors
    if (!$result_pme) {
        error_log("SQL Error: " . mysqli_error($conn));
        return [
            'complete' => 0,
            'pending' => 0,
            'total' => 0,
            'percentage' => 0
        ];
    }

    $row_pme = mysqli_fetch_assoc($result_pme);

    // Ensure values are not null
    $complete = $row_pme['complete'] ?? 0;
    $pending = $row_pme['pending'] ?? 0;
    $total = $complete + $pending; // Ensure total is sum of counted statuses
    $percentage = ($total > 0) ? round(($complete / $total) * 100, 2) : 0;

    // Debugging: Log computed values
    error_log("PME Data - Complete: $complete, Pending: $pending, Total: $total, Percentage: $percentage");

    return [
        'complete' => $complete,
        'pending' => $pending,
        'total' => $total,
        'percentage' => $percentage
    ];
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