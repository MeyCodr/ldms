<?php
session_start();
require '../../asset/vendor/autoload.php';
include "../../tna_training_options.php";

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../../login.php");
    exit();
}

// Rows the import template leaves room for; validations cover this range.
const TEMPLATE_ROWS = 2000;

$sections = tna_training_sections();
$spreadsheet = new Spreadsheet();

// ===== TRAINING OPTIONS SHEET =====
// Same order the forms show: per section, ungrouped options first, then
// each group in order. The import reads the row order back as the new order.
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Training Options');
$sheet->fromArray(['ID', 'Section', 'Group', 'Training Name', 'Status'], null, 'A1');

$rows = [];
$res = $conn->query(
    "SELECT o.id, o.section, c.name AS grp, o.name, o.is_active
     FROM tna_training_option o
     LEFT JOIN tna_training_category c ON c.id = o.category_id
     ORDER BY o.category_id IS NOT NULL, c.sort_order, o.sort_order, o.id"
);
while ($r = $res->fetch_assoc()) {
    $rows[$r['section']][] = $r;
}

$row = 2;
foreach ($sections as $key => $label) {
    foreach ($rows[$key] ?? [] as $r) {
        $sheet->setCellValueExplicit("A{$row}", $r['id'], DataType::TYPE_STRING);
        $sheet->setCellValue("B{$row}", $label);
        $sheet->setCellValueExplicit("C{$row}", (string) $r['grp'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", $r['name'], DataType::TYPE_STRING);
        $sheet->setCellValue("E{$row}", (int) $r['is_active'] ? 'Active' : 'Hidden');
        $row++;
    }
}

$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(42);
$sheet->getColumnDimension('C')->setWidth(45);
$sheet->getColumnDimension('D')->setWidth(85);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');
// ID column greyed out: it is how the import recognises existing options.
$sheet->getStyle('A2:A' . TEMPLATE_ROWS)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
$sheet->getStyle('A2:A' . TEMPLATE_ROWS)->getFont()->getColor()->setRGB('777777');
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:E' . max(2, $row - 1));

// ===== LISTS SHEET (hidden source for the dropdowns) =====
$lists = $spreadsheet->createSheet();
$lists->setTitle('Lists');
$i = 1;
foreach ($sections as $label) {
    $lists->setCellValue("A{$i}", $label);
    $i++;
}
$lists->setCellValue('B1', 'Active');
$lists->setCellValue('B2', 'Hidden');
$lists->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

function list_validation($formula, $title)
{
    $v = new DataValidation();
    $v->setType(DataValidation::TYPE_LIST);
    $v->setErrorStyle(DataValidation::STYLE_STOP);
    $v->setAllowBlank(true);
    $v->setShowDropDown(true);
    $v->setShowErrorMessage(true);
    $v->setErrorTitle('Invalid value');
    $v->setError("Please pick a $title from the list.");
    $v->setFormula1($formula);
    return $v;
}
$sheet->setDataValidation('B2:B' . TEMPLATE_ROWS, list_validation('Lists!$A$1:$A$' . count($sections), 'section'));
$sheet->setDataValidation('E2:E' . TEMPLATE_ROWS, list_validation('Lists!$B$1:$B$2', 'status'));

// ===== HOW TO USE SHEET =====
$help = $spreadsheet->createSheet();
$help->setTitle('How to use');
$lines = [
    ['How to update the TNA "Training Required" options with this file'],
    [''],
    ['Add an option', 'Add a new row. Leave ID blank. Pick the Section, type the Training Name, Status = Active.'],
    ['Rename an option', 'Change the Training Name on its row. Keep the ID. Saved TNAs that use the old name are updated to the new name.'],
    ['Hide / show an option', 'Set Status to Hidden or Active. Hidden options cannot be picked for new TNAs; staff who already chose one keep it.'],
    ['Move to another group', 'Change the Group. A group name that does not exist yet in that section is created.'],
    ['Change the order', 'Move rows up or down. Within each section, the row order becomes the dropdown order (ungrouped options always come first, then groups in the order they first appear).'],
    ['Remove an option', 'Set Status to Hidden. Deleting a row from this file does NOT delete the option - it is just left as it is. Unused options can be deleted on the Training Options page.'],
    [''],
    ['Rules', 'Do not change or copy IDs. Do not add OTHERS - it is added automatically. Names cannot contain double quotes, backslashes or < >. Names are saved in UPPERCASE.'],
    ['', 'An option cannot be moved to another section - hide it and add a new row in the other section instead.'],
    ['', 'The import shows a preview of every change first. Nothing is saved until you confirm, and if anything fails nothing is saved.'],
];
$help->fromArray($lines, null, 'A1');
$help->getStyle('A1')->getFont()->setBold(true)->setSize(13);
$help->getStyle('A3:A12')->getFont()->setBold(true);
$help->getColumnDimension('A')->setWidth(24);
$help->getColumnDimension('B')->setWidth(130);
$help->getStyle('B3:B12')->getAlignment()->setWrapText(true);

$spreadsheet->setActiveSheetIndex(0);

$filename = 'tna_training_options_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
