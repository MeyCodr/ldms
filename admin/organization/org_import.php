<?php
session_start();
require '../../asset/vendor/autoload.php';
include "../../dbconn.php";

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

$deleteMissing = isset($_POST['delete_missing']) && $_POST['delete_missing'] === '1';

$summary = [
    'divisions_updated' => 0,
    'departments_updated' => 0,
    'sections_updated' => 0,
    'divisions_deleted' => 0,
    'departments_deleted' => 0,
    'sections_deleted' => 0,
    'skipped' => 0,
    'errors' => [],
];

// Reads column A of a sheet (skipping the header row) and returns the set of valid numeric IDs present.
function collectSheetIds($spreadsheet, $sheetName)
{
    $ids = [];
    if (!$spreadsheet->sheetNameExists($sheetName)) return $ids;
    $rows = $spreadsheet->getSheetByName($sheetName)->toArray();
    array_shift($rows);
    foreach ($rows as $r) {
        $id = trim((string)($r[0] ?? ''));
        if ($id !== '' && ctype_digit($id)) $ids[] = (int)$id;
    }
    return $ids;
}

// ===== DIVISIONS (ID, Name, Short Name) =====
if ($spreadsheet->sheetNameExists('Divisions')) {
    $rows = $spreadsheet->getSheetByName('Divisions')->toArray();
    array_shift($rows); // drop header
    foreach ($rows as $r) {
        $id = trim((string)($r[0] ?? ''));
        if ($id === '' || !ctype_digit($id)) continue;
        $id = (int)$id;
        $name = strtoupper(trim((string)($r[1] ?? '')));
        $shortname = strtoupper(trim((string)($r[2] ?? ''))) ?: null;
        if ($name === '') {
            $summary['errors'][] = "Division ID {$id}: name cannot be empty, skipped.";
            $summary['skipped']++;
            continue;
        }

        $check = $conn->prepare("SELECT name, shortname FROM divisions WHERE id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Division ID {$id}: not found, skipped.";
            $summary['skipped']++;
            continue;
        }
        if ($existing['name'] === $name && $existing['shortname'] === $shortname) continue; // no change

        $stmt = $conn->prepare("UPDATE divisions SET name = ?, shortname = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $shortname, $id);
        if ($stmt->execute()) {
            $summary['divisions_updated']++;
        } else {
            $summary['errors'][] = "Division ID {$id}: update failed ({$conn->error}).";
        }
    }
}

// ===== DEPARTMENTS (ID, Division ID, Division Name, Name, Short Name) =====
if ($spreadsheet->sheetNameExists('Departments')) {
    $rows = $spreadsheet->getSheetByName('Departments')->toArray();
    array_shift($rows);
    foreach ($rows as $r) {
        $id = trim((string)($r[0] ?? ''));
        if ($id === '' || !ctype_digit($id)) continue;
        $id = (int)$id;
        $name = strtoupper(trim((string)($r[3] ?? '')));
        $shortname = strtoupper(trim((string)($r[4] ?? ''))) ?: null;
        if ($name === '') {
            $summary['errors'][] = "Department ID {$id}: name cannot be empty, skipped.";
            $summary['skipped']++;
            continue;
        }

        $check = $conn->prepare("SELECT name, shortname FROM departments WHERE id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Department ID {$id}: not found, skipped.";
            $summary['skipped']++;
            continue;
        }
        if ($existing['name'] === $name && $existing['shortname'] === $shortname) continue;

        $stmt = $conn->prepare("UPDATE departments SET name = ?, shortname = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $shortname, $id);
        if ($stmt->execute()) {
            $summary['departments_updated']++;
        } else {
            $summary['errors'][] = "Department ID {$id}: update failed ({$conn->error}).";
        }
    }
}

// ===== SECTIONS (ID, Department ID, Department Name, Name, Short Name) =====
if ($spreadsheet->sheetNameExists('Sections')) {
    $rows = $spreadsheet->getSheetByName('Sections')->toArray();
    array_shift($rows);
    foreach ($rows as $r) {
        $id = trim((string)($r[0] ?? ''));
        if ($id === '' || !ctype_digit($id)) continue;
        $id = (int)$id;
        $name = strtoupper(trim((string)($r[3] ?? '')));
        $shortname = strtoupper(trim((string)($r[4] ?? ''))) ?: null;
        if ($name === '') {
            $summary['errors'][] = "Section ID {$id}: name cannot be empty, skipped.";
            $summary['skipped']++;
            continue;
        }

        $check = $conn->prepare("SELECT name, shortname FROM sections WHERE id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        if (!$existing) {
            $summary['errors'][] = "Section ID {$id}: not found, skipped.";
            $summary['skipped']++;
            continue;
        }
        if ($existing['name'] === $name && $existing['shortname'] === $shortname) continue;

        $stmt = $conn->prepare("UPDATE sections SET name = ?, shortname = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $shortname, $id);
        if ($stmt->execute()) {
            $summary['sections_updated']++;
        } else {
            $summary['errors'][] = "Section ID {$id}: update failed ({$conn->error}).";
        }
    }
}

// ===== DELETE ROWS REMOVED FROM THE SHEET (only when explicitly requested) =====
if ($deleteMissing) {
    // Sections first, then departments, then divisions — bottom-up so child rows are
    // cleared before their parents are considered for deletion.
    if ($spreadsheet->sheetNameExists('Sections')) {
        $sheetIds = collectSheetIds($spreadsheet, 'Sections');
        $dbIds = [];
        $res = $conn->query("SELECT id FROM sections");
        while ($row = $res->fetch_assoc()) $dbIds[] = (int)$row['id'];
        foreach (array_diff($dbIds, $sheetIds) as $id) {
            $check = $conn->prepare("SELECT COUNT(*) AS c FROM user WHERE section_id = ?");
            $check->bind_param('i', $id);
            $check->execute();
            $count = $check->get_result()->fetch_assoc()['c'];
            if ($count > 0) {
                $summary['errors'][] = "Section ID {$id}: removed from sheet but has {$count} staff assigned — not deleted.";
                continue;
            }
            $del = $conn->prepare("DELETE FROM sections WHERE id = ?");
            $del->bind_param('i', $id);
            if ($del->execute()) {
                $summary['sections_deleted']++;
            } else {
                $summary['errors'][] = "Section ID {$id}: delete failed ({$conn->error}).";
            }
        }
    }

    if ($spreadsheet->sheetNameExists('Departments')) {
        $sheetIds = collectSheetIds($spreadsheet, 'Departments');
        $dbIds = [];
        $res = $conn->query("SELECT id FROM departments");
        while ($row = $res->fetch_assoc()) $dbIds[] = (int)$row['id'];
        foreach (array_diff($dbIds, $sheetIds) as $id) {
            $check = $conn->prepare("SELECT COUNT(*) AS c FROM user WHERE department_id = ?");
            $check->bind_param('i', $id);
            $check->execute();
            $count = $check->get_result()->fetch_assoc()['c'];
            if ($count > 0) {
                $summary['errors'][] = "Department ID {$id}: removed from sheet but has {$count} staff assigned — not deleted.";
                continue;
            }
            $del = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $del->bind_param('i', $id);
            if ($del->execute()) {
                $summary['departments_deleted']++;
            } else {
                $summary['errors'][] = "Department ID {$id}: delete failed ({$conn->error}).";
            }
        }
    }

    if ($spreadsheet->sheetNameExists('Divisions')) {
        $sheetIds = collectSheetIds($spreadsheet, 'Divisions');
        $dbIds = [];
        $res = $conn->query("SELECT id FROM divisions");
        while ($row = $res->fetch_assoc()) $dbIds[] = (int)$row['id'];
        foreach (array_diff($dbIds, $sheetIds) as $id) {
            $check = $conn->prepare("SELECT COUNT(*) AS c FROM user WHERE division_id = ?");
            $check->bind_param('i', $id);
            $check->execute();
            $count = $check->get_result()->fetch_assoc()['c'];
            if ($count > 0) {
                $summary['errors'][] = "Division ID {$id}: removed from sheet but has {$count} staff assigned — not deleted.";
                continue;
            }
            $del = $conn->prepare("DELETE FROM divisions WHERE id = ?");
            $del->bind_param('i', $id);
            if ($del->execute()) {
                $summary['divisions_deleted']++;
            } else {
                $summary['errors'][] = "Division ID {$id}: delete failed ({$conn->error}).";
            }
        }
    }
}

$summary['message'] = 'done';
respond($summary);
?>
