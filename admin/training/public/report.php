<?php

include "../../../dbconn.php";

require '../../../asset/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filterStartDate = isset($_GET['startdate']) ? trim($_GET['startdate']) : '';
$filterEndDate = isset($_GET['enddate']) ? trim($_GET['enddate']) : '';

if (
    $filterStartDate !== ''
    && $filterEndDate !== ''
    && strtotime($filterStartDate) !== false
    && strtotime($filterEndDate) !== false
) {
    $reportStartDate = date('Y-m-d', strtotime($filterStartDate));
    $reportEndDate = date('Y-m-d', strtotime($filterEndDate));
} else {
    $reportStartDate = date('Y-01-01');
    $reportEndDate = date('Y-12-31');
}

if ($reportStartDate > $reportEndDate) {
    [$reportStartDate, $reportEndDate] = [$reportEndDate, $reportStartDate];
}

$monthLabels = [
    1 => 'JANUARY',
    2 => 'FEBRUARY',
    3 => 'MARCH',
    4 => 'APRIL',
    5 => 'MAY',
    6 => 'JUNE',
    7 => 'JULY',
    8 => 'AUGUST',
    9 => 'SEPTEMBER',
    10 => 'OCTOBER',
    11 => 'NOVEMBER',
    12 => 'DECEMBER',
];

function buildMonthPeriods($startDate, $endDate)
{
    global $monthLabels;

    $periods = [];
    $cursor = new DateTime(date('Y-m-01', strtotime($startDate)));
    $endMonth = new DateTime(date('Y-m-01', strtotime($endDate)));

    while ($cursor <= $endMonth) {
        $monthStart = $cursor->format('Y-m-01');
        $monthEnd = $cursor->format('Y-m-t');
        $periodStart = max($startDate, $monthStart);
        $periodEnd = min($endDate, $monthEnd);

        $periods[] = [
            'label' => $monthLabels[(int) $cursor->format('n')] . ' ' . $cursor->format('Y'),
            'start' => $periodStart,
            'end' => $periodEnd,
        ];

        $cursor->modify('+1 month');
    }

    return $periods;
}

function fetchDepartmentMetrics($conn, $startDate, $endDate)
{
    $startDate = mysqli_real_escape_string($conn, $startDate);
    $endDate = mysqli_real_escape_string($conn, $endDate);

    $sql = "
        SELECT
            departments.department,
            COALESCE(manpower.totalman, 0) AS totalman,
            COALESCE(public_hours.publictotalhour, 0) AS publictotalhour,
            COALESCE(ojt_hours.ojttotalhour, 0) AS ojttotalhour
        FROM (
            SELECT department FROM (
                SELECT u.department
                FROM training t
                JOIN participation p ON t.id = p.trainingid
                JOIN user u ON p.userid = u.id
                WHERE t.startdate BETWEEN '$startDate' AND '$endDate'
                  AND p.attendance = 'COMPLETED'
                UNION
                SELECT u.department
                FROM ojt o
                JOIN participateojt po ON o.id = po.ojtid
                JOIN user u ON po.userid = u.id
                WHERE o.startdate BETWEEN '$startDate' AND '$endDate'
                  AND po.attendance = 'COMPLETEDOJT'
            ) department_union
        ) departments
        LEFT JOIN (
            SELECT
                staff_union.department,
                COUNT(DISTINCT staff_union.userid) AS totalman
            FROM (
                SELECT u.department, u.id AS userid
                FROM training t
                JOIN participation p ON t.id = p.trainingid
                JOIN user u ON p.userid = u.id
                WHERE t.startdate BETWEEN '$startDate' AND '$endDate'
                  AND p.attendance = 'COMPLETED'
                UNION
                SELECT u.department, u.id AS userid
                FROM ojt o
                JOIN participateojt po ON o.id = po.ojtid
                JOIN user u ON po.userid = u.id
                WHERE o.startdate BETWEEN '$startDate' AND '$endDate'
                  AND po.attendance = 'COMPLETEDOJT'
            ) staff_union
            GROUP BY staff_union.department
        ) manpower ON departments.department = manpower.department
        LEFT JOIN (
            SELECT
                u.department,
                ROUND(SUM(
                    ((DATEDIFF(t.enddate, t.startdate)) + 1)
                    * ROUND(TIME_TO_SEC(TIMEDIFF(t.endtime, t.starttime)) / 3600, 2)
                ), 2) AS publictotalhour
            FROM training t
            JOIN participation p ON t.id = p.trainingid
            JOIN user u ON p.userid = u.id
            WHERE t.startdate BETWEEN '$startDate' AND '$endDate'
              AND p.attendance = 'COMPLETED'
            GROUP BY u.department
        ) public_hours ON departments.department = public_hours.department
        LEFT JOIN (
            SELECT
                u.department,
                ROUND(SUM(
                    ((DATEDIFF(o.enddate, o.startdate)) + 1)
                    * ROUND(TIME_TO_SEC(TIMEDIFF(o.endtime, o.starttime)) / 3600, 2)
                    * po.totalman
                ), 2) AS ojttotalhour
            FROM ojt o
            JOIN participateojt po ON o.id = po.ojtid
            JOIN user u ON po.userid = u.id
            WHERE o.startdate BETWEEN '$startDate' AND '$endDate'
              AND po.attendance = 'COMPLETEDOJT'
            GROUP BY u.department
        ) ojt_hours ON departments.department = ojt_hours.department
        ORDER BY departments.department
    ";

    $query = mysqli_query($conn, $sql);
    if (!$query) {
        die('Query failed: ' . mysqli_error($conn));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $totalman = (float) $row['totalman'];
        $publicHours = (float) $row['publictotalhour'];
        $ojtHours = (float) $row['ojttotalhour'];

        $rows[$row['department']] = [
            'totalman' => $totalman,
            'publictotalhour' => $publicHours,
            'ojttotalhour' => $ojtHours,
            'totalavghour' => $totalman > 0 ? round(($publicHours + $ojtHours) / $totalman, 2) : 0,
        ];
    }

    return $rows;
}

function applyAvgColor($sheet, $cell, $value)
{
    $color = $value < 4 ? 'FF0000' : '92D050';
    $sheet->getStyle($cell)
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB($color);
}

$periods = buildMonthPeriods($reportStartDate, $reportEndDate);
$periodData = [];
$allDepartments = [];

foreach ($periods as $index => $period) {
    $periodData[$index] = fetchDepartmentMetrics($conn, $period['start'], $period['end']);
    foreach (array_keys($periodData[$index]) as $department) {
        $allDepartments[$department] = true;
    }
}

$departmentList = array_keys($allDepartments);
sort($departmentList);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->getSheetView()->setZoomScale(85);

$sheet->mergeCells('A1:A2')->setCellValue('A1', 'NO');
$sheet->mergeCells('B1:B2')->setCellValue('B1', 'DEPARTMENT');
$sheet->getColumnDimension('A')->setAutoSize(false);
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setAutoSize(true);

$dataStartRow = 3;
$currentRow = $dataStartRow;

foreach ($departmentList as $index => $department) {
    $sheet->setCellValue('A' . $currentRow, $index + 1);
    $sheet->setCellValue('B' . $currentRow, $department);
    $currentRow++;
}

$summaryRow = $currentRow;
$averageRow = $summaryRow + 1;

if (!empty($departmentList)) {
    $sheet->setCellValue('B' . $averageRow, 'Avg TR Hours');
    $sheet->getStyle('B' . $averageRow)->getFont()->setBold(true);
}

foreach ($periods as $index => $period) {
    $startColumnIndex = 3 + ($index * 4);
    $col1 = Coordinate::stringFromColumnIndex($startColumnIndex);
    $col2 = Coordinate::stringFromColumnIndex($startColumnIndex + 1);
    $col3 = Coordinate::stringFromColumnIndex($startColumnIndex + 2);
    $col4 = Coordinate::stringFromColumnIndex($startColumnIndex + 3);

    $sheet->mergeCells($col1 . '1:' . $col4 . '1')->setCellValue($col1 . '1', $period['label']);
    $sheet->setCellValue($col1 . '2', 'MANPOWER');
    $sheet->setCellValue($col2 . '2', 'PUBLIC');
    $sheet->setCellValue($col3 . '2', 'OJT');
    $sheet->setCellValue($col4 . '2', 'AVG TR HRS');

    $sheet->getColumnDimension($col1)->setAutoSize(true);
    $sheet->getColumnDimension($col2)->setAutoSize(true);
    $sheet->getColumnDimension($col3)->setAutoSize(true);
    $sheet->getColumnDimension($col4)->setAutoSize(true);

    $rowPointer = $dataStartRow;
    foreach ($departmentList as $department) {
        $metrics = $periodData[$index][$department] ?? [
            'totalman' => 0,
            'publictotalhour' => 0,
            'ojttotalhour' => 0,
            'totalavghour' => 0,
        ];

        $sheet->setCellValue($col1 . $rowPointer, $metrics['totalman']);
        $sheet->setCellValue($col2 . $rowPointer, $metrics['publictotalhour']);
        $sheet->setCellValue($col3 . $rowPointer, $metrics['ojttotalhour']);
        $sheet->setCellValue($col4 . $rowPointer, $metrics['totalavghour']);
        applyAvgColor($sheet, $col4 . $rowPointer, $metrics['totalavghour']);
        $rowPointer++;
    }

    if (!empty($departmentList)) {
        $sheet->setCellValue($col1 . $summaryRow, '=SUM(' . $col1 . $dataStartRow . ':' . $col1 . ($summaryRow - 1) . ')');
        $sheet->setCellValue($col2 . $summaryRow, '=SUM(' . $col2 . $dataStartRow . ':' . $col2 . ($summaryRow - 1) . ')');
        $sheet->setCellValue($col3 . $summaryRow, '=SUM(' . $col3 . $dataStartRow . ':' . $col3 . ($summaryRow - 1) . ')');
        $sheet->setCellValue($col2 . $averageRow, '=IFERROR(' . $col2 . $summaryRow . '/' . $col1 . $summaryRow . ',0)');
        $sheet->setCellValue($col3 . $averageRow, '=IFERROR(' . $col3 . $summaryRow . '/' . $col1 . $summaryRow . ',0)');

        $sheet->getStyle($col1 . $summaryRow . ':' . $col3 . $summaryRow)->getFont()->setBold(true);
        $sheet->getStyle($col2 . $averageRow . ':' . $col3 . $averageRow)->getFont()->setBold(true);
        $sheet->getStyle($col2 . $averageRow . ':' . $col3 . $averageRow)
            ->getNumberFormat()
            ->setFormatCode('0.00');
        $sheet->getStyle($col1 . '1:' . $col4 . ($summaryRow - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }
}

$lastColumn = Coordinate::stringFromColumnIndex(2 + (count($periods) * 4));
$lastHeaderRow = empty($departmentList) ? 2 : $summaryRow - 1;

if (!empty($departmentList)) {
    $sheet->getStyle('A1:B' . ($summaryRow - 1))
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
}

$sheet->getStyle('C1:' . $lastColumn . '1')->getAlignment()->setHorizontal('center');
$sheet->getStyle('A1:' . $lastColumn . '2')
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('D9D9D9');
$sheet->getStyle('A1:' . $lastColumn . '2')->getFont()->setBold(true);

$filenameSuffix = $reportStartDate . '_to_' . $reportEndDate;
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Training Hours ' . $filenameSuffix . '.xlsx"');
$writer->save('php://output');
exit;
?>
