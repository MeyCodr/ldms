<?php
session_start();
require '../../asset/vendor/autoload.php';
include "../../dbconn.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../../login.php");
    exit();
}

$spreadsheet = new Spreadsheet();

// ===== DIVISIONS SHEET =====
$sheetDiv = $spreadsheet->getActiveSheet();
$sheetDiv->setTitle('Divisions');
$sheetDiv->fromArray(['ID', 'Division Name', 'Short Name'], null, 'A1');
$row = 2;
$res = $conn->query("SELECT id, name, shortname FROM divisions ORDER BY name");
while ($r = $res->fetch_assoc()) {
    $sheetDiv->setCellValueExplicit("A{$row}", $r['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheetDiv->setCellValue("B{$row}", $r['name']);
    $sheetDiv->setCellValue("C{$row}", $r['shortname']);
    $row++;
}
foreach (['A', 'B', 'C'] as $col) {
    $sheetDiv->getColumnDimension($col)->setAutoSize(true);
}
$sheetDiv->getStyle('A1:C1')->getFont()->setBold(true);
$sheetDiv->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');

// ===== DEPARTMENTS SHEET =====
$sheetDept = $spreadsheet->createSheet();
$sheetDept->setTitle('Departments');
$sheetDept->fromArray(['ID', 'Division ID', 'Division Name (reference only)', 'Department Name', 'Short Name'], null, 'A1');
$row = 2;
$res = $conn->query(
    "SELECT dp.id, dp.division_id, dv.name AS division_name, dp.name, dp.shortname
     FROM departments dp JOIN divisions dv ON dv.id = dp.division_id
     ORDER BY dv.name, dp.name"
);
while ($r = $res->fetch_assoc()) {
    $sheetDept->setCellValueExplicit("A{$row}", $r['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheetDept->setCellValueExplicit("B{$row}", $r['division_id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheetDept->setCellValue("C{$row}", $r['division_name']);
    $sheetDept->setCellValue("D{$row}", $r['name']);
    $sheetDept->setCellValue("E{$row}", $r['shortname']);
    $row++;
}
foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
    $sheetDept->getColumnDimension($col)->setAutoSize(true);
}
$sheetDept->getStyle('A1:E1')->getFont()->setBold(true);
$sheetDept->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');

// ===== SECTIONS SHEET =====
$sheetSect = $spreadsheet->createSheet();
$sheetSect->setTitle('Sections');
$sheetSect->fromArray(['ID', 'Department ID', 'Department Name (reference only)', 'Section Name', 'Short Name'], null, 'A1');
$row = 2;
$res = $conn->query(
    "SELECT sc.id, sc.department_id, dp.name AS department_name, sc.name, sc.shortname
     FROM sections sc JOIN departments dp ON dp.id = sc.department_id
     ORDER BY dp.name, sc.name"
);
while ($r = $res->fetch_assoc()) {
    $sheetSect->setCellValueExplicit("A{$row}", $r['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheetSect->setCellValueExplicit("B{$row}", $r['department_id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheetSect->setCellValue("C{$row}", $r['department_name']);
    $sheetSect->setCellValue("D{$row}", $r['name']);
    $sheetSect->setCellValue("E{$row}", $r['shortname']);
    $row++;
}
foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
    $sheetSect->getColumnDimension($col)->setAutoSize(true);
}
$sheetSect->getStyle('A1:E1')->getFont()->setBold(true);
$sheetSect->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');

$spreadsheet->setActiveSheetIndex(0);

$filename = 'org_structure_template_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
