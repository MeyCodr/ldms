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

        $dateCell = $sheet->getCell("C{$rowIndex}");
        $rawValue = $dateCell->getValue();

        if ($rawValue === null || trim((string) $rawValue) === '') {
            $summary['skipped']++;
            continue;
        }

        $dateJoin = null;
        if (is_numeric($rawValue) && ExcelDate::isDateTime($dateCell)) {
            $dateJoin = ExcelDate::excelToDateTimeObject($rawValue)->format('Y-m-d');
        } else {
            $ts = strtotime(trim((string) $rawValue));
            if ($ts !== false) {
                $dateJoin = date('Y-m-d', $ts);
            }
        }

        if ($dateJoin === null) {
            $summary['errors'][] = "Staff No {$staffno}: could not parse date '{$rawValue}', skipped.";
            continue;
        }

        $check = $conn->prepare("SELECT date_join FROM user WHERE staffno = ?");
        $check->bind_param('s', $staffno);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Staff No {$staffno}: not found, skipped.";
            continue;
        }
        if ($existing['date_join'] === $dateJoin) continue; // no change

        $stmt = $conn->prepare("UPDATE user SET date_join = ? WHERE staffno = ?");
        $stmt->bind_param('ss', $dateJoin, $staffno);
        if ($stmt->execute()) {
            $summary['updated']++;
        } else {
            $summary['errors'][] = "Staff No {$staffno}: update failed ({$conn->error}).";
        }
    }

    $summary['message'] = 'done';
    respond($summary);
?>
