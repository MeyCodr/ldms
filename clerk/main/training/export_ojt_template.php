<?php
    session_start();
    require '../../../asset/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;
    use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'CLERK' || $_SESSION['usertype'] != 'MAIN') {
        header("Location: ../../../login.php");
        exit();
    }

    $trainerTypeOptions = ['INTERNAL', 'EXTERNAL'];

    $columns = [
        'A' => 'Title',
        'B' => 'Venue',
        'C' => 'Start Date (YYYY-MM-DD)',
        'D' => 'End Date (YYYY-MM-DD)',
        'E' => 'Start Time (HH:MM)',
        'F' => 'End Time (HH:MM)',
        'G' => 'Trainer Type',
        'H' => 'Trainer Name',
        'I' => 'Participant Staff No',
        'J' => 'What Did You Learn (optional)',
        'K' => 'Skill Before Training 1-5 (optional)',
        'L' => 'Skill After Training 1-5 (optional)',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('OJT Import');
    $sheet->fromArray(array_values($columns), null, 'A1');

    // Two example rows showing one training with two participants: every
    // column repeats except Participant Staff No, which is what varies.
    // A0001 shows the feedback columns filled in (imports as already
    // completed); A0002 leaves them blank (imports as pending, same as
    // before this feature existed - that participant fills in their own
    // attendance form later).
    $exampleRows = [
        ['(EXAMPLE) SAFETY BRIEFING - LINE 1', 'ASSEMBLY LINE 1', '2026-01-05', '2026-01-05', '08:00', '09:00', 'INTERNAL', 'AHMAD BIN ALI', 'A0001', 'Lockout-tagout procedure for Line 1', '2', '4'],
        ['(EXAMPLE) SAFETY BRIEFING - LINE 1', 'ASSEMBLY LINE 1', '2026-01-05', '2026-01-05', '08:00', '09:00', 'INTERNAL', 'AHMAD BIN ALI', 'A0002', '', '', ''],
    ];
    $row = 2;
    foreach ($exampleRows as $exampleRow) {
        $col = 'A';
        foreach ($exampleRow as $value) {
            $sheet->setCellValueExplicit("{$col}{$row}", $value, DataType::TYPE_STRING);
            $col++;
        }
        $row++;
    }
    $lastExampleRow = $row - 1;
    $sheet->getStyle("A2:L{$lastExampleRow}")->getFont()->setItalic(true)->getColor()->setRGB('808080');

    // Enough rows below the examples for real data plus headroom for the dropdown validation.
    $lastRow = $lastExampleRow + 500;

    foreach (array_keys($columns) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getStyle('A1:L1')->getFont()->setBold(true);
    $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');
    $sheet->getStyle("C2:C{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
    $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
    $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('hh:mm');
    $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('hh:mm');
    $sheet->freezePane('A2');

    // ===== OPTIONS SHEET (dropdown validation source) =====
    $optionsSheet = $spreadsheet->createSheet();
    $optionsSheet->setTitle('Options');
    $optionsSheet->fromArray(['Trainer Type'], null, 'A1');
    $r = 2;
    foreach ($trainerTypeOptions as $value) {
        $optionsSheet->setCellValue("A{$r}", $value);
        $r++;
    }
    $optionsSheet->getColumnDimension('A')->setAutoSize(true);
    $optionsSheet->getStyle('A1')->getFont()->setBold(true);

    $validation = $sheet->getCell('G2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_STOP);
    $validation->setAllowBlank(false);
    $validation->setShowDropDown(true);
    $validation->setShowErrorMessage(true);
    $validation->setErrorTitle('Invalid Value');
    $validation->setError('Please select INTERNAL or EXTERNAL from the dropdown list.');
    $validation->setFormula1('\'Options\'!$A$2:$A$' . (count($trainerTypeOptions) + 1));
    for ($vr = 2; $vr <= $lastRow; $vr++) {
        $sheet->getCell("G{$vr}")->setDataValidation(clone $validation);
    }

    // K/L (Skill Before/After) are optional, but if a value is entered it
    // must be a whole number 1-5, matching the rating scale on the staff's
    // own attendance form (attend_ojt.php).
    $skillValidation = new DataValidation();
    $skillValidation->setType(DataValidation::TYPE_WHOLE);
    $skillValidation->setErrorStyle(DataValidation::STYLE_STOP);
    $skillValidation->setAllowBlank(true);
    $skillValidation->setShowErrorMessage(true);
    $skillValidation->setErrorTitle('Invalid Value');
    $skillValidation->setError('Enter a whole number from 1 to 5, or leave blank.');
    $skillValidation->setOperator(DataValidation::OPERATOR_BETWEEN);
    $skillValidation->setFormula1('1');
    $skillValidation->setFormula2('5');
    foreach (['K', 'L'] as $skillCol) {
        for ($vr = 2; $vr <= $lastRow; $vr++) {
            $sheet->getCell("{$skillCol}{$vr}")->setDataValidation(clone $skillValidation);
        }
    }

    // ===== INSTRUCTIONS SHEET =====
    $instructionsSheet = $spreadsheet->createSheet();
    $instructionsSheet->setTitle('Instructions');
    $instructions = [
        ['HOW TO FILL THIS TEMPLATE'],
        [''],
        ['1. One row = one participant, not one training.'],
        ['   To add a training with several participants, repeat the SAME Title, Venue, Start Date, End Date,'],
        ['   Start Time, End Time, Trainer Type and Trainer Name on one row per participant - only the'],
        ['   Participant Staff No changes between those rows. See the two grey example rows on the'],
        ['   "OJT Import" sheet: they are the SAME training with two different participants.'],
        [''],
        ['2. Title must be entered exactly the same (same spelling/case) on every row that belongs to the'],
        ['   same training, or the rows will be imported as separate trainings.'],
        [''],
        ['3. Start Date / End Date: format YYYY-MM-DD, e.g. 2026-01-05.'],
        [''],
        ['4. Start Time / End Time: 24-hour format HH:MM, e.g. 08:00 or 17:30.'],
        [''],
        ['5. Trainer Type: choose INTERNAL or EXTERNAL from the dropdown on the "OJT Import" sheet.'],
        [''],
        ['6. Trainer Name: the trainer\'s name as text (used for both INTERNAL and EXTERNAL trainers).'],
        [''],
        ['7. Participant Staff No must match an existing Staff No already in the system exactly.'],
        ['   Rows with a Staff No that cannot be found will be rejected and nothing in the file will be'],
        ['   imported until every row is fixed - fix the reported rows and re-upload the whole file.'],
        [''],
        ['8. What Did You Learn / Skill Before Training / Skill After Training (columns J-L) are OPTIONAL.'],
        ['   Leave all three blank if the participant has not done the OJT feedback yet - they will show up'],
        ['   with "Fill In Attendance" in their own My Training list, same as before.'],
        ['   Fill in all three to import that participant as already COMPLETED, with their feedback recorded -'],
        ['   this is meant for OJT that already happened and was recorded on paper/elsewhere beforehand.'],
        ['   Skill Before/After must each be a whole number 1-5 (1 = Poor, 5 = Excellent). You must fill in'],
        ['   all three columns together or leave all three blank - filling only one or two is rejected.'],
        [''],
        ['9. Delete the two grey example rows (or leave them - rows whose Title starts with "(EXAMPLE)"'],
        ['   will still be imported as a real training, so it is best to delete them before uploading).'],
    ];
    $instructionsSheet->fromArray($instructions, null, 'A1');
    $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $instructionsSheet->getColumnDimension('A')->setWidth(100);

    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'ojt_import_template_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
?>
