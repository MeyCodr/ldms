<?php

    include "../../../dbconn.php";

    require '../../../asset/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;

    $startdate = $_POST['startdate1'];
    $enddate = $_POST['enddate1'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getSheet(0);
$sheet->getSheetView()->setZoomScale(85);

$stmt = $conn->prepare("SELECT staffno, staffname,gender, designation, user.department, section, title, venue, startdate, enddate, starttime, endtime, trainername, q1, q2, q3 
                        FROM ojt 
                        JOIN participateojt ON ojt.id = ojtid 
                        JOIN user ON userid = user.id 
                        WHERE attendance = ? AND startdate BETWEEN ? AND ?");

$attendance = 'COMPLETEDOJT';
$stmt->bind_param('sss', $attendance, $startdate, $enddate);
$stmt->execute();
$query_run = $stmt->get_result();

if ($query_run->num_rows > 0) {
    $sheet->setCellValue('A1', 'NO');
    $sheet->setCellValue('B1', 'STAFF NUMBER');
    $sheet->setCellValue('C1', 'FULL NAME');
    $sheet->setCellValue('D1', 'GENDER');
    $sheet->setCellValue('E1', 'DESIGNATION');
    $sheet->setCellValue('F1', 'DEPARTMENT');
    $sheet->setCellValue('G1', 'SECTION');
    $sheet->setCellValue('H1', 'TITLE');
    $sheet->setCellValue('I1', 'VENUE');
    $sheet->setCellValue('J1', 'START DATE');
    $sheet->setCellValue('K1', 'END DATE');
    $sheet->setCellValue('L1', 'START TIME');
    $sheet->setCellValue('M1', 'END TIME');
    $sheet->setCellValue('N1', 'TRAINER');
    $sheet->setCellValue('O1', 'Please highlight what have you learned from the OJT');
    $sheet->setCellValue('P1', 'Your knowledge / skill before training (Pengetahuan/kemahiran sebelum training)');
    $sheet->setCellValue('Q1', 'Your knowledge / skill after training (Pengetahuan/kemahiran selepas latihan)');

    $rowCountOrder = 2;
    $bil = 1;
    foreach ($query_run as $data) {
        $sheet->setCellValue('A' . $rowCountOrder, $bil);
        $sheet->setCellValue('B' . $rowCountOrder, $data['staffno']);
        $sheet->setCellValue('C' . $rowCountOrder, $data['staffname']);
        $sheet->setCellValue('D' . $rowCountOrder, $data['gender']);  
        $sheet->setCellValue('E' . $rowCountOrder, $data['designation']);
        $sheet->setCellValue('F' . $rowCountOrder, $data['department']);
        $sheet->setCellValue('G' . $rowCountOrder, $data['section']);
        $sheet->setCellValue('H' . $rowCountOrder, $data['title']);
        $sheet->setCellValue('I' . $rowCountOrder, $data['venue']);
        $sheet->setCellValue('J' . $rowCountOrder, $data['startdate']);
        $sheet->setCellValue('K' . $rowCountOrder, $data['enddate']);
        $sheet->setCellValue('L' . $rowCountOrder, $data['starttime']);
        $sheet->setCellValue('M' . $rowCountOrder, $data['endtime']);
        $sheet->setCellValue('N' . $rowCountOrder, $data['trainername']);
        $sheet->setCellValue('O' . $rowCountOrder, $data['q1']);
        $sheet->setCellValue('P' . $rowCountOrder, $data['q2']);
        $sheet->setCellValue('Q' . $rowCountOrder, $data['q3']);

        $rowCountOrder++;
        $bil++;
    }

    $year = date("Y");
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Audit Report OJT ' . $year . '.xlsx"');
    $writer->save('php://output');
}
