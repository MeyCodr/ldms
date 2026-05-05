<?php

include "../../../dbconn.php";
require '../../../asset/vendor/autoload.php';

date_default_timezone_set("Asia/Kuala_Lumpur");
$datetime = "(" . date("Ymd/his") . ")";

$response = [];

if ($_FILES['import_file']['name']) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $clerkid = $_POST['clerkid'];

    $allowed_ext = ['xls', 'csv', 'xlsx'];
    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data_day = $spreadsheet->getSheet(0)->toArray();
        array_shift($data_day);

        foreach ($data_day as $row_day) {
            $titlebef = strtoupper(str_replace("'", "''", mysqli_real_escape_string($conn, $row_day[0])));
            $venue = strtoupper(mysqli_real_escape_string($conn, $row_day[1]));
            $startdate = date("Y-m-d", strtotime(mysqli_real_escape_string($conn, $row_day[2])));
            $enddate = date("Y-m-d", strtotime(mysqli_real_escape_string($conn, $row_day[3])));
            $starttime = date("H:i:s", strtotime(mysqli_real_escape_string($conn, $row_day[4]))); // ⬅️ ensure correct format
            $endtime = date("H:i:s", strtotime(mysqli_real_escape_string($conn, $row_day[5])));
            $trainer = strtoupper(mysqli_real_escape_string($conn, $row_day[6]));
            $trainername = strtoupper(mysqli_real_escape_string($conn, $row_day[7]));
            $staffno = strtoupper(mysqli_real_escape_string($conn, $row_day[8]));

            $title = $titlebef . " - " . $datetime;

            $sql = "SELECT title, startdate FROM ojt WHERE title = '$title'";
            $query = mysqli_query($conn, $sql);
            if (!$query) {
                $response[] = ['message' => 'DB Error', 'error' => mysqli_error($conn)];
                continue;
            }

            $titledb = $startdatedb = "";
            while ($row = mysqli_fetch_assoc($query)) {
                $titledb = str_replace("'", "''", mysqli_real_escape_string($conn, $row['title']));
                $startdatedb = mysqli_real_escape_string($conn, $row['startdate']);
            }

            if ($title != $titledb && $startdate != $startdatedb && $titlebef != '') {
                $datetime1 = new DateTime($startdate);
                $datetime2 = new DateTime($enddate);
                $interval = $datetime1->diff($datetime2);
                $woweekends = 0;

                for ($i = 0; $i <= $interval->days; $i++) {
                    $datetime1->modify('+1 day');
                    $weekday = $datetime1->format('w');
                    if ($weekday !== "1" && $weekday !== "7") {
                        $woweekends++;
                    }
                }

                $hours = ((strtotime($endtime) - strtotime($starttime)) / 60) / 60;

                $sql = "INSERT INTO ojt (title, startdate, enddate, starttime, endtime, venue, trainername, totalday, totalhour, trainertype) 
                        VALUES ('$title', '$startdate', '$enddate', '$starttime', '$endtime', '$venue', '$trainername', '$woweekends', '$hours', '$trainer')";
                if (!mysqli_query($conn, $sql)) {
                    $response[] = ['message' => 'DB Error', 'error' => mysqli_error($conn)];
                    continue;
                }

                $sql = "SELECT MAX(id) AS id FROM ojt WHERE title = '$title'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $ojtid = mysqli_real_escape_string($conn, $row['id']);

                $code = sprintf("%05d", $ojtid);
                $year = date("dmy");
                $trid = 'OJ' . $year . $code;

                $sql = "SELECT id, department FROM user WHERE staffno = '$staffno'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $userid = mysqli_real_escape_string($conn, $row['id']);
                $department = mysqli_real_escape_string($conn, $row['department']);

                $sql = "INSERT INTO participateojt (ojtid, userid, totalman, department, clerkid) 
                        VALUES ('$ojtid', '$userid', '1', '$department', '$clerkid')";
                mysqli_query($conn, $sql);

                $sql = "SELECT SUM(totalman) AS totaluser FROM participateojt WHERE ojtid = '$ojtid'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $totaluser = mysqli_real_escape_string($conn, $row['totaluser']);

                $sql = "UPDATE ojt SET totalman = '$totaluser', trainingcode = '$trid' WHERE id = '$ojtid'";
                mysqli_query($conn, $sql);

                $response[] = ['message' => 'insert', 'title' => $title];
            } else if ($title == $titledb && $startdate == $startdatedb && $titlebef != '') {
                $sql = "SELECT MAX(id) AS id FROM ojt WHERE title = '$title'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $ojtid = mysqli_real_escape_string($conn, $row['id']);

                $sql = "SELECT id, department FROM user WHERE staffno = '$staffno'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $userid = mysqli_real_escape_string($conn, $row['id']);
                $department = mysqli_real_escape_string($conn, $row['department']);

                $sql = "INSERT INTO participateojt (ojtid, userid, totalman, department, clerkid) 
                        VALUES ('$ojtid', '$userid', '1', '$department', '$clerkid')";
                mysqli_query($conn, $sql);

                $sql = "SELECT SUM(totalman) AS totaluser FROM participateojt WHERE ojtid = '$ojtid'";
                $query = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($query);
                $totaluser = mysqli_real_escape_string($conn, $row['totaluser']);

                $sql = "UPDATE ojt SET totalman = '$totaluser' WHERE id = '$ojtid'";
                mysqli_query($conn, $sql);

                $response[] = ['message' => 'insert', 'title' => $title];
            }
        }

        echo json_encode($response); // ✅ final JSON response
    } else {
        echo json_encode(['message' => 'Invalid file type']);
    }
} else {
    echo json_encode(['message' => 'File is empty or not uploaded']);
}
