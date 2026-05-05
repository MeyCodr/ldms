<?php
include "../../../dbconn.php";
require '../../../asset/vendor/autoload.php';

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

date_default_timezone_set("Asia/Kuala_Lumpur");
$datetime = "(" . date("Ymd/his") . ")";

if (!empty($_FILES["import_file"]["name"])) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $clerkid = $_POST['clerkid'];
    $titledb = '';
    $startdatedb = '';

    $allowed_ext = ['xls', 'csv', 'xlsx'];
    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data_day = $spreadsheet->getSheet(0)->toArray();
        array_shift($data_day);
        foreach ($data_day as $row_day) {
            $titlebef = strtoupper(mysqli_real_escape_string($conn, $row_day[0]));
            $venue = strtoupper(mysqli_real_escape_string($conn, $row_day[1]));
            $startdate = date("Y-m-d", strtotime(mysqli_real_escape_string($conn, $row_day[2])));
            $enddate = date("Y-m-d", strtotime(mysqli_real_escape_string($conn, $row_day[3])));
            $starttime = mysqli_real_escape_string($conn, $row_day[4]);
            $endtime = mysqli_real_escape_string($conn, $row_day[5]);
            $trainer = strtoupper(mysqli_real_escape_string($conn, $row_day[6]));
            $trainername = strtoupper(mysqli_real_escape_string($conn, $row_day[7]));
            $staffno = strtoupper(mysqli_real_escape_string($conn, $row_day[8]));

            $title = $titlebef . " - " . $datetime;

            $sql = "SELECT title,startdate FROM ojt where title = '$title'";
            $query = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($query)) {
                $titledb = mysqli_real_escape_string($conn, $row['title']);
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

                $sql = "insert into ojt (title, startdate, enddate, starttime, endtime, venue, trainername, totalday, totalhour, trainertype) values ('$title', '$startdate', '$enddate', '$starttime', '$endtime', '$venue', '$trainername', '$woweekends', '$hours', '$trainer')";
                mysqli_query($conn, $sql);

                $sql = "select max(id) as id from ojt where title = '$title'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $ojtid = mysqli_real_escape_string($conn, $row['id']);
                }

                $code = sprintf("%05d", $ojtid);
                $year = date("dmy");
                $trid = 'OJ' . $year . $code;

                $sql = "select id,department from user where staffno = '$staffno'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $userid = mysqli_real_escape_string($conn, $row['id']);
                    $department = mysqli_real_escape_string($conn, $row['department']);
                }

                $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid) values ('$ojtid','$userid','1','$department','$clerkid')";
                mysqli_query($conn, $sql);

                $sql = "select sum(totalman) as totaluser from participateojt where ojtid = '$ojtid'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $totaluser = mysqli_real_escape_string($conn, $row['totaluser']);
                }

                $sql = "update ojt set totalman =  '$totaluser', trainingcode = '$trid' where id = '$ojtid'";
                if (mysqli_query($conn, $sql)) {
                    echo json_encode(['message' => 'insert']);
                } else {
                    echo json_encode(['message' => 'error']);
                }
            } else if ($title == $titledb && $startdate == $startdatedb && $titlebef != '') {
                $sql = "select max(id) as id from ojt where title = '$title'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $ojtid = mysqli_real_escape_string($conn, $row['id']);
                }

                $sql = "select id,department from user where staffno = '$staffno'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $userid = mysqli_real_escape_string($conn, $row['id']);
                    $department = mysqli_real_escape_string($conn, $row['department']);
                }

                $sql = "insert into participateojt (ojtid,userid,totalman,department,clerkid) values ('$ojtid','$userid','1','$department','$clerkid')";
                mysqli_query($conn, $sql);

                $sql = "select sum(totalman) as totaluser from participateojt where ojtid = '$ojtid'";
                $query = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($query)) {
                    $totaluser = mysqli_real_escape_string($conn, $row['totaluser']);
                }

                $sql = "update ojt set totalman =  '$totaluser' where id = '$ojtid'";
                if (mysqli_query($conn, $sql)) {
                    echo json_encode(['message' => 'insert']);
                } else {
                    echo json_encode(['message' => 'error']);
                }
            }
        }
    }
} else {
    echo 'Error';
}
?>