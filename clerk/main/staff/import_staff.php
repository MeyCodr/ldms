<?php
    session_start();
    require '../../../asset/vendor/autoload.php';
    include "../../../dbconn.php";
    include_once __DIR__ . '/../../../division_department_section.php';

    header('Content-Type: application/json; charset=utf-8');

    function respond($data)
    {
        if (ob_get_length()) ob_clean();
        echo json_encode($data);
        exit();
    }

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'CLERK') {
        respond(['message' => 'error', 'detail' => 'Unauthorized']);
    }

    if (empty($_FILES['import_file']['name'])) {
        respond(['message' => 'error', 'detail' => 'No file uploaded.']);
    }

    $fileName = $_FILES['import_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['xls', 'xlsx'])) {
        respond(['message' => 'error', 'detail' => 'Please upload an .xls or .xlsx file generated from the template.']);
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['import_file']['tmp_name']);
    } catch (Exception $e) {
        respond(['message' => 'error', 'detail' => 'Could not read the file: ' . $e->getMessage()]);
    }

    $validGenders = ['MALE', 'FEMALE'];
    $validStatuses = ['ACTIVE' => 'ACTIVE', 'RESIGN' => 'RESIGN', 'NOT ACTIVE' => 'RESIGN'];
    $orgStructure = getDbOrgStructure();

    $summary = [
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestDataRow();

    for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
        $staffno = trim((string) $sheet->getCell("A{$rowIndex}")->getValue());
        if ($staffno === '') continue;
        $staffno = strtoupper($staffno);

        // Only CONTRACT staff belong to this list, matching the page's scope.
        $check = $conn->prepare("SELECT staffname, gender, division, department, section, division_id, department_id, section_id, status FROM user WHERE staffno = ? AND designation = 'CONTRACT'");
        $check->bind_param('s', $staffno);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Staff No {$staffno}: not found in contract staff list, skipped.";
            continue;
        }

        $setClauses = [];
        $types = '';
        $params = [];

        // ===== Staff Name (column B) =====
        $staffnameRaw = trim((string) $sheet->getCell("B{$rowIndex}")->getValue());
        if ($staffnameRaw !== '') {
            $staffname = strtoupper($staffnameRaw);
            if ($staffname !== $existing['staffname']) {
                $setClauses[] = 'staffname = ?';
                $types .= 's';
                $params[] = $staffname;
            }
        }

        // ===== Gender (column C) =====
        $genderRaw = trim((string) $sheet->getCell("C{$rowIndex}")->getValue());
        if ($genderRaw !== '') {
            $genderUpper = strtoupper($genderRaw);
            if (!in_array($genderUpper, $validGenders, true)) {
                $summary['errors'][] = "Staff No {$staffno}: '{$genderRaw}' is not a valid gender, gender left unchanged.";
            } elseif ($genderUpper !== $existing['gender']) {
                $setClauses[] = 'gender = ?';
                $types .= 's';
                $params[] = $genderUpper;
            }
        }

        // ===== Division / Department / Section (columns D, E, F) =====
        $divisionRaw = trim((string) $sheet->getCell("D{$rowIndex}")->getValue());
        $departmentRaw = trim((string) $sheet->getCell("E{$rowIndex}")->getValue());
        $sectionRaw = trim((string) $sheet->getCell("F{$rowIndex}")->getValue());

        if ($divisionRaw !== '' || $departmentRaw !== '' || $sectionRaw !== '') {
            $effectiveDivision = $divisionRaw !== '' ? $divisionRaw : $existing['division'];
            $effectiveDepartment = $departmentRaw !== '' ? $departmentRaw : $existing['department'];
            $effectiveSection = $sectionRaw !== '' ? $sectionRaw : $existing['section'];

            $divisionMatch = null;
            foreach (array_keys($orgStructure) as $divName) {
                if (strcasecmp($divName, $effectiveDivision) === 0) { $divisionMatch = $divName; break; }
            }

            if ($divisionMatch === null) {
                $summary['errors'][] = "Staff No {$staffno}: division '{$effectiveDivision}' not found, division/department/section left unchanged.";
            } else {
                $departmentMatch = null;
                foreach (array_keys($orgStructure[$divisionMatch]) as $depName) {
                    if (strcasecmp($depName, $effectiveDepartment) === 0) { $departmentMatch = $depName; break; }
                }

                if ($departmentMatch === null) {
                    $summary['errors'][] = "Staff No {$staffno}: department '{$effectiveDepartment}' not found under division '{$divisionMatch}', division/department/section left unchanged.";
                } else {
                    $sectionOptions = $orgStructure[$divisionMatch][$departmentMatch];
                    $sectionMatch = null;
                    if ($effectiveSection === '') {
                        $sectionMatch = '';
                    } else {
                        foreach ($sectionOptions as $secName) {
                            if (strcasecmp($secName, $effectiveSection) === 0) { $sectionMatch = $secName; break; }
                        }
                    }

                    if ($sectionMatch === null) {
                        $summary['errors'][] = "Staff No {$staffno}: section '{$effectiveSection}' not found under department '{$departmentMatch}', division/department/section left unchanged.";
                    } else {
                        $divisionId = getDivisionIdByName($divisionMatch);
                        $departmentId = getDepartmentIdByName($divisionId, $departmentMatch);
                        $sectionId = $sectionMatch === '' ? null : getSectionIdByName($departmentId, $sectionMatch);

                        if ($divisionMatch !== $existing['division']) {
                            $setClauses[] = 'division = ?'; $types .= 's'; $params[] = $divisionMatch;
                        }
                        if ($departmentMatch !== $existing['department']) {
                            $setClauses[] = 'department = ?'; $types .= 's'; $params[] = $departmentMatch;
                        }
                        if ($sectionMatch !== $existing['section']) {
                            $setClauses[] = 'section = ?'; $types .= 's'; $params[] = $sectionMatch;
                        }
                        if ($divisionId != $existing['division_id']) {
                            $setClauses[] = 'division_id = ?'; $types .= 'i'; $params[] = $divisionId;
                        }
                        if ($departmentId != $existing['department_id']) {
                            $setClauses[] = 'department_id = ?'; $types .= 'i'; $params[] = $departmentId;
                        }
                        if ($sectionId != $existing['section_id']) {
                            $setClauses[] = 'section_id = ?'; $types .= 'i'; $params[] = $sectionId;
                        }
                    }
                }
            }
        }

        // ===== Status (column G) =====
        $statusRaw = trim((string) $sheet->getCell("G{$rowIndex}")->getValue());
        if ($statusRaw !== '') {
            $statusKey = strtoupper($statusRaw);
            if (!isset($validStatuses[$statusKey])) {
                $summary['errors'][] = "Staff No {$staffno}: '{$statusRaw}' is not a valid status, status left unchanged.";
            } else {
                $statusValue = $validStatuses[$statusKey];
                if ($statusValue !== $existing['status']) {
                    $setClauses[] = 'status = ?';
                    $types .= 's';
                    $params[] = $statusValue;
                }
            }
        }

        if (empty($setClauses)) {
            $summary['skipped']++;
            continue;
        }

        $params[] = $staffno;
        $types .= 's';

        $stmt = $conn->prepare("UPDATE user SET " . implode(', ', $setClauses) . " WHERE staffno = ? AND designation = 'CONTRACT'");
        $refs = [$types];
        foreach ($params as $key => $value) {
            $refs[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
        if ($stmt->execute()) {
            $summary['updated']++;
        } else {
            $summary['errors'][] = "Staff No {$staffno}: update failed ({$conn->error}).";
        }
    }

    $summary['message'] = 'done';
    respond($summary);
?>
