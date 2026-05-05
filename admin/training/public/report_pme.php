<?php
 include "../../../dbconn.php";

 require '../../../asset/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Fetch all data from `pme` table and join with `user` to get staffname of verifier & HOD
$sql = "SELECT pme.*,
               user.staffname AS verified_by,
               hod.staffname AS hod_name
        FROM pme
        LEFT JOIN user ON pme.verified_by = user.id
        LEFT JOIN user AS hod ON pme.hodid = hod.id";
$result = $conn->query($sql);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->getSheetView()->setZoomScale(85);

// Set column headers (Modify based on your table structure)
$headers = ["staffno", "staffname", "training title", "Evaluation Priod Start", "Evaluation Priod End", "ojt",
            "level rating", "level percent", "level remark", "level rating2", "level percent2", "level remark2",
            "behavioral rating", "behavioral percent", "behavioral remark", "result rating", "result percent", "result remark", "Average Mark",
             "Status", "HOD Name", "verified by"];
$columnIndex = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . '1', $header);
    $columnIndex++;
}

// Insert data into the spreadsheet
$rowNumber = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $row['staffno']);
    $sheet->setCellValue('B' . $rowNumber, $row['staffname']);
    $sheet->setCellValue('C' . $rowNumber, $row['training_title']);
    $sheet->setCellValue('D' . $rowNumber, $row['from_date']);
    $sheet->setCellValue('E' . $rowNumber, $row['to_date']);
    $sheet->setCellValue('F' . $rowNumber, $row['ojt']);
    $sheet->setCellValue('G' . $rowNumber, $row['level_rating']);
    $sheet->setCellValue('H' . $rowNumber, $row['level_percent']);
    $sheet->setCellValue('I' . $rowNumber, $row['level_remark']);
    $sheet->setCellValue('J' . $rowNumber, $row['level_rating2']);
    $sheet->setCellValue('K' . $rowNumber, $row['level_percent2']);
    $sheet->setCellValue('L' . $rowNumber, $row['level_remark2']);
    $sheet->setCellValue('M' . $rowNumber, $row['behavioral_rating']);
    $sheet->setCellValue('N' . $rowNumber, $row['behavioral_percent']);
    $sheet->setCellValue('O' . $rowNumber, $row['behavioral_remark']);
    $sheet->setCellValue('P' . $rowNumber, $row['result_rating']);
    $sheet->setCellValue('Q' . $rowNumber, $row['result_percent']);
    $sheet->setCellValue('R' . $rowNumber, $row['result_remark']);
    $sheet->setCellValue('S' . $rowNumber, $row['average_mark']);
    $sheet->setCellValue('T' . $rowNumber, $row['status']);
    $sheet->setCellValue('U' . $rowNumber, $row['hod_name']);
    $sheet->setCellValue('V' . $rowNumber, $row['verified_by']); 

    
    $rowNumber++;

    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);
    $sheet->getColumnDimension('F')->setAutoSize(true);
    $sheet->getColumnDimension('G')->setAutoSize(true);
    $sheet->getColumnDimension('H')->setAutoSize(true);
    $sheet->getColumnDimension('I')->setAutoSize(true);
    $sheet->getColumnDimension('J')->setAutoSize(true);
    $sheet->getColumnDimension('K')->setAutoSize(true);
    $sheet->getColumnDimension('L')->setAutoSize(true);
    $sheet->getColumnDimension('M')->setAutoSize(true);
    $sheet->getColumnDimension('N')->setAutoSize(true);
    $sheet->getColumnDimension('O')->setAutoSize(true);
    $sheet->getColumnDimension('P')->setAutoSize(true);
    $sheet->getColumnDimension('Q')->setAutoSize(true);
    $sheet->getColumnDimension('R')->setAutoSize(true);
    $sheet->getColumnDimension('S')->setAutoSize(true);
    $sheet->getColumnDimension('T')->setAutoSize(true);
    $sheet->getColumnDimension('U')->setAutoSize(true);
    $sheet->getColumnDimension('V')->setAutoSize(true);
}

// Set Headers for File Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="PME_Report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Close connection
$conn->close();
exit();
?>
