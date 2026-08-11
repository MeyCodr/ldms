<?php
    session_start();
    require '../../asset/vendor/autoload.php';
    include "../../dbconn.php";

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
        header("Location: ../../login.php");
        exit();
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Date Join');
    $sheet->fromArray(['Staff No', 'Staff Name (reference only)', 'Date Join (YYYY-MM-DD)'], null, 'A1');

    $row = 2;
    $res = $conn->query("SELECT staffno, staffname, date_join FROM user WHERE staffno IS NOT NULL AND staffno <> '' ORDER BY staffno");
    while ($r = $res->fetch_assoc()) {
        $sheet->setCellValueExplicit("A{$row}", $r['staffno'], DataType::TYPE_STRING);
        $sheet->setCellValue("B{$row}", $r['staffname']);
        $sheet->setCellValueExplicit("C{$row}", $r['date_join'] ?: '', DataType::TYPE_STRING);
        $row++;
    }

    foreach (['A', 'B', 'C'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getStyle('A1:C1')->getFont()->setBold(true);
    $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');

    $filename = 'date_join_template_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
?>
