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

    $columns = [
        'A' => 'Staff No',
        'B' => 'Staff Name',
        'C' => 'Gender',
        'D' => 'Division',
        'E' => 'Department',
        'F' => 'Section',
        'G' => 'Status (ACTIVE / RESIGN)',
        'H' => 'HOD Staff No (optional override)',
        'I' => 'Current HOD Name (reference only)',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Staff Data');
    $sheet->fromArray(array_values($columns), null, 'A1');

    $row = 2;
    // hod.* is joined so the sheet round-trips: the HOD Staff No column (H)
    // is what the importer reads back, the name beside it (I) is only
    // there so a clerk can see who is currently assigned before deciding
    // whether to change it - re-uploading it unchanged is a no-op.
    $res = $conn->query("SELECT u.staffno, u.staffname, u.gender, u.division, u.department, u.section, u.status,
                                hod.staffno AS hod_staffno, hod.staffname AS hod_staffname
                         FROM user u
                         LEFT JOIN user hod ON hod.id = u.hodid
                         WHERE u.designation = 'CONTRACT' AND u.staffno IS NOT NULL AND u.staffno <> ''
                         ORDER BY u.staffno");
    while ($r = $res->fetch_assoc()) {
        $sheet->setCellValueExplicit("A{$row}", $r['staffno'], DataType::TYPE_STRING);
        $sheet->setCellValue("B{$row}", $r['staffname']);
        $sheet->setCellValueExplicit("C{$row}", $r['gender'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", $r['division'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E{$row}", $r['department'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("F{$row}", $r['section'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("G{$row}", $r['status'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("H{$row}", $r['hod_staffno'] ?: '', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("I{$row}", $r['hod_staffname'] ?: '', DataType::TYPE_STRING);
        $row++;
    }
    $lastRow = $row - 1;

    foreach (array_keys($columns) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E8FF');
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
    foreach ($optionLists as $col => $values) {
        $r = 2;
        foreach ($values as $value) {
            $optionsSheet->setCellValue("{$col}{$r}", $value);
            $r++;
        }
    }
    foreach (array_keys($optionLists) as $col) {
        $optionsSheet->getColumnDimension($col)->setAutoSize(true);
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
    foreach (['A', 'B', 'C'] as $col) {
        $orgSheet->getColumnDimension($col)->setAutoSize(true);
    }
    $orgSheet->getStyle('A1:C1')->getFont()->setBold(true);

    // Department -> current HOD staff no, so the HOD dropdown (column H) can
    // suggest each department's own head without a clerk needing to know
    // their staff number by memory.
    $deptHodStaffNo = [];
    $hodRes = $conn->query(
        "SELECT d.name, hod.staffno
         FROM departments d
         LEFT JOIN user hod ON hod.id = d.hod_user_id"
    );
    while ($hr = $hodRes->fetch_assoc()) {
        $deptHodStaffNo[$hr['name']] = $hr['staffno'] ?: '';
    }

    // ===== CASCADING LOOKUP HELPER SHEETS (Division -> Department -> Section -> HOD) =====
    $departmentsByDivisionIndex = [];
    $deptFlatList = [];
    $sectionsByDeptFlatIndex = [];
    $hodByDeptFlatIndex = [];

    foreach ($divisionOptions as $divIdx0 => $divisionName) {
        $divIdx = $divIdx0 + 1;
        $departmentNames = array_keys($orgStructure[$divisionName]);
        $departmentsByDivisionIndex[$divIdx] = $departmentNames;

        foreach ($departmentNames as $departmentName) {
            $deptFlatList[] = $departmentName;
            $flatIdx = count($deptFlatList);
            $sections = $orgStructure[$divisionName][$departmentName];
            $sectionsByDeptFlatIndex[$flatIdx] = !empty($sections) ? $sections : ['-'];
            $hodByDeptFlatIndex[$flatIdx] = isset($deptHodStaffNo[$departmentName]) ? $deptHodStaffNo[$departmentName] : '';
        }
    }

    $deptListsSheet = $spreadsheet->createSheet();
    $deptListsSheet->setTitle('Dept Lists');
    foreach ($departmentsByDivisionIndex as $divIdx => $departmentNames) {
        $col = Coordinate::stringFromColumnIndex($divIdx);
        $deptListsSheet->setCellValue("{$col}1", $divisionOptions[$divIdx - 1]);
        $r = 2;
        foreach ($departmentNames as $departmentName) {
            $deptListsSheet->setCellValue("{$col}{$r}", $departmentName);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange("DEPT_{$divIdx}", $deptListsSheet, "\${$col}\$2:\${$col}\$" . ($r - 1)));
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
        $col = Coordinate::stringFromColumnIndex($flatIdx);
        $secListsSheet->setCellValue("{$col}1", $deptFlatList[$flatIdx - 1]);
        $r = 2;
        foreach ($sectionNames as $sectionName) {
            $secListsSheet->setCellValue("{$col}{$r}", $sectionName);
            $r++;
        }
        $spreadsheet->addNamedRange(new NamedRange("SEC_{$flatIdx}", $secListsSheet, "\${$col}\$2:\${$col}\$" . ($r - 1)));
    }
    $secListsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

    // One named range per department, holding just that department's current
    // HOD staff no (blank if the department has none assigned yet). The HOD
    // dropdown on column H is a suggestion only, not enforced - a clerk can
    // still type any other real staff no, user id, or name for a deliberate
    // exception, and the import validates it the same way either way.
    $hodListsSheet = $spreadsheet->createSheet();
    $hodListsSheet->setTitle('HOD Lists');
    foreach ($hodByDeptFlatIndex as $flatIdx => $hodStaffNo) {
        $col = Coordinate::stringFromColumnIndex($flatIdx);
        $hodListsSheet->setCellValue("{$col}1", $deptFlatList[$flatIdx - 1]);
        $hodListsSheet->setCellValueExplicit("{$col}2", $hodStaffNo, DataType::TYPE_STRING);
        $spreadsheet->addNamedRange(new NamedRange("HOD_{$flatIdx}", $hodListsSheet, "\${$col}\$2:\${$col}\$2"));
    }
    $hodListsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

    $spreadsheet->addNamedRange(new NamedRange('DIVLIST', $optionsSheet, '$B$2:$B$' . (count($divisionOptions) + 1)));

    // ===== DROPDOWN VALIDATION on Staff Data sheet =====
    $applyListValidation = function ($colLetter, $optionCol, $count) use ($sheet, $lastRow) {
        if ($lastRow < 2 || $count < 1) return;
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

        // HOD (H) suggests the row's own department head but does not enforce
        // it - setShowErrorMessage(false) means typing any other staff no,
        // user id, or name is still accepted by Excel (the import is what
        // actually validates it).
        $hodValidation = $sheet->getCell("H{$r}")->getDataValidation();
        $hodValidation->setType(DataValidation::TYPE_LIST);
        $hodValidation->setAllowBlank(true);
        $hodValidation->setShowDropDown(true);
        $hodValidation->setShowErrorMessage(false);
        $hodValidation->setFormula1("INDIRECT(\"HOD_\"&MATCH(\$E{$r},DEPTFLAT,0))");
    }

    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'contract_staff_bulk_update_template_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
?>
