<?php
    session_start();
    require '../../asset/vendor/autoload.php';
    include "../../dbconn.php";

    use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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

        // ===== Parse Date Join (column C) =====
        $dateCell = $sheet->getCell("C{$rowIndex}");
        $dateRaw = $dateCell->getValue();
        $dateJoin = null;
        if ($dateRaw !== null && trim((string) $dateRaw) !== '') {
            if (is_numeric($dateRaw) && ExcelDate::isDateTime($dateCell)) {
                $dateJoin = ExcelDate::excelToDateTimeObject($dateRaw)->format('Y-m-d');
            } else {
                $ts = strtotime(trim((string) $dateRaw));
                if ($ts !== false) {
                    $dateJoin = date('Y-m-d', $ts);
                }
            }
            if ($dateJoin === null) {
                $summary['errors'][] = "Staff No {$staffno}: could not parse date '{$dateRaw}', date join left unchanged.";
            }
        }

        // ===== Parse Plant (column D) =====
        $plantRaw = trim((string) $sheet->getCell("D{$rowIndex}")->getValue());
        $plant = null;
        if ($plantRaw !== '') {
            $plantUpper = strtoupper($plantRaw);
            if (in_array($plantUpper, $validPlants, true)) {
                $plant = $plantUpper;
            } else {
                $summary['errors'][] = "Staff No {$staffno}: '{$plantRaw}' is not a valid plant, plant left unchanged.";
            }
        }

        if ($dateJoin === null && $plant === null) {
            $summary['skipped']++;
            continue;
        }

        $check = $conn->prepare("SELECT date_join, plant FROM user WHERE staffno = ?");
        $check->bind_param('s', $staffno);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Staff No {$staffno}: not found, skipped.";
            continue;
        }

        $setClauses = [];
        $types = '';
        $params = [];

        if ($dateJoin !== null && $existing['date_join'] !== $dateJoin) {
            $setClauses[] = 'date_join = ?';
            $types .= 's';
            $params[] = $dateJoin;
        }
        if ($plant !== null && $existing['plant'] !== $plant) {
            $setClauses[] = 'plant = ?';
            $types .= 's';
            $params[] = $plant;
        }

        if (empty($setClauses)) continue; // no change

        $params[] = $staffno;
        $types .= 's';

        $stmt = $conn->prepare("UPDATE user SET " . implode(', ', $setClauses) . " WHERE staffno = ?");
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
