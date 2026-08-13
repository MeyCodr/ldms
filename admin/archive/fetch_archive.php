<?php
session_start();
include "../../dbconn.php";
include "archive_config.php";

header('Content-Type: application/json; charset=utf-8');

function archiveRespond($data)
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($data);
    exit();
}

function archiveBindParams($stmt, $types, $params)
{
    $refs = [$types];
    foreach ($params as $key => $value) {
        $refs[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

if (!archiveUserCanAccess()) {
    archiveRespond(['error' => 'unauthorized']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$entityKey = isset($_POST['entity']) ? $_POST['entity'] : '';

if (!isset($ARCHIVE_ENTITIES[$entityKey])) {
    archiveRespond(['error' => 'invalid_entity']);
}

$entity = $ARCHIVE_ENTITIES[$entityKey];
$table = $entity['table'];       // trusted - comes from whitelist, never from request
$dateColumn = $entity['date_column'];

// The archive table only exists once scripts/archive_2025_and_below.php --execute has run.
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($conn, $table) . "'");
$tableExists = $tableCheck && mysqli_num_rows($tableCheck) > 0;

if (!$tableExists) {
    if ($action === 'get_columns') {
        archiveRespond(['exists' => false, 'columns' => []]);
    }
    archiveRespond([
        'draw' => isset($_POST['draw']) ? (int) $_POST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'columns' => [],
        'data' => [],
        'exists' => false,
    ]);
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

if ($action === 'get_columns') {
    archiveRespond(['exists' => true, 'columns' => $columns, 'date_column' => $dateColumn]);
}

if ($action === 'list') {
    $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int) $_POST['length'] : 25;
    if ($length <= 0 || $length > 500) {
        $length = 25;
    }
    $draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 0;
    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
    $dateFrom = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
    $dateTo = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

    $where = [];
    $types = '';
    $params = [];

    if ($dateFrom !== '' && preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateFrom)) {
        $where[] = "`$dateColumn` >= ?";
        $types .= 's';
        $params[] = $dateFrom;
    }
    if ($dateTo !== '' && preg_match('/^\d{4}(-\d{2}-\d{2})?$/', $dateTo)) {
        $where[] = "`$dateColumn` <= ?";
        $types .= 's';
        $params[] = $dateTo;
    }
    if ($keyword !== '' && count($textColumns) > 0) {
        $likeParts = [];
        foreach ($textColumns as $col) {
            $likeParts[] = "`$col` LIKE ?";
            $types .= 's';
            $params[] = '%' . $keyword . '%';
        }
        $where[] = '(' . implode(' OR ', $likeParts) . ')';
    }

    $whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

    $totalRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$table`"));
    $recordsTotal = (int) $totalRow['c'];

    if (count($params) > 0) {
        $countStmt = $conn->prepare("SELECT COUNT(*) AS c FROM `$table` $whereSql");
        archiveBindParams($countStmt, $types, $params);
        $countStmt->execute();
        $recordsFiltered = (int) $countStmt->get_result()->fetch_assoc()['c'];
    } else {
        $recordsFiltered = $recordsTotal;
    }

    $colList = implode(',', array_map(function ($c) {
        return "`$c`";
    }, $columns));

    $dataSql = "SELECT $colList FROM `$table` $whereSql ORDER BY `$dateColumn` DESC LIMIT ?, ?";
    $dataTypes = $types . 'ii';
    $dataParams = $params;
    $dataParams[] = $start;
    $dataParams[] = $length;

    $dataStmt = $conn->prepare($dataSql);
    archiveBindParams($dataStmt, $dataTypes, $dataParams);
    $dataStmt->execute();
    $dataResult = $dataStmt->get_result();

    $rows = [];
    while ($row = $dataResult->fetch_assoc()) {
        $rows[] = $row;
    }

    archiveRespond([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'columns' => $columns,
        'data' => $rows,
        'exists' => true,
    ]);
}

archiveRespond(['error' => 'invalid_action']);
