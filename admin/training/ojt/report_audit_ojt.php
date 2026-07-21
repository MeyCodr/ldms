<?php

    include "../../../dbconn.php";

    require '../../../asset/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;

    ini_set('memory_limit', '2048M');

    $startdate = $_POST['startdate1'];
    $enddate = $_POST['enddate1'];

    $attendance = 'COMPLETEDOJT';

    $maxRows = 100000;
    $countStmt = $conn->prepare("SELECT COUNT(*) AS c
                                    FROM ojt
                                    JOIN participateojt ON ojt.id = ojtid
                                    JOIN user ON userid = user.id
                                    WHERE attendance = ? AND startdate BETWEEN ? AND ?");
    $countStmt->bind_param('sss', $attendance, $startdate, $enddate);
    $countStmt->execute();
    $rowCount = $countStmt->get_result()->fetch_assoc()['c'];

    if ($rowCount > $maxRows) {
        http_response_code(400);
        echo "The selected date range has " . number_format($rowCount) . " records, which is too large to export in one file. Please narrow the date range (under " . number_format($maxRows) . " records) and try again.";
        exit;
    }

$stmt = $conn->prepare("SELECT staffno, staffname,gender, designation, user.department, section, title, venue, startdate, enddate, starttime, endtime, trainername, q1, q2, q3
                        FROM ojt
                        JOIN participateojt ON ojt.id = ojtid
                        JOIN user ON userid = user.id
                        WHERE attendance = ? AND startdate BETWEEN ? AND ?");

$stmt->bind_param('sss', $attendance, $startdate, $enddate);
$stmt->execute();
$query_run = $stmt->get_result();

if ($query_run->num_rows > 0) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getSheet(0);
    $sheet->getSheetView()->setZoomScale(85);

    $sheet->fromArray([[
        'NO', 'STAFF NUMBER', 'FULL NAME', 'GENDER', 'DESIGNATION', 'DEPARTMENT', 'SECTION', 'TITLE', 'VENUE',
        'START DATE', 'END DATE', 'START TIME', 'END TIME', 'TRAINER',
        'Please highlight what have you learned from the OJT',
        'Your knowledge / skill before training (Pengetahuan/kemahiran sebelum training)',
        'Your knowledge / skill after training (Pengetahuan/kemahiran selepas latihan)'
    ]], null, 'A1');

    $rows = [];
    $bil = 1;
    while ($data = $query_run->fetch_assoc()) {
        $rows[] = [
            $bil,
            $data['staffno'],
            $data['staffname'],
            $data['gender'],
            $data['designation'],
            $data['department'],
            $data['section'],
            $data['title'],
            $data['venue'],
            $data['startdate'],
            $data['enddate'],
            $data['starttime'],
            $data['endtime'],
            $data['trainername'],
            $data['q1'],
            $data['q2'],
            $data['q3']
        ];
        $bil++;
    }

    $sheet->fromArray($rows, null, 'A2');
    unset($rows);

    $year = date("Y");
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Audit Report OJT ' . $year . '.xlsx"');
    $writer->save('php://output');
}
