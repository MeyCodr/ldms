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

    $validGenders = ['MALE', 'FEMALE'];
    $validStatuses = ['ACTIVE' => 'ACTIVE', 'RESIGN' => 'RESIGN', 'NOT ACTIVE' => 'RESIGN'];

    $FIELD_LABELS = [
        'staffname' => 'Staff Name',
        'gender'    => 'Gender',
        'division'  => 'Division',
        'department'=> 'Department',
        'section'   => 'Section',
        'status'    => 'Status',
        'hodid'     => 'HOD',
    ];

    function matchOption($value, array $options)
    {
        foreach ($options as $option) {
            if (strcasecmp($option, $value) === 0) {
                return $option;
            }
        }
        return null;
    }

    /**
     * Every existing user, indexed three ways (Staff No, id, name), fetched
     * once so a large file does not run one lookup query per row.
     */
    function staffIndex()
    {
        global $conn;
        static $index = null;
        if ($index !== null) return $index;

        $index = ['byStaffNo' => [], 'byId' => [], 'byName' => []];
        $res = $conn->query("SELECT id, staffno, staffname, gender, designation, division, department, section, division_id, department_id, section_id, status, hodid FROM user");
        while ($row = $res->fetch_assoc()) {
            $index['byId'][(int) $row['id']] = $row;

            $staffno = strtoupper(trim((string) $row['staffno']));
            if ($staffno !== '' && !isset($index['byStaffNo'][$staffno])) {
                $index['byStaffNo'][$staffno] = $row;
            }

            $name = strtoupper(trim((string) $row['staffname']));
            if ($name !== '') {
                $index['byName'][$name][] = $row;
            }
        }
        return $index;
    }

    /**
     * Resolve the optional HOD column into a real user id. Accepts a staff
     * number (M0361), a numeric user id (150), or an exact unique staff
     * name - same rule as admin/staff/import_staff.php. Anything that does
     * not resolve is an error rather than a silently stored value.
     *
     * Returns ['id' => int|null, 'error' => string|null].
     */
    function resolveHodId($raw)
    {
        static $cache = [];

        $key = strtoupper(trim((string) $raw));
        if ($key === '') {
            return ['id' => null, 'error' => null];
        }
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $index = staffIndex();
        $found = null;

        if (isset($index['byStaffNo'][$key])) {
            $found = $index['byStaffNo'][$key];
        }
        if ($found === null && ctype_digit($key) && isset($index['byId'][(int) $key])) {
            $found = $index['byId'][(int) $key];
        }
        if ($found === null && isset($index['byName'][$key])) {
            if (count($index['byName'][$key]) > 1) {
                return $cache[$key] = ['id' => null, 'error' => "HOD '{$raw}': more than one staff has that name, use their staff number instead."];
            }
            $found = $index['byName'][$key][0];
        }

        if ($found === null) {
            return $cache[$key] = ['id' => null, 'error' => "HOD '{$raw}' not found - use a staff number, user id or exact staff name."];
        }

        return $cache[$key] = ['id' => (int) $found['id'], 'error' => null];
    }

    /**
     * Runs for both preview and commit so the confirmed import is exactly
     * the one that was previewed. One row = one CONTRACT staff: a Staff No
     * not yet in the system is an insert, an existing CONTRACT Staff No is
     * an update (blank cells left unchanged), and a Staff No that belongs
     * to a non-CONTRACT record is refused - this page only manages
     * contract staff.
     */
    function analyseStaffImport($path)
    {
        global $validGenders, $validStatuses, $FIELD_LABELS;

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } catch (\Throwable $e) {
            return ['error' => 'Could not read the file: ' . $e->getMessage()];
        }

        $orgStructure = getDbOrgStructure();
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $staffLookup = staffIndex()['byStaffNo'];

        $rows = [];
        $counts = ['insert' => 0, 'update' => 0, 'unchanged' => 0, 'error' => 0];
        $seenStaffNo = [];
        $exampleSkipped = 0;

        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $cell = function ($col) use ($sheet, $rowIndex) {
                return trim((string) $sheet->getCell("{$col}{$rowIndex}")->getValue());
            };

            $staffno = strtoupper($cell('A'));
            if (strpos($staffno, '(EXAMPLE)') === 0) {
                $exampleSkipped++;
                continue;
            }

            $isBlank = true;
            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                if ($cell($col) !== '') { $isBlank = false; break; }
            }
            if ($isBlank) continue;

            $entry = [
                'line' => $rowIndex,
                'staffno' => $staffno,
                'staffname' => '',
                'action' => 'error',
                'messages' => [],
                'changes' => [],
            ];

            if ($staffno === '') {
                $entry['messages'][] = 'Staff No is blank.';
                $rows[] = $entry;
                $counts['error']++;
                continue;
            }

            if (isset($seenStaffNo[$staffno])) {
                $entry['messages'][] = 'Duplicate of line ' . $seenStaffNo[$staffno] . ' in this file.';
                $rows[] = $entry;
                $counts['error']++;
                continue;
            }
            $seenStaffNo[$staffno] = $rowIndex;

            $existing = isset($staffLookup[$staffno]) ? $staffLookup[$staffno] : null;
            if ($existing !== null && $existing['designation'] !== 'CONTRACT') {
                $entry['staffname'] = $existing['staffname'];
                $entry['messages'][] = "Staff No {$staffno} already exists as a {$existing['designation']} staff; this page only manages contract staff.";
                $rows[] = $entry;
                $counts['error']++;
                continue;
            }
            $isNew = $existing === null;
            $entry['staffname'] = $isNew ? '' : $existing['staffname'];

            $values = [];
            $failed = false;

            // ===== Staff Name (column B) =====
            $staffnameRaw = $cell('B');
            if ($staffnameRaw !== '') {
                $values['staffname'] = strtoupper($staffnameRaw);
                $entry['staffname'] = $values['staffname'];
            } elseif ($isNew) {
                $entry['messages'][] = 'Staff Name is required for a new staff record.';
                $failed = true;
            }

            // ===== Gender (column C) =====
            $genderRaw = $cell('C');
            if ($genderRaw !== '') {
                $gender = matchOption($genderRaw, $validGenders);
                if ($gender === null) {
                    $entry['messages'][] = "'{$genderRaw}' is not a valid gender (MALE / FEMALE).";
                    $failed = true;
                } else {
                    $values['gender'] = $gender;
                }
            } elseif ($isNew) {
                $entry['messages'][] = 'Gender is required for a new staff record.';
                $failed = true;
            }

            // ===== Division / Department / Section (columns D, E, F) =====
            $divisionRaw = $cell('D');
            $departmentRaw = $cell('E');
            $sectionRaw = $cell('F');
            $orgTouched = ($divisionRaw !== '' || $departmentRaw !== '' || $sectionRaw !== '');

            if ($isNew && !$orgTouched) {
                $entry['messages'][] = 'Division / Department is required for a new staff record.';
                $failed = true;
            } elseif ($orgTouched) {
                $effectiveDivision = $divisionRaw !== '' ? $divisionRaw : ($isNew ? '' : $existing['division']);
                $effectiveDepartment = $departmentRaw !== '' ? $departmentRaw : ($isNew ? '' : $existing['department']);
                $effectiveSection = $sectionRaw !== '' ? $sectionRaw : ($isNew ? '' : $existing['section']);

                $divisionMatch = matchOption($effectiveDivision, array_keys($orgStructure));
                if ($divisionMatch === null) {
                    $entry['messages'][] = "Division '{$effectiveDivision}' not found.";
                    $failed = true;
                } else {
                    $departmentMatch = matchOption($effectiveDepartment, array_keys($orgStructure[$divisionMatch]));
                    if ($departmentMatch === null) {
                        $entry['messages'][] = "Department '{$effectiveDepartment}' not found under division '{$divisionMatch}'.";
                        $failed = true;
                    } else {
                        $sectionOptions = $orgStructure[$divisionMatch][$departmentMatch];
                        $sectionMatch = $effectiveSection === '' ? '' : matchOption($effectiveSection, $sectionOptions);
                        if ($sectionMatch === null) {
                            $entry['messages'][] = "Section '{$effectiveSection}' not found under department '{$departmentMatch}'.";
                            $failed = true;
                        } else {
                            $divisionId = getDivisionIdByName($divisionMatch);
                            $departmentId = getDepartmentIdByName($divisionId, $departmentMatch);
                            $sectionId = $sectionMatch === '' ? null : getSectionIdByName($departmentId, $sectionMatch);

                            $values['division'] = $divisionMatch;
                            $values['department'] = $departmentMatch;
                            $values['section'] = $sectionMatch;
                            $values['division_id'] = $divisionId;
                            $values['department_id'] = $departmentId;
                            $values['section_id'] = $sectionId;

                            // hodid follows the department's assigned HOD
                            // (org.php > Assign HOD), the single source of
                            // truth - see getDepartmentHodId(). Guarded so a
                            // department's own HOD is never recorded as
                            // reporting to themselves.
                            if ($isNew || $departmentMatch !== $existing['department']) {
                                $hodValue = getDepartmentHodId($departmentId) ?: 0;
                                if (!$isNew && $hodValue === (int) $existing['id']) {
                                    $hodValue = 0;
                                }
                                $values['hodid'] = $hodValue;
                            }
                        }
                    }
                }
            }

            // ===== Status (column G) =====
            $statusRaw = $cell('G');
            if ($statusRaw !== '') {
                $statusKey = strtoupper($statusRaw);
                if (!isset($validStatuses[$statusKey])) {
                    $entry['messages'][] = "'{$statusRaw}' is not a valid status (ACTIVE / RESIGN).";
                    $failed = true;
                } else {
                    $values['status'] = $validStatuses[$statusKey];
                }
            } elseif ($isNew) {
                $values['status'] = 'ACTIVE';
            }

            // ===== HOD override (column H) =====
            // Optional. Comes last on purpose: an explicit HOD in the file
            // overrides the one derived from the department above. Accepts
            // a Staff No, user id, or exact staff name - anything that
            // cannot be resolved to a real person is an error, never a
            // silent guess.
            $hodRaw = $cell('H');
            if ($hodRaw !== '') {
                $hod = resolveHodId($hodRaw);
                if ($hod['error'] !== null) {
                    $entry['messages'][] = $hod['error'];
                    $failed = true;
                } else {
                    $values['hodid'] = $hod['id'];
                }
            }

            if ($failed) {
                $entry['action'] = 'error';
                $rows[] = $entry;
                $counts['error']++;
                continue;
            }

            if ($isNew) {
                $values['staffno'] = $staffno;
                $values['password'] = md5('P@ss1234');
                $values['designation'] = 'CONTRACT';
                $entry['action'] = 'insert';
                $entry['changes'] = $values;
                $counts['insert']++;
            } else {
                $changed = [];
                foreach ($values as $column => $value) {
                    $current = array_key_exists($column, $existing) ? $existing[$column] : null;
                    if (($value === null && ($current === null || (string) $current === ''))
                        || ($value !== null && (string) $value === (string) $current)) {
                        continue;
                    }
                    $changed[$column] = $value;
                }
                if (empty($changed)) {
                    $entry['action'] = 'unchanged';
                    $counts['unchanged']++;
                } else {
                    $entry['action'] = 'update';
                    $entry['changes'] = $changed;
                    $entry['id'] = (int) $existing['id'];
                    $counts['update']++;
                }
            }

            $rows[] = $entry;
        }

        return [
            'rows' => $rows,
            'counts' => $counts,
            'exampleSkipped' => $exampleSkipped,
        ];
    }

    function describeChanges(array $changes, array $labels)
    {
        $parts = [];
        foreach ($changes as $column => $value) {
            if (!isset($labels[$column])) continue; // internal columns (ids, password, staffno, designation)
            $parts[] = $labels[$column] . ' = ' . ($value === '' ? '(blank)' : $value);
        }
        return implode('; ', $parts);
    }

    /**
     * Write the analysed rows. A row marked "error" is simply skipped (it
     * was already reported at preview time); only a genuine database
     * failure aborts and rolls back the whole batch, so one bad statement
     * cannot leave a half-written file behind.
     */
    function applyStaffImport(array $rows)
    {
        global $conn;

        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => []];
        $aborted = false;
        $intColumns = ['division_id', 'department_id', 'section_id', 'hodid'];

        $conn->begin_transaction();

        foreach ($rows as $row) {
            if ($row['action'] === 'error') {
                $summary['skipped']++;
                $summary['errors'][] = "Line {$row['line']} ({$row['staffno']}): " . implode(' ', $row['messages']);
                continue;
            }
            if ($row['action'] === 'unchanged') {
                $summary['unchanged']++;
                continue;
            }

            $changes = $row['changes'];
            $columns = array_keys($changes);
            $types = '';
            $params = [];
            foreach ($columns as $column) {
                $types .= in_array($column, $intColumns, true) ? 'i' : 's';
                $params[] = $changes[$column];
            }

            if ($row['action'] === 'insert') {
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $sql = "INSERT INTO `user` (`" . implode('`, `', $columns) . "`) VALUES ({$placeholders})";
            } else {
                $setClauses = [];
                foreach ($columns as $column) {
                    $setClauses[] = "`{$column}` = ?";
                }
                $sql = "UPDATE `user` SET " . implode(', ', $setClauses) . " WHERE id = ?";
                $types .= 'i';
                $params[] = $row['id'];
            }

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $summary['errors'][] = "Line {$row['line']} ({$row['staffno']}): could not prepare statement ({$conn->error}).";
                $aborted = true;
                break;
            }
            $refs = [$types];
            foreach ($params as $key => $value) {
                $refs[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);

            if (!$stmt->execute()) {
                $summary['errors'][] = "Line {$row['line']} ({$row['staffno']}): " . $row['action'] . " failed ({$stmt->error}).";
                $aborted = true;
                break;
            }
            $summary[$row['action'] === 'insert' ? 'inserted' : 'updated']++;
        }

        $summary['aborted'] = $aborted;
        return $summary;
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'preview') {
        if (empty($_FILES['import_file']['name'])) {
            respond(['message' => 'error', 'detail' => 'No file uploaded.']);
        }

        $fileName = $_FILES['import_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['xls', 'xlsx'], true)) {
            respond(['message' => 'error', 'detail' => 'Please upload an .xls or .xlsx file generated from one of the templates.']);
        }
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            respond(['message' => 'error', 'detail' => 'Upload failed.']);
        }

        // Park the file so Confirm imports exactly what was previewed.
        $token = bin2hex(random_bytes(16));
        $storedPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ldms_clerk_staff_import_' . $token . '.' . $fileExt;
        if (!move_uploaded_file($_FILES['import_file']['tmp_name'], $storedPath)) {
            respond(['message' => 'error', 'detail' => 'Could not store the uploaded file for review.']);
        }

        $result = analyseStaffImport($storedPath);
        if (isset($result['error'])) {
            @unlink($storedPath);
            respond(['message' => 'error', 'detail' => $result['error']]);
        }

        if (isset($_SESSION['clerk_staff_import']['path'])) {
            @unlink($_SESSION['clerk_staff_import']['path']);   // drop an abandoned preview
        }
        $_SESSION['clerk_staff_import'] = [
            'token' => $token,
            'path' => $storedPath,
            'name' => $fileName,
            'created' => time(),
        ];

        $preview = [];
        foreach ($result['rows'] as $row) {
            $preview[] = [
                'line' => $row['line'],
                'staffno' => $row['staffno'],
                'staffname' => $row['staffname'],
                'action' => $row['action'],
                'detail' => $row['action'] === 'error'
                    ? implode(' ', $row['messages'])
                    : describeChanges($row['changes'], $FIELD_LABELS),
            ];
        }

        respond([
            'message' => 'done',
            'token' => $token,
            'filename' => $fileName,
            'counts' => $result['counts'],
            'exampleSkipped' => $result['exampleSkipped'],
            'rows' => $preview,
        ]);
    }

    if ($action === 'commit') {
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        if (!isset($_SESSION['clerk_staff_import']) || $_SESSION['clerk_staff_import']['token'] !== $token) {
            respond(['message' => 'error', 'detail' => 'This preview has expired. Please upload the file again.']);
        }

        $storedPath = $_SESSION['clerk_staff_import']['path'];
        if (!is_file($storedPath)) {
            unset($_SESSION['clerk_staff_import']);
            respond(['message' => 'error', 'detail' => 'The previewed file is no longer available. Please upload it again.']);
        }

        $result = analyseStaffImport($storedPath);
        if (isset($result['error'])) {
            respond(['message' => 'error', 'detail' => $result['error']]);
        }

        $summary = applyStaffImport($result['rows']);

        if ($summary['aborted']) {
            $conn->rollback();
            respond([
                'message' => 'error',
                'detail' => "Import aborted and nothing was saved.\n" . implode("\n", $summary['errors']),
            ]);
        }

        $conn->commit();
        unset($summary['aborted']);

        @unlink($storedPath);
        unset($_SESSION['clerk_staff_import']);

        $summary['message'] = 'done';
        respond($summary);
    }

    if ($action === 'cancel') {
        if (isset($_SESSION['clerk_staff_import']['path'])) {
            @unlink($_SESSION['clerk_staff_import']['path']);
        }
        unset($_SESSION['clerk_staff_import']);
        respond(['message' => 'done']);
    }

    respond(['message' => 'error', 'detail' => 'Unknown action.']);
?>
