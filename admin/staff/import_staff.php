<?php
    session_start();
    include "../../dbconn.php";
    include_once __DIR__ . '/../../division_department_section.php';

    header('Content-Type: application/json; charset=utf-8');

    function respond($data)
    {
        if (ob_get_length()) ob_clean();
        echo json_encode($data);
        exit();
    }

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
        respond(['message' => 'error', 'detail' => 'Unauthorized']);
    }

    /* =====================================================================
     * Field catalogue
     * ---------------------------------------------------------------------
     * Every column the import understands, with the header spellings that
     * map onto it. Headers are matched after being lower-cased and stripped
     * of everything that is not a letter or a digit, so "Staff No.",
     * "STAFF_NO" and "staff no" all collapse to "staffno".
     * usertype / roletype / hodid are deliberately NOT importable: they
     * drive permissions and skill-matrix approval routing, so they stay
     * under the single-staff form. hodid is derived from the department.
     * ===================================================================== */
    $FIELD_LABELS = [
        'staffno'     => 'Staff No.',
        'staffname'   => 'Staff Name',
        'email'       => 'Email',
        'gender'      => 'Gender',
        'designation' => 'Designation',
        'division'    => 'Division',
        'department'  => 'Department',
        'section'     => 'Section',
        'status'      => 'Status',
        'date_join'   => 'Date Join',
        'plant'       => 'Plant',
        'grade'       => 'Grade',
        'hodid'       => 'HOD',
    ];

    $FIELD_SYNONYMS = [
        'staffno'     => ['staffno', 'staffnum', 'staffnumber', 'staffcode', 'employeeno', 'employeenumber', 'employeeid', 'empno', 'empid', 'nostaf', 'nopekerja', 'idpekerja'],
        'staffname'   => ['staffname', 'name', 'fullname', 'employeename', 'nama', 'namapenuh', 'namapekerja'],
        'email'       => ['email', 'emailaddress', 'mail', 'emel'],
        'gender'      => ['gender', 'sex', 'jantina'],
        'designation' => ['designation', 'position', 'jobtitle', 'title', 'jawatan'],
        'division'    => ['division', 'bahagian'],
        'department'  => ['department', 'dept', 'departmentname', 'jabatan'],
        'section'     => ['section', 'sectionname', 'unit', 'seksyen'],
        'status'      => ['status', 'employmentstatus', 'staffstatus', 'statuspekerja'],
        'date_join'   => ['datejoin', 'datejoined', 'joindate', 'joiningdate', 'dateofjoining', 'dateofjoin', 'hiredate', 'doj', 'tarikhmasuk', 'tarikhmula'],
        'plant'       => ['plant', 'site', 'location', 'kilang'],
        'grade'       => ['grade', 'gred'],
        'hodid'       => ['hodid', 'hod', 'hodno', 'hodstaffno', 'hodstaffid', 'hoduserid', 'hodname', 'reportsto', 'reportingto', 'ketuajabatan'],
    ];

    $INT_COLUMNS = ['division_id', 'department_id', 'section_id', 'hodid', 'grade'];

    /* ===================== Reference / validation data ==================== */
    $validPlants = [
        'ALAM IMPIAN PLANT',
        'ALAM MEGAH PLANT',
        'BUKIT BERUNTUNG PLANT',
        'FIF TANJUNG MALIM',
        'PEGOH PLANT',
        'PEKAN PLANT',
        'RASA PLANT',
        'SHAH ALAM 1 PLANT',
        'SHAH ALAM 2 PLANT',
        'TANJUNG MALIM 2',
        'WAREHOUSE BB',
    ];
    $validGenders = ['MALE', 'FEMALE'];
    $validDesignations = ['CONTRACT', 'EXECUTIVE', 'MANAGER (AM/HOS & ABOVE)', 'NON EXECUTIVE', 'TRAINEE'];
    $validStatuses = ['ACTIVE' => 'ACTIVE', 'RESIGN' => 'RESIGN', 'NOT ACTIVE' => 'RESIGN', 'RESIGNED' => 'RESIGN', 'INACTIVE' => 'RESIGN'];

    /* ============================ CSV helpers ============================ */

    function normaliseHeader($header)
    {
        $header = (string) $header;
        // Strip a UTF-8 BOM if this is the very first header cell.
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        // Drop parenthetical hints so the downloaded Excel template's
        // "Status (ACTIVE / RESIGN)" and "Date Join (YYYY-MM-DD)" still match.
        $header = preg_replace('/\([^)]*\)/', '', $header);
        return preg_replace('/[^a-z0-9]/', '', strtolower($header));
    }

    /**
     * Guess the delimiter from the header line so exports that use ";" or
     * tabs (common from Excel on a non-English locale) still import.
     */
    function detectDelimiter($line)
    {
        $candidates = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        $inQuotes = false;
        $length = strlen($line);
        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];
            if ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif (!$inQuotes && isset($candidates[$char])) {
                $candidates[$char]++;
            }
        }
        arsort($candidates);
        $best = key($candidates);
        return $candidates[$best] > 0 ? $best : ',';
    }

    function toUtf8($value)
    {
        $value = (string) $value;
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }
        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    /**
     * Read the whole CSV into a header row plus data rows, remembering the
     * original file line number of each row for the preview report.
     */
    function readCsvFile($path)
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ['error' => 'Could not open the uploaded file.'];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return ['error' => 'The file is empty.'];
        }
        $delimiter = detectDelimiter($firstLine);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter, '"', '');
        if ($headers === false) {
            fclose($handle);
            return ['error' => 'Could not read the header row.'];
        }
        foreach ($headers as $index => $header) {
            $headers[$index] = trim(toUtf8($header));
        }

        $rows = [];
        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $lineNumber++;
            // fgetcsv returns [null] for a completely blank line.
            if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                continue;
            }
            foreach ($row as $index => $value) {
                $row[$index] = trim(toUtf8($value));
            }
            $rows[] = ['line' => $lineNumber, 'cells' => $row];
        }
        fclose($handle);

        return ['delimiter' => $delimiter, 'headers' => $headers, 'rows' => $rows];
    }

    /**
     * Read an .xls/.xlsx workbook into the same shape readCsvFile() returns,
     * so everything downstream is format-agnostic. Excel date cells are
     * converted to Y-m-d here rather than left as serial numbers.
     */
    function readSpreadsheetFile($path)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } catch (Exception $e) {
            return ['error' => 'Could not read the file: ' . $e->getMessage()];
        }

        // The downloaded template carries helper sheets alongside the data.
        $sheet = $spreadsheet->getSheetByName('Staff Data');
        if ($sheet === null) {
            $sheet = $spreadsheet->getSheet(0);
        }

        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $readRow = function ($rowIndex) use ($sheet, $highestColumn) {
            $cells = [];
            for ($col = 1; $col <= $highestColumn; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $rowIndex);
                $value = $cell->getValue();
                if ($value !== null && is_numeric($value) && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
                $cells[] = trim((string) $value);
            }
            return $cells;
        };

        $headers = $highestRow >= 1 ? $readRow(1) : [];

        $rows = [];
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $cells = $readRow($rowIndex);
            $hasContent = false;
            foreach ($cells as $cell) {
                if ($cell !== '') { $hasContent = true; break; }
            }
            if (!$hasContent) continue;
            $rows[] = ['line' => $rowIndex, 'cells' => $cells];
        }

        return ['delimiter' => null, 'headers' => $headers, 'rows' => $rows];
    }

    /**
     * Pick the reader that matches the uploaded file.
     */
    function readImportFile($path, $extension)
    {
        if (in_array($extension, ['xls', 'xlsx'], true)) {
            require_once '../../asset/vendor/autoload.php';
            return readSpreadsheetFile($path);
        }
        return readCsvFile($path);
    }

    /**
     * Parse a date the way a Malaysian HR export writes it. strtotime() is
     * only a last resort because it reads 03/04/2020 as March 4th, while
     * every local export means 3rd April.
     */
    function parseImportDate($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y', 'd/m/y', 'Y/m/d', 'd-M-Y', 'j M Y', 'd M Y', 'M j, Y'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format . '|', $raw);
            if ($date !== false) {
                // PHP 8.2+ returns false here when the parse was clean.
                $errors = DateTime::getLastErrors();
                $clean = $errors === false
                    || ((int) $errors['warning_count'] === 0 && (int) $errors['error_count'] === 0);
                if ($clean) {
                    return $date->format('Y-m-d');
                }
            }
        }

        $timestamp = strtotime($raw);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    /* =====================================================================
     * Staff index
     * ---------------------------------------------------------------------
     * user.staffno carries no index, so every "WHERE staffno = ?" is a full
     * table scan. A 5000-row file doing that per row is O(n^2) and takes
     * minutes. One pass loads the whole table into memory instead, which
     * turns the entire analysis into a couple of queries.
     *
     * This is a snapshot taken before any writes, so a row cannot name a
     * staff created earlier in the same file as its HOD - that reports a
     * clean "HOD not found" rather than doing anything surprising.
     * ===================================================================== */
    function staffIndex()
    {
        global $conn;
        static $index = null;
        if ($index !== null) return $index;

        $index = ['byStaffNo' => [], 'byId' => [], 'byName' => []];

        $res = $conn->query("SELECT id, staffno, staffname, email, gender, designation, division,
                                    department, section, division_id, department_id, section_id,
                                    status, hodid, date_join, plant, grade, usertype
                             FROM user");
        while ($row = $res->fetch_assoc()) {
            $index['byId'][(int) $row['id']] = $row;

            // trim() matters: the file side trims before comparing, and a
            // stored staffno with stray whitespace would otherwise look like
            // a brand new staff and be inserted as a duplicate.
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
     * Resolve whatever the HOD column holds into a real user id.
     *
     * Accepts a staff number (M0361), a numeric user id (150), or an exact
     * unique staff name. Staff numbers are never purely numeric in this
     * database and never collide with an id, so the forms cannot be
     * confused. Anything that does not resolve is an error rather than a
     * silently stored value - a hodid pointing at nobody is the whole
     * problem this column could otherwise create at scale.
     *
     * Returns ['id' => int|null, 'error' => string|null, 'label' => string].
     */
    function resolveHodId($raw)
    {
        global $conn;
        static $cache = [];

        $key = strtoupper(trim((string) $raw));
        if ($key === '') {
            return ['id' => null, 'error' => null, 'label' => ''];
        }
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $index = staffIndex();
        $found = null;

        // 1. staff number
        if (isset($index['byStaffNo'][$key])) {
            $found = $index['byStaffNo'][$key];
        }

        // 2. numeric user id
        if ($found === null && ctype_digit($key) && isset($index['byId'][(int) $key])) {
            $found = $index['byId'][(int) $key];
        }

        // 3. exact staff name, only if it is unambiguous
        if ($found === null && isset($index['byName'][$key])) {
            if (count($index['byName'][$key]) > 1) {
                return $cache[$key] = ['id' => null, 'error' => "HOD '{$raw}': more than one staff has that name, use their staff number instead.", 'label' => ''];
            }
            $found = $index['byName'][$key][0];
        }

        if ($found === null) {
            return $cache[$key] = ['id' => null, 'error' => "HOD '{$raw}' not found - use a staff number, user id or exact staff name.", 'label' => ''];
        }

        return $cache[$key] = [
            'id' => (int) $found['id'],
            'error' => null,
            'label' => $found['staffno'] . ' ' . $found['staffname'],
        ];
    }

    /* =====================================================================
     * Memoised org lookups
     * ---------------------------------------------------------------------
     * A full staff file is ~5000 rows but resolves against only ~40
     * departments, so the underlying queries are cached by name. Without
     * this the org lookups alone cost three queries per row.
     * ===================================================================== */
    function cachedDivisionId($name)
    {
        static $cache = [];
        if (!array_key_exists($name, $cache)) {
            $cache[$name] = getDivisionIdByName($name);
        }
        return $cache[$name];
    }

    function cachedDepartmentId($divisionId, $name)
    {
        static $cache = [];
        $key = $divisionId . '|' . $name;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = getDepartmentIdByName($divisionId, $name);
        }
        return $cache[$key];
    }

    function cachedSectionId($departmentId, $name)
    {
        static $cache = [];
        $key = $departmentId . '|' . $name;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = getSectionIdByName($departmentId, $name);
        }
        return $cache[$key];
    }

    function matchOption($value, array $options)
    {
        foreach ($options as $option) {
            if (strcasecmp($option, $value) === 0) {
                return $option;
            }
        }
        return null;
    }

    /* ======================= Analysis (shared core) ======================
     * Runs for both preview and commit so the confirmed import is exactly
     * the one that was previewed.
     * ===================================================================== */
    function analyseImport($path, $extension = 'csv')
    {
        global $conn, $FIELD_LABELS, $FIELD_SYNONYMS;
        global $validPlants, $validGenders, $validDesignations, $validStatuses;

        $file = readImportFile($path, $extension);
        if (isset($file['error'])) {
            return ['error' => $file['error']];
        }

        /* ---- auto-capture the header row ---- */
        $columnOf = [];   // field => column index
        $mapping = [];    // for the preview report
        $ignored = [];
        foreach ($file['headers'] as $index => $header) {
            if ($header === '') continue;
            $normalised = normaliseHeader($header);
            $matched = null;
            foreach ($FIELD_SYNONYMS as $field => $synonyms) {
                if (in_array($normalised, $synonyms, true)) {
                    $matched = $field;
                    break;
                }
            }
            if ($matched === null || isset($columnOf[$matched])) {
                // Unknown header, or a duplicate of one already claimed.
                $ignored[] = $header;
                continue;
            }
            $columnOf[$matched] = $index;
            $mapping[] = ['header' => $header, 'field' => $matched, 'label' => $FIELD_LABELS[$matched]];
        }

        if (!isset($columnOf['staffno'])) {
            return ['error' => 'No Staff No. column found. The file needs a header row with a column named Staff No (or Employee No / No Staf).'];
        }

        $orgStructure = getDbOrgStructure();
        $rows = [];
        $counts = ['insert' => 0, 'update' => 0, 'unchanged' => 0, 'error' => 0];
        $seenStaffNo = [];

        foreach ($file['rows'] as $rawRow) {
            $cells = $rawRow['cells'];
            $get = function ($field) use ($cells, $columnOf) {
                if (!isset($columnOf[$field])) return null;      // column absent from file
                $index = $columnOf[$field];
                return isset($cells[$index]) ? trim((string) $cells[$index]) : '';
            };

            $staffno = strtoupper((string) $get('staffno'));
            $entry = [
                'line' => $rawRow['line'],
                'staffno' => $staffno,
                'staffname' => '',
                'action' => 'error',
                'messages' => [],
                'changes' => [],
                'matched' => [],
            ];

            if ($staffno === '') {
                // A trailing row of empty cells is noise, not an error.
                $hasContent = false;
                foreach ($cells as $cell) {
                    if (trim((string) $cell) !== '') { $hasContent = true; break; }
                }
                if (!$hasContent) continue;
                $entry['messages'][] = 'Staff No. is blank.';
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
            $seenStaffNo[$staffno] = $rawRow['line'];

            $staffLookup = staffIndex();
            $existing = isset($staffLookup['byStaffNo'][$staffno]) ? $staffLookup['byStaffNo'][$staffno] : null;
            $isNew = !$existing;
            $entry['staffname'] = $isNew ? '' : $existing['staffname'];

            $values = [];   // column => value to write
            $failed = false;

            /* ---- Staff Name ---- */
            $staffnameRaw = $get('staffname');
            if ($staffnameRaw !== null && $staffnameRaw !== '') {
                $values['staffname'] = strtoupper($staffnameRaw);
                $entry['staffname'] = $values['staffname'];
            } elseif ($isNew) {
                $entry['messages'][] = 'Staff Name is required for a new staff record.';
                $failed = true;
            }

            /* ---- Email ---- */
            $emailRaw = $get('email');
            if ($emailRaw !== null && $emailRaw !== '') {
                if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                    $entry['messages'][] = "'{$emailRaw}' is not a valid email.";
                    $failed = true;
                } else {
                    $values['email'] = $emailRaw;
                }
            }

            /* ---- Gender ---- */
            $genderRaw = $get('gender');
            if ($genderRaw !== null && $genderRaw !== '') {
                $gender = matchOption($genderRaw, $validGenders);
                if ($gender === null) {
                    // Accept the single-letter shorthand HR exports often use.
                    $short = strtoupper(substr($genderRaw, 0, 1));
                    if ($short === 'M') $gender = 'MALE';
                    elseif ($short === 'F') $gender = 'FEMALE';
                }
                if ($gender === null) {
                    $entry['messages'][] = "'{$genderRaw}' is not a valid gender (MALE / FEMALE).";
                    $failed = true;
                } else {
                    $values['gender'] = $gender;
                }
            }

            /* ---- Designation ---- */
            $designationRaw = $get('designation');
            if ($designationRaw !== null && $designationRaw !== '') {
                $designation = matchOption($designationRaw, $validDesignations);
                if ($designation === null) {
                    $entry['messages'][] = "'{$designationRaw}' is not a valid designation.";
                    $failed = true;
                } else {
                    $values['designation'] = $designation;
                }
            }

            /* ---- Division / Department / Section ---- */
            $divisionRaw = $get('division');
            $departmentRaw = $get('department');
            $sectionRaw = $get('section');
            $orgTouched = ($divisionRaw !== null && $divisionRaw !== '')
                || ($departmentRaw !== null && $departmentRaw !== '')
                || ($sectionRaw !== null && $sectionRaw !== '');

            if ($isNew && !$orgTouched) {
                $entry['messages'][] = 'Department is required for a new staff record.';
                $failed = true;
            } elseif ($orgTouched) {
                $effectiveDivision = ($divisionRaw !== null && $divisionRaw !== '') ? $divisionRaw : ($isNew ? '' : $existing['division']);
                $effectiveDepartment = ($departmentRaw !== null && $departmentRaw !== '') ? $departmentRaw : ($isNew ? '' : $existing['department']);
                $effectiveSection = ($sectionRaw !== null && $sectionRaw !== '') ? $sectionRaw : ($isNew ? '' : $existing['section']);

                // Division may be left out of the file entirely - infer it
                // from the department, which is unique across the org tree.
                if ($effectiveDivision === '' && $effectiveDepartment !== '') {
                    foreach ($orgStructure as $divName => $departments) {
                        foreach (array_keys($departments) as $depName) {
                            if (strcasecmp($depName, $effectiveDepartment) === 0) {
                                $effectiveDivision = $divName;
                                break 2;
                            }
                        }
                    }
                }

                // A row that carries its own stored org values unchanged must
                // not be rejected for them. Plenty of existing records hold
                // legacy division/department/section text that no longer
                // exists in the org tables; refusing those would block a
                // straight re-upload of the staff export over data the admin
                // never touched. Only a genuine *change* has to resolve.
                $orgUnchanged = !$isNew
                    && (string) $effectiveDivision === (string) $existing['division']
                    && (string) $effectiveDepartment === (string) $existing['department']
                    && (string) $effectiveSection === (string) $existing['section'];

                $divisionMatch = matchOption($effectiveDivision, array_keys($orgStructure));
                if ($divisionMatch === null && $orgUnchanged) {
                    $entry['messages'][] = "Note: stored division '{$effectiveDivision}' is not in the organisation list; left as it is.";
                } elseif ($divisionMatch === null) {
                    $entry['messages'][] = "Division '{$effectiveDivision}' not found.";
                    $failed = true;
                } else {
                    $departmentMatch = matchOption($effectiveDepartment, array_keys($orgStructure[$divisionMatch]));
                    if ($departmentMatch === null && $orgUnchanged) {
                        $entry['messages'][] = "Note: stored department '{$effectiveDepartment}' is not under division '{$divisionMatch}' in the organisation list; left as it is.";
                    } elseif ($departmentMatch === null) {
                        $entry['messages'][] = "Department '{$effectiveDepartment}' not found under division '{$divisionMatch}'.";
                        $failed = true;
                    } else {
                        $sectionMatch = $effectiveSection === '' ? '' : matchOption($effectiveSection, $orgStructure[$divisionMatch][$departmentMatch]);
                        if ($sectionMatch === null && $orgUnchanged) {
                            $entry['messages'][] = "Note: stored section '{$effectiveSection}' is not under department '{$departmentMatch}' in the organisation list; left as it is.";
                        } elseif ($sectionMatch === null) {
                            $entry['messages'][] = "Section '{$effectiveSection}' not found under department '{$departmentMatch}'.";
                            $failed = true;
                        } else {
                            $divisionId = cachedDivisionId($divisionMatch);
                            $departmentId = cachedDepartmentId($divisionId, $departmentMatch);
                            $sectionId = $sectionMatch === '' ? null : cachedSectionId($departmentId, $sectionMatch);

                            $values['division'] = $divisionMatch;
                            $values['department'] = $departmentMatch;
                            $values['section'] = $sectionMatch;
                            $values['division_id'] = $divisionId;
                            $values['department_id'] = $departmentId;
                            $values['section_id'] = $sectionId;

                            // Same rule as the single-staff form: the HOD link
                            // follows the department by default (an explicit
                            // HOD column below overrides it). Guarded so a
                            // department's own HOD is never recorded as
                            // reporting to themselves.
                            if ($isNew || $departmentMatch !== $existing['department']) {
                                $hodid = getDepartmentHodId($departmentId) ?: 0;
                                if (!$isNew && $hodid === (int) $existing['id']) {
                                    $hodid = 0;
                                }
                                $values['hodid'] = $hodid;
                            }
                        }
                    }
                }
            }

            /* ---- Status ---- */
            $statusRaw = $get('status');
            if ($statusRaw !== null && $statusRaw !== '') {
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

            /* ---- Date Join ---- */
            $dateRaw = $get('date_join');
            if ($dateRaw !== null && $dateRaw !== '') {
                $dateJoin = parseImportDate($dateRaw);
                if ($dateJoin === null) {
                    $entry['messages'][] = "Could not read the date '{$dateRaw}'.";
                    $failed = true;
                } else {
                    $values['date_join'] = $dateJoin;
                }
            }

            /* ---- Plant ---- */
            $plantRaw = $get('plant');
            if ($plantRaw !== null && $plantRaw !== '') {
                $plant = matchOption($plantRaw, $validPlants);
                if ($plant === null) {
                    $entry['messages'][] = "'{$plantRaw}' is not a valid plant.";
                    $failed = true;
                } else {
                    $values['plant'] = $plant;
                }
            }

            /* ---- Grade ---- */
            $gradeRaw = $get('grade');
            if ($gradeRaw !== null && $gradeRaw !== '') {
                if (!is_numeric($gradeRaw)) {
                    $entry['messages'][] = "'{$gradeRaw}' is not a valid grade.";
                    $failed = true;
                } else {
                    $values['grade'] = (int) $gradeRaw;
                }
            }

            /* ---- HOD ----
             * Comes last on purpose: an explicit HOD in the file overrides
             * the one derived from the department above. */
            $hodRaw = $get('hodid');
            if ($hodRaw !== null && $hodRaw !== '') {
                $hod = resolveHodId($hodRaw);
                if ($hod['error'] !== null) {
                    $entry['messages'][] = $hod['error'];
                    $failed = true;
                } else {
                    $values['hodid'] = $hod['id'];

                    // Flag disagreement with the department's own HOD. Not an
                    // error - deliberate exceptions exist - but worth seeing
                    // before confirming, and worth knowing that reassigning
                    // the department's HOD later will overwrite this row.
                    $effectiveDepartmentId = array_key_exists('department_id', $values)
                        ? $values['department_id']
                        : ($isNew ? null : $existing['department_id']);
                    $departmentHod = getDepartmentHodId($effectiveDepartmentId);
                    if ($departmentHod !== null && $departmentHod !== $hod['id']) {
                        $departmentHodLabel = resolveHodId((string) $departmentHod);
                        $entry['messages'][] = "Note: this department's assigned HOD is "
                            . ($departmentHodLabel['label'] !== '' ? $departmentHodLabel['label'] : $departmentHod)
                            . "; reassigning the department HOD later will overwrite this.";
                    }
                }
            }

            if ($failed) {
                $entry['action'] = 'error';
                $rows[] = $entry;
                $counts['error']++;
                continue;
            }

            if ($isNew) {
                $values['password'] = md5('P@ss1234');
                $values['staffno'] = $staffno;
                $entry['action'] = 'insert';
                $entry['changes'] = $values;
                if (!isset($values['designation'])) {
                    $entry['messages'][] = 'No designation given - this staff will not appear in designation-filtered lists.';
                }
                $counts['insert']++;
            } else {
                // Only write what actually differs, so a re-import is a no-op.
                // Columns that were read but already match are tracked
                // separately so the preview can say "already correct"
                // instead of leaving them out and looking like they were
                // ignored.
                $changed = [];
                $alreadyMatching = [];
                foreach ($values as $column => $value) {
                    $current = array_key_exists($column, $existing) ? $existing[$column] : null;
                    if (($value === null && $current === null)
                        || ($value !== null && $current !== null && (string) $value === (string) $current)
                        || ($value === null && (string) $current === '')) {
                        $alreadyMatching[] = $column;
                        continue;
                    }
                    $changed[$column] = $value;
                }
                $entry['matched'] = $alreadyMatching;
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
            'mapping' => $mapping,
            'ignored' => $ignored,
            'rows' => $rows,
            'counts' => $counts,
        ];
    }

    /**
     * Write the analysed rows. All-or-nothing: any statement failure rolls
     * the whole file back so a half-imported staff list is never left behind.
     */
    function applyImport(array $rows)
    {
        global $conn, $INT_COLUMNS;

        $summary = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => []];
        $aborted = false;
        $statementCache = [];

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
                $types .= in_array($column, $INT_COLUMNS, true) ? 'i' : 's';
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

            // Rows sharing a column set share their SQL, so prepare each
            // distinct statement once instead of once per row.
            if (!isset($statementCache[$sql])) {
                $prepared = $conn->prepare($sql);
                if (!$prepared) {
                    $summary['errors'][] = "Line {$row['line']} ({$row['staffno']}): could not prepare statement ({$conn->error}).";
                    $aborted = true;
                    break;
                }
                $statementCache[$sql] = $prepared;
            }
            $stmt = $statementCache[$sql];
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

    // Derived columns the admin never typed, so they are noise in the preview.
    // hodid is shown: it is now importable, and even when derived from the
    // department it is worth seeing before confirming.
    function hiddenPreviewColumns()
    {
        return ['password', 'division_id', 'department_id', 'section_id', 'staffno'];
    }

    function describeChanges(array $changes)
    {
        $hidden = hiddenPreviewColumns();
        $parts = [];
        foreach ($changes as $column => $value) {
            if (in_array($column, $hidden, true)) continue;
            if ($column === 'hodid') {
                $parts[] = 'hod = ' . describeHod($value);
                continue;
            }
            $parts[] = $column . ' = ' . ($value === null || $value === '' ? '(blank)' : $value);
        }
        return implode('; ', $parts);
    }

    // Render a hodid as something an admin can actually check.
    function describeHod($hodId)
    {
        if ($hodId === null || (int) $hodId === 0) return '(none)';
        $resolved = resolveHodId((string) (int) $hodId);
        return $resolved['label'] !== '' ? $resolved['label'] : (string) $hodId;
    }

    /**
     * Columns that were read from the file but already hold that value.
     * Spelled out so an unchanged column is never mistaken for an ignored one.
     */
    function describeMatched(array $matched)
    {
        $hidden = hiddenPreviewColumns();
        $parts = [];
        foreach ($matched as $column) {
            if (in_array($column, $hidden, true)) continue;
            $parts[] = $column === 'hodid' ? 'hod' : $column;
        }
        return empty($parts) ? '' : 'already correct: ' . implode(', ', $parts);
    }

    /* ============================== Actions ============================== */

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'preview') {
        if (empty($_FILES['import_file']['name'])) {
            respond(['message' => 'error', 'detail' => 'No file uploaded.']);
        }

        $fileName = $_FILES['import_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['csv', 'txt', 'xls', 'xlsx'])) {
            respond(['message' => 'error', 'detail' => 'Please upload a .csv, .xls or .xlsx file.']);
        }
        if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
            respond(['message' => 'error', 'detail' => 'Upload failed.']);
        }

        // Park the file so Confirm imports exactly what was previewed.
        $token = bin2hex(random_bytes(16));
        $storedPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ldms_staff_import_' . $token . '.' . $fileExt;
        if (!move_uploaded_file($_FILES['import_file']['tmp_name'], $storedPath)) {
            respond(['message' => 'error', 'detail' => 'Could not store the uploaded file for review.']);
        }

        $result = analyseImport($storedPath, $fileExt);
        if (isset($result['error'])) {
            @unlink($storedPath);
            respond(['message' => 'error', 'detail' => $result['error']]);
        }

        if (isset($_SESSION['staff_csv_import']['path'])) {
            @unlink($_SESSION['staff_csv_import']['path']);   // drop an abandoned preview
        }
        $_SESSION['staff_csv_import'] = [
            'token' => $token,
            'path' => $storedPath,
            'name' => $fileName,
            'ext' => $fileExt,
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
                    : trim(describeChanges($row['changes']) . ' ' . implode(' ', $row['messages'])),
                'matched' => describeMatched($row['matched']),
            ];
        }

        respond([
            'message' => 'done',
            'token' => $token,
            'filename' => $fileName,
            'mapping' => $result['mapping'],
            'ignored' => $result['ignored'],
            'counts' => $result['counts'],
            'rows' => $preview,
        ]);
    }

    if ($action === 'commit') {
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        if (!isset($_SESSION['staff_csv_import']) || $_SESSION['staff_csv_import']['token'] !== $token) {
            respond(['message' => 'error', 'detail' => 'This preview has expired. Please upload the file again.']);
        }

        $storedPath = $_SESSION['staff_csv_import']['path'];
        if (!is_file($storedPath)) {
            unset($_SESSION['staff_csv_import']);
            respond(['message' => 'error', 'detail' => 'The previewed file is no longer available. Please upload it again.']);
        }

        $storedExt = isset($_SESSION['staff_csv_import']['ext']) ? $_SESSION['staff_csv_import']['ext'] : 'csv';
        $result = analyseImport($storedPath, $storedExt);
        if (isset($result['error'])) {
            respond(['message' => 'error', 'detail' => $result['error']]);
        }

        $summary = applyImport($result['rows']);

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
        unset($_SESSION['staff_csv_import']);

        $summary['message'] = 'done';
        respond($summary);
    }

    if ($action === 'cancel') {
        if (isset($_SESSION['staff_csv_import']['path'])) {
            @unlink($_SESSION['staff_csv_import']['path']);
        }
        unset($_SESSION['staff_csv_import']);
        respond(['message' => 'done']);
    }

    respond(['message' => 'error', 'detail' => 'Unknown action.']);
?>
