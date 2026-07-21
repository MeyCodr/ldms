<?php
session_start();
include "../../dbconn.php";

require '../../asset/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: ../../login.php");
    exit();
}

$department = isset($_GET['department']) ? $_GET['department'] : 'ALL';
$currentYear = (int) date('Y');
$currentQuarter = (int) ceil(date('n') / 3);
$targetPercentage = 75;
$staffRows = array();
$topicColumns = array();
$topicTotals = array();
$topicCounts = array();

$staffSql = "SELECT
                    sme.id AS evaluation_id,
                    u.id AS staff_id,
                    u.staffno,
                    u.staffname,
                    u.designation,
                    u.grade,
                    creator.staffname AS evaluated_by,
                    verifier.staffname AS verified_by,
                    approver.staffname AS approved_by,
                    COALESCE(dp.name, u.department) AS department,
                    COALESCE(s.name, u.section) AS section
                FROM skill_matrix_evaluations sme
                INNER JOIN user u ON u.id = sme.staffid
                LEFT JOIN user creator ON creator.id = sme.created_by
                LEFT JOIN user verifier ON verifier.id = creator.hodid
                LEFT JOIN user approver ON approver.id = sme.approved_by
                LEFT JOIN departments dp ON u.department_id = dp.id
                LEFT JOIN sections s ON u.section_id = s.id
                WHERE YEAR(sme.evaluation_date) = ?
                AND QUARTER(sme.evaluation_date) = ?
                AND sme.approval_status = 'PENDING'
                ";

if ($department != '' && $department != 'ALL') {
    $staffSql .= "AND (dp.name = ? OR u.department = ?) ";
}

$staffSql .= "ORDER BY department, u.staffname";
$stmt = $conn->prepare($staffSql);

if ($department != '' && $department != 'ALL') {
    $stmt->bind_param("iiss", $currentYear, $currentQuarter, $department, $department);
} else {
    $stmt->bind_param("ii", $currentYear, $currentQuarter);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $staffRows[$row['evaluation_id']] = array(
        'staffno' => $row['staffno'],
        'staffname' => $row['staffname'],
        'designation_grade' => $row['designation'] . ' / ' . $row['grade'],
        'evaluated_by' => $row['evaluated_by'],
        'verified_by' => $row['verified_by'],
        'approved_by' => $row['approved_by'],
        'scores' => array(),
        'overall_total' => 0,
        'overall_count' => 0
    );
}

if (count($staffRows) > 0) {
    $evaluationIds = array_keys($staffRows);
    $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
    $types = str_repeat('i', count($evaluationIds));
    $topicSql = "SELECT
                    t.evaluation_id,
                    t.section_type,
                    t.topic_name,
                    MIN(t.sort_order) AS topic_sort_order,
                    SUM(i.rating) AS total_rating,
                    COUNT(i.id) AS item_count
                FROM skill_matrix_topics t
                INNER JOIN skill_matrix_items i ON i.topic_id = t.id
                WHERE t.evaluation_id IN ($placeholders)
                GROUP BY t.evaluation_id, t.section_type, t.topic_name
                ORDER BY FIELD(t.section_type, 'knowledge', 'skill', 'ability'), topic_sort_order";
    $topicStmt = $conn->prepare($topicSql);
    $topicStmt->bind_param($types, ...$evaluationIds);
    $topicStmt->execute();
    $topicResult = $topicStmt->get_result();

    while ($topicRow = $topicResult->fetch_assoc()) {
        $topicKey = $topicRow['section_type'] . '|' . $topicRow['topic_name'];
        $percentage = $topicRow['item_count'] > 0 ? ($topicRow['total_rating'] / ($topicRow['item_count'] * 5)) * 100 : 0;

        if (!isset($topicColumns[$topicKey])) {
            $topicColumns[$topicKey] = array(
                'section_type' => $topicRow['section_type'],
                'topic_name' => $topicRow['topic_name']
            );
            $topicTotals[$topicKey] = 0;
            $topicCounts[$topicKey] = 0;
        }

        $staffRows[$topicRow['evaluation_id']]['scores'][$topicKey] = $percentage;
        $staffRows[$topicRow['evaluation_id']]['overall_total'] += $percentage;
        $staffRows[$topicRow['evaluation_id']]['overall_count']++;
        $topicTotals[$topicKey] += $percentage;
        $topicCounts[$topicKey]++;
    }
}

$evaluatedByNames = array();
$verifiedByNames = array();
$approvedByNames = array();
foreach ($staffRows as $staffRow) {
    if (!empty($staffRow['evaluated_by'])) {
        $evaluatedByNames[$staffRow['evaluated_by']] = true;
    }
    if (!empty($staffRow['verified_by'])) {
        $verifiedByNames[$staffRow['verified_by']] = true;
    }
    if (!empty($staffRow['approved_by'])) {
        $approvedByNames[$staffRow['approved_by']] = true;
    }
}
$reportEvaluatedBy = implode(', ', array_keys($evaluatedByNames));
$reportVerifiedBy = implode(', ', array_keys($verifiedByNames));
$reportApprovedBy = implode(', ', array_keys($approvedByNames));

$fixedColumnCount = 4;
$topicCount = count($topicColumns);
$lastColumnIndex = $fixedColumnCount + $topicCount + 1;
$lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Skill Matrix Report');
$sheet->getSheetView()->setZoomScale(100);

$titleText = 'Skill Matrix Report - Q' . $currentQuarter . ' ' . $currentYear;
if ($department != '' && $department != 'ALL') {
    $titleText .= ' - ' . $department;
}
$sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', $titleText);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$headerRow1 = 3;
$headerRow2 = 4;
$headerRow3 = 5;
$dataStartRow = 6;

if ($topicCount > 0) {
    $abilityStartCol = Coordinate::stringFromColumnIndex($fixedColumnCount + 1);
    $abilityEndCol = Coordinate::stringFromColumnIndex($fixedColumnCount + $topicCount);
    $sheet->mergeCells($abilityStartCol . $headerRow1 . ':' . $abilityEndCol . $headerRow1)
        ->setCellValue($abilityStartCol . $headerRow1, 'ABILITY DESCRIPTION');
}
$totalColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
$sheet->setCellValue($totalColumn . $headerRow1, 'TOTAL');
$sheet->mergeCells('A' . $headerRow1 . ':D' . $headerRow1)->setCellValue('A' . $headerRow1, '');

$sheet->mergeCells('A' . $headerRow2 . ':D' . $headerRow2)->setCellValue('A' . $headerRow2, 'No.');
$columnNo = 1;
foreach ($topicColumns as $topic) {
    $col = Coordinate::stringFromColumnIndex($fixedColumnCount + $columnNo);
    $sheet->setCellValue($col . $headerRow2, $columnNo);
    $columnNo++;
}

$sheet->setCellValue('A' . $headerRow3, 'NO.');
$sheet->setCellValue('B' . $headerRow3, 'EMP. NO');
$sheet->setCellValue('C' . $headerRow3, 'NAME');
$sheet->setCellValue('D' . $headerRow3, 'DESIGNATION / GRADE');
$columnIndex = $fixedColumnCount + 1;
foreach ($topicColumns as $topic) {
    $col = Coordinate::stringFromColumnIndex($columnIndex);
    $sheet->setCellValue($col . $headerRow3, $topic['topic_name']);
    $columnIndex++;
}
$sheet->setCellValue($totalColumn . $headerRow3, 'AVERAGE');

$sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $headerRow3)->getFont()->setBold(true);
$sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $headerRow3)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER)
    ->setWrapText(true);
$sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $headerRow3)
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('D9EDF7');

$rowPointer = $dataStartRow;
$rowNo = 1;
foreach ($staffRows as $staffRow) {
    $overallAverage = $staffRow['overall_count'] > 0 ? $staffRow['overall_total'] / $staffRow['overall_count'] : 0;

    $sheet->setCellValue('A' . $rowPointer, $rowNo);
    $sheet->setCellValue('B' . $rowPointer, $staffRow['staffno']);
    $sheet->setCellValue('C' . $rowPointer, $staffRow['staffname']);
    $sheet->setCellValue('D' . $rowPointer, $staffRow['designation_grade']);

    $columnIndex = $fixedColumnCount + 1;
    foreach ($topicColumns as $topicKey => $topic) {
        $score = isset($staffRow['scores'][$topicKey]) ? $staffRow['scores'][$topicKey] : 0;
        $col = Coordinate::stringFromColumnIndex($columnIndex);
        $sheet->setCellValue($col . $rowPointer, round($score) . '%');
        $columnIndex++;
    }

    $sheet->setCellValue($totalColumn . $rowPointer, number_format($overallAverage, 2) . '%');
    $sheet->getStyle($totalColumn . $rowPointer)->getFont()->setBold(true);

    $rowPointer++;
    $rowNo++;
}

$summaryRow = $rowPointer;
$targetRow = $summaryRow + 1;

$sheet->mergeCells('A' . $summaryRow . ':D' . $summaryRow)->setCellValue('A' . $summaryRow, 'AVERAGE');
$sheet->mergeCells('A' . $targetRow . ':D' . $targetRow)->setCellValue('A' . $targetRow, 'TARGET');

$overallTopicAverageTotal = 0;
$overallTopicAverageCount = 0;
$columnIndex = $fixedColumnCount + 1;
foreach ($topicColumns as $topicKey => $topic) {
    $topicAverage = $topicCounts[$topicKey] > 0 ? $topicTotals[$topicKey] / $topicCounts[$topicKey] : 0;
    $overallTopicAverageTotal += $topicAverage;
    $overallTopicAverageCount++;

    $col = Coordinate::stringFromColumnIndex($columnIndex);
    $sheet->setCellValue($col . $summaryRow, round($topicAverage) . '%');
    $sheet->setCellValue($col . $targetRow, $targetPercentage . '%');
    $columnIndex++;
}
$overallAverageValue = $overallTopicAverageCount > 0 ? $overallTopicAverageTotal / $overallTopicAverageCount : 0;
$sheet->setCellValue($totalColumn . $summaryRow, number_format($overallAverageValue, 2) . '%');
$sheet->setCellValue($totalColumn . $targetRow, $targetPercentage . '%');

$sheet->getStyle('A' . $summaryRow . ':' . $lastColumn . $summaryRow)
    ->getFont()->setBold(true);
$sheet->getStyle('A' . $summaryRow . ':' . $lastColumn . $summaryRow)
    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9EDF7');
$sheet->getStyle('A' . $targetRow . ':' . $lastColumn . $targetRow)
    ->getFont()->setBold(true);
$sheet->getStyle('A' . $targetRow . ':' . $lastColumn . $targetRow)
    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FCF8E3');

$lastDataRow = $targetRow;
$sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $lastDataRow)
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('A' . $dataStartRow . ':' . $lastColumn . $lastDataRow)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('C' . $dataStartRow . ':C' . $summaryRow)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

foreach (range(1, $lastColumnIndex) as $columnIndex) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
}
$sheet->getColumnDimension('C')->setWidth(28);
$sheet->getColumnDimension('D')->setWidth(22);

$signOffHeaderRow = $lastDataRow + 3;
$signOffValueRow = $signOffHeaderRow + 1;

$sheet->setCellValue('C' . $signOffHeaderRow, 'EVALUATED BY');
$sheet->setCellValue('D' . $signOffHeaderRow, 'VERIFIED BY');
$sheet->setCellValue('E' . $signOffHeaderRow, 'APPROVED BY');
$sheet->setCellValue('C' . $signOffValueRow, $reportEvaluatedBy);
$sheet->setCellValue('D' . $signOffValueRow, $reportVerifiedBy);
$sheet->setCellValue('E' . $signOffValueRow, $reportApprovedBy);

$sheet->getStyle('C' . $signOffHeaderRow . ':E' . $signOffHeaderRow)->getFont()->setBold(true);
$sheet->getStyle('C' . $signOffHeaderRow . ':E' . $signOffHeaderRow)
    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9EDF7');
$sheet->getStyle('C' . $signOffHeaderRow . ':E' . $signOffValueRow)
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('C' . $signOffHeaderRow . ':E' . $signOffValueRow)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER)
    ->setWrapText(true);

$filenameSuffix = 'Q' . $currentQuarter . '_' . $currentYear;
if ($department != '' && $department != 'ALL') {
    $filenameSuffix .= '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $department);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Skill Matrix Report ' . $filenameSuffix . '.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
?>
