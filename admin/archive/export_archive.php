<?php
session_start();
include "../../dbconn.php";
include "archive_config.php";

if (!archiveUserCanAccess()) {
    header("Location: ../../login.php");
    exit();
}

$entityKey = isset($_GET['entity']) ? $_GET['entity'] : '';
if (!isset($ARCHIVE_ENTITIES[$entityKey])) {
    header('Content-Type: text/plain');
    die('Invalid archive type.');
}

$entity = $ARCHIVE_ENTITIES[$entityKey];
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
