<?php
    session_start();
    require '../../../asset/vendor/autoload.php';
    include "../../../dbconn.php";
    include_once __DIR__ . '/../../../division_department_section.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;
    use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
    use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
    use PhpOffice\PhpSpreadsheet\NamedRange;
    use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'CLERK') {
        header("Location: ../../../login.php");
        exit();
    }

    $genderOptions = ['MALE', 'FEMALE'];
    $statusOptions = ['ACTIVE', 'RESIGN'];
    $orgStructure = getDbOrgStructure();
    $divisionOptions = array_keys($orgStructure);

    // Pick a real Division/Department/Section combination for the example row,
    // so the row validates against the dropdowns as-is if a user does not
    // touch those columns.
    $exampleDivision = '';
    $exampleDepartment = '';
    $exampleSection = '';
    foreach ($orgStructure as $divName => $departments) {
        if (empty($departments)) continue;
        $exampleDivision = $divName;
        foreach ($departments as $depName => $sections) {
            $exampleDepartment = $depName;
            $exampleSection = !empty($sections) ? $sections[0] : '';
            break;
        }
        break;
    }

    $columns = [
        'A' => 'Staff No',
        'B' => 'Staff Name',
        'C' => 'Gender',
        'D' => 'Division',
        'E' => 'Department',
        'F' => 'Section',
        'G' => 'Status (ACTIVE / RESIGN)',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('New Staff');
    $sheet->fromArray(array_values($columns), null, 'A1');

    // Single example row. Its Staff No is prefixed "(EXAMPLE)" - import_new_staff.php
    // recognises that prefix and skips the row automatically, so leaving it in
    // the uploaded file is harmless.
    $exampleRow = ['(EXAMPLE) A9999', 'AHMAD BIN ALI', 'MALE', $exampleDivision, $exampleDepartment, $exampleSection, 'ACTIVE'];
    $col = 'A';
    foreach ($exampleRow as $value) {
        $sheet->setCellValueExplicit("{$col}2", $value, DataType::TYPE_STRING);
        $col++;
    }
    $sheet->getStyle('A2:G2')->getFont()->setItalic(true)->getColor()->setRGB('808080');

    // Enough rows below the example for real data plus headroom for the dropdown validation.
    $lastRow = 501;

    foreach (array_keys($columns) as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }
    $sheet->getStyle('A1:G1')->getFont()->setBold(true);
    $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');
    $sheet->freezePane('A2');

    // ===== OPTIONS SHEET (dropdown validation sources) =====
    $optionsSheet = $spreadsheet->createSheet();
    $optionsSheet->setTitle('Options');
    $optionsSheet->fromArray(['Gender', 'Division', 'Status'], null, 'A1');
    $optionLists = [
        'A' => $genderOptions,
        'B' => $divisionOptions,
        'C' => $statusOptions,
    ];
    foreach ($optionLists as $c => $values) {
        $r = 2;
        foreach ($values as $value) {
            $optionsSheet->setCellValue("{$c}{$r}", $value);
            $r++;
        }
    }
    foreach (array_keys($optionLists) as $c) {
        $optionsSheet->getColumnDimension($c)->setAutoSize(true);
    }
    $optionsSheet->getStyle('A1:C1')->getFont()->setBold(true);

    // ===== ORG REFERENCE SHEET (valid Division / Department / Section combinations) =====
    $orgSheet = $spreadsheet->createSheet();
    $orgSheet->setTitle('Division-Department-Section');
    $orgSheet->fromArray(['Division', 'Department', 'Section'], null, 'A1');
    $orgRow = 2;
    foreach ($orgStructure as $divisionName => $departments) {
        foreach ($departments as $departmentName => $sections) {
            if (empty($sections)) {
                $orgSheet->setCellValue("A{$orgRow}", $divisionName);
                $orgSheet->setCellValue("B{$orgRow}", $departmentName);
                $orgSheet->setCellValue("C{$orgRow}", '');
                $orgRow++;
                continue;
            }
            foreach ($sections as $sectionName) {
                $orgSheet->setCellValue("A{$orgRow}", $divisionName);
                $orgSheet->setCellValue("B{$orgRow}", $departmentName);
                $orgSheet->setCellValue("C{$orgRow}", $sectionName);
                $orgRow++;
            }
        }
    }
    foreach (['A', 'B', 'C'] as $c) {
        $orgSheet->getColumnDimension($c)->setAutoSize(true);
    }
    $orgSheet->getStyle('A1:C1')->getFont()->setBold(true);

    // ===== CASCADING LOOKUP HELPER SHEETS (Division -> Department -> Section) =====
    $departmentsByDivisionIndex = [];
    $deptFlatList = [];
    $sectionsByDeptFlatIndex = [];

    foreach ($divisionOptions as $divIdx0 => $divisionName) {
        $divIdx = $divIdx0 + 1;
        $departmentNames = array_keys($orgStructure[$divisionName]);
        $departmentsByDivisionIndex[$divIdx] = $departmentNames;

        foreach ($departmentNames as $departmentName) {
            $deptFlatList[] = $departmentName;
            $flatIdx = count($deptFlatList);
            $sections = $orgStructure[$divisionName][$departmentName];
            $sectionsByDeptFlatIndex[$flatIdx] = !empty($sections) ? $sections : ['-'];
        }
    }

    $deptListsSheet = $spreadsheet->createSheet();
    $deptListsSheet->setTitle('Dept Lists');
    foreach ($departmentsByDivisionIndex as $divIdx => $departmentNames) {
        $c = Coordinate::stringFromColumnIndex($divIdx);
        $deptListsSheet->setCellValue("{$c}1", $divisionOptions[$divIdx - 1]);
        $r = 2;
        foreach ($departmentNames as $departmentName) {
            $deptListsSheet->setCellValue("{$c}{$r}", $departmentName);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange("DEPT_{$divIdx}", $deptListsSheet, "\${$c}\$2:\${$c}\$" . ($r - 1)));
    }
    $deptFlatCol = Coordinate::stringFromColumnIndex(count($divisionOptions) + 2);
    $deptListsSheet->setCellValue("{$deptFlatCol}1", 'All Departments (flat order)');
    $r = 2;
    foreach ($deptFlatList as $departmentName) {
        $deptListsSheet->setCellValue("{$deptFlatCol}{$r}", $departmentName);
        $r++;
    }
    $spreadsheet->addNamedRange(new NamedRange('DEPTFLAT', $deptListsSheet, "\${$deptFlatCol}\$2:\${$deptFlatCol}\$" . ($r - 1)));
    $deptListsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

    $secListsSheet = $spreadsheet->createSheet();
    $secListsSheet->setTitle('Section Lists');
    foreach ($sectionsByDeptFlatIndex as $flatIdx => $sectionNames) {
        $c = Coordinate::stringFromColumnIndex($flatIdx);
        $secListsSheet->setCellValue("{$c}1", $deptFlatList[$flatIdx - 1]);
        $r = 2;
        foreach ($sectionNames as $sectionName) {
            $secListsSheet->setCellValue("{$c}{$r}", $sectionName);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange("SEC_{$flatIdx}", $secListsSheet, "\${$c}\$2:\${$c}\$" . ($r - 1)));
    }
    $secListsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

    $spreadsheet->addNamedRange(new NamedRange('DIVLIST', $optionsSheet, '$B$2:$B$' . (count($divisionOptions) + 1)));

    // ===== DROPDOWN VALIDATION on New Staff sheet =====
    $applyListValidation = function ($colLetter, $optionCol, $count) use ($sheet, $lastRow) {
        if ($count < 1) return;
        $validation = $sheet->getCell("{$colLetter}2")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid Value');
        $validation->setError('Please select a value from the dropdown list.');
        $validation->setFormula1("'Options'!\${$optionCol}\$2:\${$optionCol}\$" . ($count + 1));

        for ($r = 2; $r <= $lastRow; $r++) {
            $sheet->getCell("{$colLetter}{$r}")->setDataValidation(clone $validation);
        }
    };
    $applyListValidation('C', 'A', count($genderOptions));
    $applyListValidation('D', 'B', count($divisionOptions));
    $applyListValidation('G', 'C', count($statusOptions));

    // Department (E) and Section (F) dropdowns cascade off the Division/Department picked
    // in the same row, so each row needs its own row-relative formula (can't be cloned).
    for ($r = 2; $r <= $lastRow; $r++) {
        $deptValidation = $sheet->getCell("E{$r}")->getDataValidation();
        $deptValidation->setType(DataValidation::TYPE_LIST);
        $deptValidation->setErrorStyle(DataValidation::STYLE_STOP);
        $deptValidation->setAllowBlank(true);
        $deptValidation->setShowDropDown(true);
        $deptValidation->setShowErrorMessage(true);
        $deptValidation->setErrorTitle('Invalid Value');
        $deptValidation->setError('Please select a Division first, then pick a Department from its dropdown.');
        $deptValidation->setFormula1("INDIRECT(\"DEPT_\"&MATCH(\$D{$r},DIVLIST,0))");

        $secValidation = $sheet->getCell("F{$r}")->getDataValidation();
        $secValidation->setType(DataValidation::TYPE_LIST);
        $secValidation->setErrorStyle(DataValidation::STYLE_STOP);
        $secValidation->setAllowBlank(true);
        $secValidation->setShowDropDown(true);
        $secValidation->setShowErrorMessage(true);
        $secValidation->setErrorTitle('Invalid Value');
        $secValidation->setError('Please select a Department first, then pick a Section from its dropdown.');
        $secValidation->setFormula1("INDIRECT(\"SEC_\"&MATCH(\$E{$r},DEPTFLAT,0))");
    }

    // ===== INSTRUCTIONS SHEET =====
    $instructionsSheet = $spreadsheet->createSheet();
    $instructionsSheet->setTitle('Instructions');
    $instructions = [
        ['HOW TO FILL THIS TEMPLATE'],
        [''],
        ['1. One row = one new staff member. Every new account is created as CONTRACT staff,'],
        ['   the same as adding one staff at a time via the "Add Staff" button.'],
        [''],
        ['2. Staff No (column A) must not already exist in the system - duplicates are rejected.'],
        [''],
        ['3. Staff Name, Gender, Division, Department, Section and Status are all required for'],
        ['   every row. Use the dropdowns provided in each cell - picking a Division narrows the'],
        ['   Department dropdown to that division, and picking a Department narrows the Section'],
        ['   dropdown to that department. Leave Section blank only for a department that has no'],
        ['   sections (see the "Division-Department-Section" sheet for valid combinations).'],
        [''],
        ['4. Status: ACTIVE or RESIGN.'],
        [''],
        ['5. New accounts are created with the standard default password, the same one used when'],
        ['   adding a single staff member from this page.'],
        [''],
        ['6. The grey example row on the "New Staff" sheet is recognised automatically (its Staff'],
        ['   No starts with "(EXAMPLE)") and is skipped on import - you may delete it or leave it.'],
        [''],
        ['7. Nothing is saved unless every row in the file is valid. Fix every reported row and'],
        ['   re-upload the whole file.'],
    ];
    $instructionsSheet->fromArray($instructions, null, 'A1');
    $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $instructionsSheet->getColumnDimension('A')->setWidth(100);

    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'new_staff_import_template_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
?>
