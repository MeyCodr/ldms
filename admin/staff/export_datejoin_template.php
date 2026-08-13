<?php
    session_start();
    require '../../asset/vendor/autoload.php';
    include "../../dbconn.php";

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;
    use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
        header("Location: ../../login.php");
        exit();
    }

    $plantOptions = [
        'ALAM IMPIAN PLANT',
        'ALAM MEGAH PLANT',
        'BUKIT BERUNTUNG PLANT',
        'FIF TANJUNG MALIM',
        'PEGOH PLANT',
        'PEKAN PLANT',
        'RASA PLANT',
        'SHAH ALAM 1 PLANT',
        'SHAH ALAM 2 PLANT',
        'TANJUNG MALIM 2',
        'WAREHOUSE BB',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Date Join & Plant');
    $sheet->fromArray(['Staff No', 'Staff Name (reference only)', 'Date Join (YYYY-MM-DD)', 'Plant'], null, 'A1');

    $row = 2;
    $res = $conn->query("SELECT staffno, staffname, date_join, plant FROM user WHERE staffno IS NOT NULL AND staffno <> '' ORDER BY staffno");
    while ($r = $res->fetch_assoc()) {
        $sheet->setCellValueExplicit("A{$row}", $r['staffno'], DataType::TYPE_STRING);
        $sheet->setCellValue("B{$row}", $r['staffname']);
        $sheet->setCellValueExplicit("C{$row}", $r['date_join'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", $r['plant'] ?: '', DataType::TYPE_STRING);
        $row++;
    }
    $lastRow = $row - 1;

    foreach (['A', 'B', 'C', 'D'] as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getStyle('A1:D1')->getFont()->setBold(true);
    $sheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');

    // ===== PLANT OPTIONS SHEET (reference list + dropdown validation source) =====
    $optionsSheet = $spreadsheet->createSheet();
    $optionsSheet->setTitle('Plant Options');
    $optionsSheet->fromArray(['Valid Plant Values'], null, 'A1');
    $optRow = 2;
    foreach ($plantOptions as $option) {
        $optionsSheet->setCellValue("A{$optRow}", $option);
        $optRow++;
    }
    $optionsSheet->getColumnDimension('A')->setAutoSize(true);
    $optionsSheet->getStyle('A1')->getFont()->setBold(true);

    if ($lastRow >= 2) {
        $validation = $sheet->getCell('D2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid Plant');
        $validation->setError('Please select a plant from the dropdown list.');
        $validation->setFormula1("'Plant Options'!\$A\$2:\$A\$" . (count($plantOptions) + 1));

        for ($r = 2; $r <= $lastRow; $r++) {
            $sheet->getCell("D{$r}")->setDataValidation(clone $validation);
        }
    }

    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'date_join_plant_template_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
?>
