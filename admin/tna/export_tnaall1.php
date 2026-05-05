<?php
include "../../dbconn.php";

require '../../asset/vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getSheet(0);
$spreadsheet->getSheet(0)->setTitle("EXEC");
$sheet->getSheetView()->setZoomScale(85);
$query = "select training,section,count(*) as totalstaff from tna where userid != 0 and department = '' group by training,section order by totalstaff desc;";
$query_run = mysqli_query($conn, $query);
if (mysqli_num_rows($query_run) > 0) {
    $rowCountOrder = 4;
    $bilstaff = 1;
    $sheet->setCellValue('A1', 'SUMMARY LIST OF TRAINING REQUEST FOR EXECUTIVE');
    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A3', 'No');
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->setCellValue('B3', 'Training Title');
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->setCellValue('C3', 'Section');
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->setCellValue('D3', 'No of Request');
    $sheet->getColumnDimension('D')->setAutoSize(true);
    foreach ($query_run as $data) {
        $sheet->setCellValue('A' . $rowCountOrder, $bilstaff);
        $sheet->setCellValue('B' . $rowCountOrder, $data['training']);
        $sheet->setCellValue('C' . $rowCountOrder, $data['section']);
        $sheet->setCellValue('D' . $rowCountOrder, $data['totalstaff']);
        $bilstaff++;
        $rowCountOrder++;
    }
}
$sheet->getStyle('A3:D' . ($rowCountOrder - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$query1 = "select othertr,count(*) as totalstaff from tna where userid != 0 and department = '' and training = 'OTHERS' group by othertr order by totalstaff desc;";
$query_run1 = mysqli_query($conn, $query1);
if (mysqli_num_rows($query_run1) > 0) {
    $rowCountOrder1 = $rowCountOrder + 4;
    $titlerow = $rowCountOrder + 1;
    $bilstaff = 1;
    $sheet->setCellValue('A' . $titlerow, 'SUMMARY LIST OF OTHER TRAINING REQUEST FOR EXECUTIVE');
    $sheet->mergeCells('A' . $titlerow . ':C' . $titlerow);
    $sheet->setCellValue('A' . ($rowCountOrder + 3), 'No');
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->setCellValue('B' . ($rowCountOrder + 3), 'Training Title');
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->setCellValue('C' . ($rowCountOrder + 3), 'No of Request');
    $sheet->getColumnDimension('C')->setAutoSize(true);
    foreach ($query_run1 as $data1) {
        $sheet->setCellValue('A' . $rowCountOrder1, $bilstaff);
        $sheet->setCellValue('B' . $rowCountOrder1, $data1['othertr']);
        $sheet->setCellValue('C' . $rowCountOrder1, $data1['totalstaff']);
        $bilstaff++;
        $rowCountOrder1++;
    }
}
$sheet->getStyle('A' . ($titlerow + 2) . ':C' . ($rowCountOrder1 - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$spreadsheet->createSheet();
$sheet1 = $spreadsheet->getSheet(1);
$spreadsheet->getSheet(1)->setTitle("NON EXEC");
$sheet1->getSheetView()->setZoomScale(85);
$query = "select training,section,sum(totalman) as totalstaff from (select training,section,count(*) as totalman from tna where userid in (select userid from user join tna on user.id = userid where designation = 'NON EXECUTIVE') and department = '' group by training,section union select training,section,sum(totalstaff) as totalman from (select training,section,department,grade,concat(department, '/', grade) as departmentid from tna where department != '' group by training,section,department,grade)tablea join (select department,grade,concat(department, '/', grade) as departmentid,count(*) as totalstaff from user where designation = 'NON EXECUTIVE' and hodid = 0 group by department,grade)tableb on tablea.departmentid = tableb.departmentid group by training,section)tablea group by training,section order by totalstaff desc;";
$query_run = mysqli_query($conn, $query);
if (mysqli_num_rows($query_run) > 0) {
    $rowCountOrder = 4;
    $bilstaff = 1;
    $sheet1->setCellValue('A1', 'SUMMARY LIST OF TRAINING REQUEST FOR NON EXECUTIVE');
    $sheet1->mergeCells('A1:C1');
    $sheet1->setCellValue('A3', 'No');
    $sheet1->getColumnDimension('A')->setAutoSize(true);
    $sheet1->setCellValue('B3', 'Training Title');
    $sheet1->getColumnDimension('B')->setAutoSize(true);
    $sheet1->setCellValue('C3', 'Section');
    $sheet1->getColumnDimension('C')->setAutoSize(true);
    $sheet1->setCellValue('D3', 'No of Request');
    $sheet1->getColumnDimension('D')->setAutoSize(true);
    foreach ($query_run as $data) {
        $sheet1->setCellValue('A' . $rowCountOrder, $bilstaff);
        $sheet1->setCellValue('B' . $rowCountOrder, $data['training']);
        $sheet1->setCellValue('C' . $rowCountOrder, $data['section']);
        $sheet1->setCellValue('D' . $rowCountOrder, $data['totalstaff']);
        $bilstaff++;
        $rowCountOrder++;
    }
}
$sheet1->getStyle('A3:D' . ($rowCountOrder - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$query1 = "select othertr,sum(totalman) as totalstaff from (select othertr,count(*) as totalman from tna where userid in (select userid from user join tna on user.id = userid where designation = 'NON EXECUTIVE') and department = '' and training = 'OTHERS' group by othertr union select othertr,sum(totalstaff) as totalman from (select othertr,department,grade,concat(department, '/', grade) as departmentid from tna where department != '' and training = 'OTHERS' group by othertr,department,grade)tablea join (select department,grade,concat(department, '/', grade) as departmentid,count(*) as totalstaff from user where designation = 'NON EXECUTIVE' and hodid = 0 group by department,grade)tableb on tablea.departmentid = tableb.departmentid group by othertr)tablea group by othertr order by totalstaff desc;";
$query_run1 = mysqli_query($conn, $query1);
if (mysqli_num_rows($query_run1) > 0) {
    $rowCountOrder1 = $rowCountOrder + 4;
    $titlerow = $rowCountOrder + 1;
    $bilstaff = 1;
    $sheet1->setCellValue('A' . $titlerow, 'SUMMARY LIST OF OTHER TRAINING REQUEST FOR NON EXECUTIVE');
    $sheet1->mergeCells('A' . $titlerow . ':C' . $titlerow);
    $sheet1->setCellValue('A' . ($rowCountOrder + 3), 'No');
    $sheet1->getColumnDimension('A')->setAutoSize(true);
    $sheet1->setCellValue('B' . ($rowCountOrder + 3), 'Training Title');
    $sheet1->getColumnDimension('B')->setAutoSize(true);
    $sheet1->setCellValue('C' . ($rowCountOrder + 3), 'No of Request');
    $sheet1->getColumnDimension('C')->setAutoSize(true);
    foreach ($query_run1 as $data) {
        $sheet1->setCellValue('A' . $rowCountOrder1, $bilstaff);
        $sheet1->setCellValue('B' . $rowCountOrder1, $data['othertr']);
        $sheet1->setCellValue('C' . $rowCountOrder1, $data['totalstaff']);
        $bilstaff++;
        $rowCountOrder1++;
    }
}
$sheet1->getStyle('A' . ($titlerow + 2) . ':C' . ($rowCountOrder1 - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(2);
$spreadsheet->getSheet(2)->setTitle("SUMMARY");
$sheet2->getSheetView()->setZoomScale(85);
$query = "select training,othertr,tna.section,staffno,staffname,gap,user.department,designation from user join tna on user.id = userid order by staffname desc;";
$query_run = mysqli_query($conn, $query);
if (mysqli_num_rows($query_run) > 0) {
    $rowCountOrder = 4;
    $bilstaff = 1;
    $sheet2->setCellValue('A1', 'List of Training request by Descending Order (by Name)');
    $sheet2->mergeCells('A1:C1');
    $sheet2->setCellValue('A3', 'No');
    $sheet2->getColumnDimension('A')->setAutoSize(true);
    $sheet2->setCellValue('B3', 'Training');
    $sheet2->getColumnDimension('B')->setAutoSize(true);
    $sheet2->setCellValue('C3', 'Other Training');
    $sheet2->getColumnDimension('C')->setAutoSize(true);
    $sheet2->setCellValue('D3', 'Section');
    $sheet2->getColumnDimension('D')->setAutoSize(true);
    $sheet2->setCellValue('E3', 'Staff Number');
    $sheet2->getColumnDimension('E')->setAutoSize(true);
    $sheet2->setCellValue('F3', 'Name');
    $sheet2->getColumnDimension('F')->setAutoSize(true);
    $sheet2->setCellValue('G3', 'Skill Gap');
    $sheet2->getColumnDimension('G')->setAutoSize(true);
    $sheet2->setCellValue('H3', 'Department');
    $sheet2->getColumnDimension('H')->setAutoSize(true);
    $sheet2->setCellValue('I3', 'Designation');
    $sheet2->getColumnDimension('I')->setAutoSize(true);
    foreach ($query_run as $data) {
        $sheet2->setCellValue('A' . $rowCountOrder, $bilstaff);
        $sheet2->setCellValue('B' . $rowCountOrder, $data['training']);
        $sheet2->setCellValue('C' . $rowCountOrder, $data['othertr']);
        $sheet2->setCellValue('D' . $rowCountOrder, $data['section']);
        $sheet2->setCellValue('E' . $rowCountOrder, $data['staffno']);
        $sheet2->setCellValue('F' . $rowCountOrder, $data['staffname']);
        $sheet2->setCellValue('G' . $rowCountOrder, $data['gap']);
        $sheet2->setCellValue('H' . $rowCountOrder, $data['department']);
        $sheet2->setCellValue('I' . $rowCountOrder, $data['designation']);
        $bilstaff++;
        $rowCountOrder++;
    }
}
$sheet2->getStyle('A3:I' . ($rowCountOrder - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="TNA Training Summary.xlsx"');
$writer->save('php://output');
?>