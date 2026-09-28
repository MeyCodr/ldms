<?php
// Shared Excel export for the skill matrix "Matrix Chart" page (admin, clerk, HOD, office).
// Each role's matrix-chart.php builds $staffRows / $topicColumns with its own access
// scope, then calls outputMatrixChartExcel() so the file mirrors the on-screen table.

require_once __DIR__ . '/asset/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function matrixChartExcelPieLevel($score)
{
    if ($score >= 100) {
        return 100;
    }

    if ($score >= 75) {
        return 75;
    }

    if ($score >= 50) {
        return 50;
    }

    if ($score >= 25) {
        return 25;
    }

    return 0;
}

/**
 * Streams the matrix chart as an .xlsx download and exits.
 *
 * $filterParts: extra "Label: value" strings shown after the quarter line (same as the page's info bar).
 * $filenameParts: values appended to the download filename.
 */
function outputMatrixChartExcel($staffRows, $topicColumns, $matrixFixedLevels, $currentQuarter, $currentYear, $filterParts, $filenameParts, $reportEvaluatedBy, $reportVerifiedBy, $reportApprovedBy)
{
    // Unicode circles standing in for the CSS pie charts on the page
    $matrixPieSymbols = array(
        100 => "\u{25CF}",
        75 => "\u{25D5}",
        50 => "\u{25D1}",
        25 => "\u{25D4}",
        0 => "\u{25CB}"
    );

    $fixedColumnCount = 4;
    $levelCount = count($matrixFixedLevels);
    $totalColumnIndex = $fixedColumnCount + $levelCount + 1;
    $lastColumn = Coordinate::stringFromColumnIndex($totalColumnIndex);
    $totalColumn = $lastColumn;
    $levelStartCol = Coordinate::stringFromColumnIndex($fixedColumnCount + 1);
    $levelEndCol = Coordinate::stringFromColumnIndex($fixedColumnCount + $levelCount);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Matrix Chart');
    $sheet->getSheetView()->setZoomScale(100);
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

    $titleText = 'Matrix Chart - Q' . $currentQuarter . ' ' . $currentYear;
    $sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', $titleText);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $filterText = 'Current Quarter: Q' . $currentQuarter . ' ' . $currentYear;
    foreach ($filterParts as $filterPart) {
        $filterText .= '   |   ' . $filterPart;
    }
    $sheet->mergeCells('A2:' . $lastColumn . '2')->setCellValue('A2', $filterText);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setItalic(true)->setSize(11);

    $headerRow1 = 4;
    $headerRow2 = 5;
    $headerRow3 = 6;
    $dataStartRow = 7;

    // Header row 1: blank | ABILITY DESCRIPTION | TOTAL
    $sheet->mergeCells('A' . $headerRow1 . ':D' . $headerRow1);
    $sheet->mergeCells($levelStartCol . $headerRow1 . ':' . $levelEndCol . $headerRow1)
        ->setCellValue($levelStartCol . $headerRow1, 'ABILITY DESCRIPTION');
    $sheet->setCellValue($totalColumn . $headerRow1, 'TOTAL');

    // Header row 2: No. | 1..5 | blank
    $sheet->mergeCells('A' . $headerRow2 . ':D' . $headerRow2)->setCellValue('A' . $headerRow2, 'No.');
    $columnNo = 1;
    foreach ($matrixFixedLevels as $level) {
        $col = Coordinate::stringFromColumnIndex($fixedColumnCount + $columnNo);
        $sheet->setCellValue($col . $headerRow2, $columnNo);
        $columnNo++;
    }

    // Header row 3: column labels
    $sheet->setCellValue('A' . $headerRow3, 'NO.');
    $sheet->setCellValue('B' . $headerRow3, 'EMP. NO');
    $sheet->setCellValue('C' . $headerRow3, 'NAME');
    $sheet->setCellValue('D' . $headerRow3, 'DESIGNATION / GRADE');
    $columnIndex = $fixedColumnCount + 1;
    foreach ($matrixFixedLevels as $levelValue => $level) {
        $col = Coordinate::stringFromColumnIndex($columnIndex);
        $headerText = $level['label'];
        if ($level['sub'] != '') {
            $headerText .= "\n" . $level['sub'];
        }
        $headerText .= "\n(" . $levelValue . '%)';
        $sheet->setCellValue($col . $headerRow3, $headerText);
        $columnIndex++;
    }
    $sheet->setCellValue($totalColumn . $headerRow3, 'AVERAGE');

    $headerRange = 'A' . $headerRow1 . ':' . $lastColumn . $headerRow3;
    $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FF31708F');
    $sheet->getStyle($headerRange)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEF6FB');
    $sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $headerRow1)->getFill()->getStartColor()->setARGB('FF337AB7');
    $sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $headerRow1)->getFont()->getColor()->setARGB('FFFFFFFF');
    $sheet->getRowDimension($headerRow3)->setRowHeight(45);

    $rowPointer = $dataStartRow;
    $rowNo = 1;
    foreach ($staffRows as $staffRow) {
        $overallAverage = $staffRow['overall_count'] > 0 ? $staffRow['overall_total'] / $staffRow['overall_count'] : 0;
        $levelTopics = array(100 => array(), 75 => array(), 50 => array(), 25 => array(), 0 => array());
        foreach ($staffRow['scores'] as $topicKey => $score) {
            $levelTopics[matrixChartExcelPieLevel($score)][] = array(
                'name' => $topicColumns[$topicKey]['topic_name'],
                'score' => $score
            );
        }

        $sheet->setCellValueExplicit('A' . $rowPointer, $rowNo . '.', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $rowPointer, $staffRow['staffno'], DataType::TYPE_STRING);
        $sheet->setCellValue('C' . $rowPointer, $staffRow['staffname']);
        $sheet->setCellValue('D' . $rowPointer, $staffRow['designation_grade']);

        $maxLines = 1;
        $columnIndex = $fixedColumnCount + 1;
        foreach ($matrixFixedLevels as $levelValue => $level) {
            $col = Coordinate::stringFromColumnIndex($columnIndex);

            $richText = new RichText();
            $pieRun = $richText->createTextRun($matrixPieSymbols[$levelValue]);
            $pieRun->getFont()->setName('Segoe UI Symbol')->setSize(20)->setColor(new Color('FF337AB7'));

            foreach ($levelTopics[$levelValue] as $index => $topicInfo) {
                $topicRun = $richText->createTextRun("\n" . ($index + 1) . '. ' . $topicInfo['name'] . '  ' . number_format($topicInfo['score'], 0) . '%');
                $topicRun->getFont()->setName('Arial')->setSize(8)->setBold(true)->setColor(new Color('FF31708F'));
            }

            $sheet->setCellValue($col . $rowPointer, $richText);
            $sheet->getStyle($col . $rowPointer)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setWrapText(true);

            // Rough line estimate so long topic names that wrap still fit in the row
            $lines = 0;
            foreach ($levelTopics[$levelValue] as $topicInfo) {
                $lines += (int) ceil((strlen($topicInfo['name']) + 10) / 30);
            }
            $maxLines = max($maxLines, $lines);
            $columnIndex++;
        }

        $sheet->setCellValue($totalColumn . $rowPointer, number_format($overallAverage, 2) . '%');
        $sheet->getStyle($totalColumn . $rowPointer)->getFont()->setBold(true);
        $sheet->getRowDimension($rowPointer)->setRowHeight(30 + ($maxLines * 12));

        $rowPointer++;
        $rowNo++;
    }

    $lastDataRow = max($rowPointer - 1, $headerRow3);
    $sheet->getStyle('A' . $headerRow1 . ':' . $lastColumn . $lastDataRow)
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFDDDDDD');
    if ($lastDataRow >= $dataStartRow) {
        foreach (array('A', 'B', 'C', 'D', $totalColumn) as $col) {
            $sheet->getStyle($col . $dataStartRow . ':' . $col . $lastDataRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }
        $sheet->getStyle('C' . $dataStartRow . ':C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(22);
    for ($i = $fixedColumnCount + 1; $i <= $fixedColumnCount + $levelCount; $i++) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(32);
    }
    $sheet->getColumnDimension($totalColumn)->setWidth(14);

    // Remarks legend
    $remarksTitleRow = $lastDataRow + 2;
    $remarksRow = $remarksTitleRow + 1;
    $sheet->setCellValue('A' . $remarksTitleRow, 'REMARKS:');
    $sheet->getStyle('A' . $remarksTitleRow)->getFont()->setBold(true);

    $columnIndex = $fixedColumnCount + 1;
    foreach ($matrixFixedLevels as $levelValue => $level) {
        $col = Coordinate::stringFromColumnIndex($columnIndex);
        $richText = new RichText();
        $pieRun = $richText->createTextRun($matrixPieSymbols[$levelValue]);
        $pieRun->getFont()->setName('Segoe UI Symbol')->setSize(24)->setColor(new Color('FF4F81BD'));
        $labelRun = $richText->createTextRun("\n" . $level['label']);
        $labelRun->getFont()->setName('Arial')->setSize(10)->setBold(true);
        if ($level['sub'] != '') {
            $subRun = $richText->createTextRun("\n(" . $level['sub'] . ')');
            $subRun->getFont()->setName('Arial')->setSize(8)->setBold(true);
        }
        $percentRun = $richText->createTextRun("\n" . $levelValue . '%');
        $percentRun->getFont()->setName('Arial')->setSize(10);

        $sheet->setCellValue($col . $remarksRow, $richText);
        $columnIndex++;
    }
    $sheet->getStyle($levelStartCol . $remarksRow . ':' . $levelEndCol . $remarksRow)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_TOP)
        ->setWrapText(true);
    $sheet->getRowDimension($remarksRow)->setRowHeight(80);
    $sheet->getStyle('A' . $remarksTitleRow . ':' . $lastColumn . $remarksRow)
        ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFDDDDDD');

    // Sign-off table
    $signOffHeaderRow = $remarksRow + 2;
    $signOffValueRow = $signOffHeaderRow + 1;
    $signOffBlocks = array(
        array('A', 'C', 'EVALUATED BY', $reportEvaluatedBy),
        array('D', 'F', 'VERIFIED BY', $reportVerifiedBy),
        array('G', $lastColumn, 'APPROVED BY', $reportApprovedBy)
    );
    foreach ($signOffBlocks as $block) {
        $sheet->mergeCells($block[0] . $signOffHeaderRow . ':' . $block[1] . $signOffHeaderRow)->setCellValue($block[0] . $signOffHeaderRow, $block[2]);
        $sheet->mergeCells($block[0] . $signOffValueRow . ':' . $block[1] . $signOffValueRow)->setCellValue($block[0] . $signOffValueRow, $block[3]);
    }
    $signOffRange = 'A' . $signOffHeaderRow . ':' . $lastColumn . $signOffValueRow;
    $sheet->getStyle('A' . $signOffHeaderRow . ':' . $lastColumn . $signOffHeaderRow)->getFont()->setBold(true)->getColor()->setARGB('FF31708F');
    $sheet->getStyle('A' . $signOffHeaderRow . ':' . $lastColumn . $signOffHeaderRow)
        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEF6FB');
    $sheet->getStyle($signOffRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFDDDDDD');
    $sheet->getStyle($signOffRange)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getRowDimension($signOffValueRow)->setRowHeight(30);

    // Print setup: landscape, fit to one page wide, repeat header rows
    $sheet->getPageSetup()
        ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
        ->setPaperSize(PageSetup::PAPERSIZE_A4)
        ->setFitToWidth(1)
        ->setFitToHeight(0)
        ->setRowsToRepeatAtTopByStartAndEnd($headerRow1, $headerRow3);
    $sheet->freezePane('E' . $dataStartRow);

    $filenameSuffix = 'Q' . $currentQuarter . '_' . $currentYear;
    foreach ($filenameParts as $filenamePart) {
        $filenameSuffix .= '_' . trim(preg_replace('/[^A-Za-z0-9]+/', '_', $filenamePart), '_');
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Matrix Chart ' . $filenameSuffix . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}
