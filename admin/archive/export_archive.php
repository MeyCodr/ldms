<?php
session_start();
include "../../dbconn.php";
include "archive_config.php";

if (!archiveUserCanAccess()) {
    header("Location: ../../login.php");
    exit();
}

function exportParticipantHistory($conn)
{
    // Table names are literal strings here, never derived from request input.
    $trainingReady = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'training_archive'")) > 0
        && mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'participation_archive'")) > 0;
    $ojtReady = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'ojt_archive'")) > 0
        && mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'participateojt_archive'")) > 0;

    $userid = isset($_GET['participant_id']) ? (int) $_GET['participant_id'] : 0;
    if ($userid <= 0 || (!$trainingReady && !$ojtReady)) {
        header('Content-Type: text/plain');
        die('No participant selected or no archived data found for this participant yet.');
    }

    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    if (!preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateFrom)) {
        $dateFrom = '';
    }
    if (!preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateTo)) {
        $dateTo = '';
    }

    $parts = [];
    if ($trainingReady) {
        $sql = "SELECT 'Training' AS type, t.trainingcode AS trainingcode, t.title AS title,
                    t.startdate AS startdate, t.enddate AS enddate, t.venue AS venue, p.attendance AS attendance
                FROM participation_archive p JOIN training_archive t ON p.trainingid = t.id
                WHERE p.userid = " . $userid;
        if ($dateFrom !== '') {
            $sql .= " AND t.startdate >= '" . mysqli_real_escape_string($conn, $dateFrom) . "'";
        }
        if ($dateTo !== '') {
            $sql .= " AND t.startdate <= '" . mysqli_real_escape_string($conn, $dateTo) . "'";
        }
        $parts[] = $sql;
    }
    if ($ojtReady) {
        $sql = "SELECT 'OJT' AS type, o.trainingcode AS trainingcode, o.title AS title,
                    o.startdate AS startdate, o.enddate AS enddate, o.venue AS venue, po.attendance AS attendance
                FROM participateojt_archive po JOIN ojt_archive o ON po.ojtid = o.id
                WHERE po.userid = " . $userid;
        if ($dateFrom !== '') {
            $sql .= " AND o.startdate >= '" . mysqli_real_escape_string($conn, $dateFrom) . "'";
        }
        if ($dateTo !== '') {
            $sql .= " AND o.startdate <= '" . mysqli_real_escape_string($conn, $dateTo) . "'";
        }
        $parts[] = $sql;
    }

    $sql = implode(' UNION ALL ', $parts) . ' ORDER BY startdate DESC';
    $columns = ['type', 'trainingcode', 'title', 'startdate', 'enddate', 'venue', 'attendance'];

    $filename = 'participant_' . $userid . '_training_history_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $out = fopen('php://output', 'w');
    fputcsv($out, $columns);

    $result = mysqli_query($conn, $sql, MYSQLI_USE_RESULT);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($out, $row);
        }
        mysqli_free_result($result);
    }
    fclose($out);
    exit();
}

function exportPmeIncomplete($conn)
{
    $archiveReady = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'pme_archive'")) > 0;

    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    if (!preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateFrom)) {
        $dateFrom = '';
    }
    if (!preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateTo)) {
        $dateTo = '';
    }
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    $selectCols = "hod.staffname AS boss_name, hod.staffno AS boss_staffno, hod.department AS boss_department,
                p.training_title AS training_title, p.staffname AS staff_name, p.staffno AS staff_staffno,
                p.department AS staff_department, p.from_date AS from_date, p.to_date AS to_date, p.status AS status";

    $tables = ['pme'];
    if ($archiveReady) {
        $tables[] = 'pme_archive';
    }

    $parts = [];
    foreach ($tables as $table) {
        $sql = "SELECT $selectCols FROM `$table` p LEFT JOIN user hod ON hod.id = p.hodid
                WHERE p.status NOT IN ('completed', 'approved', 'verified')";
        if ($dateFrom !== '') {
            $sql .= " AND p.from_date >= '" . mysqli_real_escape_string($conn, $dateFrom) . "'";
        }
        if ($dateTo !== '') {
            $sql .= " AND p.from_date <= '" . mysqli_real_escape_string($conn, $dateTo) . "'";
        }
        if ($keyword !== '') {
            $escapedKeyword = mysqli_real_escape_string($conn, $keyword);
            $sql .= " AND (hod.staffname LIKE '%$escapedKeyword%' OR hod.staffno LIKE '%$escapedKeyword%'
                OR p.staffname LIKE '%$escapedKeyword%' OR p.staffno LIKE '%$escapedKeyword%' OR p.training_title LIKE '%$escapedKeyword%')";
        }
        $parts[] = $sql;
    }

    $sql = implode(' UNION ALL ', $parts) . ' ORDER BY training_title ASC, boss_name ASC';
    $columns = ['boss_name', 'boss_staffno', 'boss_department', 'training_title', 'staff_name', 'staff_staffno', 'staff_department', 'from_date', 'to_date', 'status'];

    $filename = 'pme_incomplete_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $out = fopen('php://output', 'w');
    fputcsv($out, $columns);

    $result = mysqli_query($conn, $sql, MYSQLI_USE_RESULT);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($out, $row);
        }
        mysqli_free_result($result);
    }
    fclose($out);
    exit();
}

$entityKey = isset($_GET['entity']) ? $_GET['entity'] : '';
if (!isset($ARCHIVE_ENTITIES[$entityKey])) {
    header('Content-Type: text/plain');
    die('Invalid archive type.');
}

$entity = $ARCHIVE_ENTITIES[$entityKey];

if (($entity['type'] ?? 'table') === 'participant_history') {
    exportParticipantHistory($conn);
}

if (($entity['type'] ?? 'table') === 'pme_incomplete') {
    exportPmeIncomplete($conn);
}

$table = $entity['table'];       // trusted - comes from whitelist, never from request
$dateColumn = $entity['date_column'];

$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($conn, $table) . "'");
if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
    header('Content-Type: text/plain');
    die('No archived data found for this type yet.');
}

// Column names come only from SHOW COLUMNS (trusted), never from request input.
$columns = [];
$textColumns = [];
$colResult = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
while ($colRow = mysqli_fetch_assoc($colResult)) {
    $columns[] = $colRow['Field'];
    if (stripos($colRow['Type'], 'char') !== false || stripos($colRow['Type'], 'text') !== false) {
        $textColumns[] = $colRow['Field'];
    }
}

$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$where = [];
if ($dateFrom !== '' && preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateFrom)) {
    $where[] = "`$dateColumn` >= '" . mysqli_real_escape_string($conn, $dateFrom) . "'";
}
if ($dateTo !== '' && preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateTo)) {
    $where[] = "`$dateColumn` <= '" . mysqli_real_escape_string($conn, $dateTo) . "'";
}
if ($keyword !== '' && count($textColumns) > 0) {
    $escapedKeyword = mysqli_real_escape_string($conn, $keyword);
    $likeParts = [];
    foreach ($textColumns as $col) {
        $likeParts[] = "`$col` LIKE '%$escapedKeyword%'";
    }
    $where[] = '(' . implode(' OR ', $likeParts) . ')';
}
$whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

$colList = implode(',', array_map(function ($c) {
    return "`$c`";
}, $columns));
$sql = "SELECT $colList FROM `$table` $whereSql ORDER BY `$dateColumn` DESC";

$filename = $entityKey . '_archive_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

// Unbuffered query so rows stream straight to the browser instead of being
// held in PHP memory - archive tables only grow, so this is what keeps a
// multi-year export from exhausting memory or stalling on first byte.
$out = fopen('php://output', 'w');
fputcsv($out, $columns);

$result = mysqli_query($conn, $sql, MYSQLI_USE_RESULT);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($out, $row);
    }
    mysqli_free_result($result);
}
fclose($out);
exit();
