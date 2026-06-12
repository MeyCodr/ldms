<?php
session_start();
include "../../dbconn.php";

header('Content-Type: application/json; charset=utf-8');

function respond($data)
{
    if (ob_get_length()) ob_clean();
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN' || !isset($_POST['btn_action'])) {
    respond(['message' => 'error', 'detail' => 'Unauthorized']);
}

$action = $_POST['btn_action'];

// ===== DIVISIONS =====

if ($action == 'add_division') {
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if ($name === '') respond(['message' => 'error', 'detail' => 'Division name is required.']);
    $stmt = $conn->prepare("INSERT INTO divisions (name, shortname) VALUES (?, ?)");
    $stmt->bind_param('ss', $name, $shortname);
    respond($stmt->execute() ? ['message' => 'insert'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'rename_division') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if (!$id || $name === '') respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $stmt = $conn->prepare("UPDATE divisions SET name = ?, shortname = ? WHERE id = ?");
    $stmt->bind_param('ssi', $name, $shortname, $id);
    respond($stmt->execute() ? ['message' => 'update'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'delete_division') {
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $check = $conn->prepare("SELECT COUNT(*) FROM user WHERE division_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();
    if ($count > 0) {
        respond(['message' => 'error', 'detail' => "Cannot delete: $count staff member(s) are still assigned to this division."]);
    }
    $stmt = $conn->prepare("DELETE FROM divisions WHERE id = ?");
    $stmt->bind_param('i', $id);
    respond($stmt->execute() ? ['message' => 'delete'] : ['message' => 'error', 'detail' => $conn->error]);
}

// ===== DEPARTMENTS =====

if ($action == 'add_department') {
    $division_id = (int) ($_POST['division_id'] ?? 0);
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if (!$division_id || $name === '') respond(['message' => 'error', 'detail' => 'Division and department name are required.']);
    $stmt = $conn->prepare("INSERT INTO departments (division_id, name, shortname) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $division_id, $name, $shortname);
    respond($stmt->execute() ? ['message' => 'insert'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'rename_department') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if (!$id || $name === '') respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $stmt = $conn->prepare("UPDATE departments SET name = ?, shortname = ? WHERE id = ?");
    $stmt->bind_param('ssi', $name, $shortname, $id);
    respond($stmt->execute() ? ['message' => 'update'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'move_department') {
    $id = (int) ($_POST['id'] ?? 0);
    $new_division_id = (int) ($_POST['division_id'] ?? 0);
    if (!$id || !$new_division_id) respond(['message' => 'error', 'detail' => 'Invalid input.']);

    $divRow = $conn->query("SELECT name FROM divisions WHERE id = $new_division_id")->fetch_assoc();
    if (!$divRow) respond(['message' => 'error', 'detail' => 'Target division not found.']);
    $newDivName = $divRow['name'];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE departments SET division_id = ? WHERE id = ?");
        $stmt1->bind_param('ii', $new_division_id, $id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE user SET division = ?, division_id = ? WHERE department_id = ?");
        $stmt2->bind_param('sii', $newDivName, $new_division_id, $id);
        $stmt2->execute();

        $conn->commit();
        respond(['message' => 'update']);
    } catch (Exception $e) {
        $conn->rollback();
        respond(['message' => 'error', 'detail' => $e->getMessage()]);
    }
}

if ($action == 'delete_department') {
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $check = $conn->prepare("SELECT COUNT(*) FROM user WHERE department_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();
    if ($count > 0) {
        respond(['message' => 'error', 'detail' => "Cannot delete: $count staff member(s) are still assigned to this department."]);
    }
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param('i', $id);
    respond($stmt->execute() ? ['message' => 'delete'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'assign_hod') {
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $hod_user_id = (isset($_POST['hod_user_id']) && $_POST['hod_user_id'] !== '') ? (int) $_POST['hod_user_id'] : null;
    if (!$department_id) respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $stmt = $conn->prepare("UPDATE departments SET hod_user_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $hod_user_id, $department_id);
    respond($stmt->execute() ? ['message' => 'update'] : ['message' => 'error', 'detail' => $conn->error]);
}

// ===== SECTIONS =====

if ($action == 'add_section') {
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if (!$department_id || $name === '') respond(['message' => 'error', 'detail' => 'Department and section name are required.']);
    $stmt = $conn->prepare("INSERT INTO sections (department_id, name, shortname) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $department_id, $name, $shortname);
    respond($stmt->execute() ? ['message' => 'insert'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'rename_section') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = strtoupper(trim($_POST['name'] ?? ''));
    $shortname = strtoupper(trim($_POST['shortname'] ?? '')) ?: null;
    if (!$id || $name === '') respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $stmt = $conn->prepare("UPDATE sections SET name = ?, shortname = ? WHERE id = ?");
    $stmt->bind_param('ssi', $name, $shortname, $id);
    respond($stmt->execute() ? ['message' => 'update'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'move_section') {
    $id = (int) ($_POST['id'] ?? 0);
    $new_department_id = (int) ($_POST['department_id'] ?? 0);
    if (!$id || !$new_department_id) respond(['message' => 'error', 'detail' => 'Invalid input.']);

    $deptRow = $conn->query(
        "SELECT dp.id, dp.name, dv.id AS div_id, dv.name AS div_name
         FROM departments dp
         JOIN divisions dv ON dv.id = dp.division_id
         WHERE dp.id = $new_department_id"
    )->fetch_assoc();
    if (!$deptRow) respond(['message' => 'error', 'detail' => 'Target department not found.']);

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE sections SET department_id = ? WHERE id = ?");
        $stmt1->bind_param('ii', $new_department_id, $id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE user SET department = ?, department_id = ?, division = ?, division_id = ? WHERE section_id = ?");
        $stmt2->bind_param('sisii', $deptRow['name'], $new_department_id, $deptRow['div_name'], $deptRow['div_id'], $id);
        $stmt2->execute();

        $conn->commit();
        respond(['message' => 'update']);
    } catch (Exception $e) {
        $conn->rollback();
        respond(['message' => 'error', 'detail' => $e->getMessage()]);
    }
}

if ($action == 'delete_section') {
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) respond(['message' => 'error', 'detail' => 'Invalid input.']);
    $check = $conn->prepare("SELECT COUNT(*) FROM user WHERE section_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();
    if ($count > 0) {
        respond(['message' => 'error', 'detail' => "Cannot delete: $count staff member(s) are still assigned to this section."]);
    }
    $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
    $stmt->bind_param('i', $id);
    respond($stmt->execute() ? ['message' => 'delete'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'transfer_staff') {
    $staff_ids = isset($_POST['staff_ids']) ? $_POST['staff_ids'] : [];
    $new_department_id = (int) ($_POST['department_id'] ?? 0);
    $new_section_id = (isset($_POST['section_id']) && $_POST['section_id'] !== '') ? (int) $_POST['section_id'] : null;

    if (empty($staff_ids) || !$new_department_id) {
        respond(['message' => 'error', 'detail' => 'Please select staff and a target department.']);
    }

    $ids = array_filter(array_map('intval', (array) $staff_ids));
    if (empty($ids)) respond(['message' => 'error', 'detail' => 'No valid staff selected.']);

    $deptRow = $conn->query(
        "SELECT dp.id, dp.name, dp.hod_user_id, dv.id AS div_id, dv.name AS div_name
         FROM departments dp JOIN divisions dv ON dv.id = dp.division_id
         WHERE dp.id = $new_department_id"
    )->fetch_assoc();
    if (!$deptRow) respond(['message' => 'error', 'detail' => 'Target department not found.']);

    $sectionName = '';
    if ($new_section_id) {
        $sectRow = $conn->query("SELECT name FROM sections WHERE id = $new_section_id")->fetch_assoc();
        if ($sectRow) $sectionName = $sectRow['name'];
        else $new_section_id = null;
    }

    $hodid = $deptRow['hod_user_id'] ? (int) $deptRow['hod_user_id'] : 0;
    $idList = implode(',', $ids);

    $conn->begin_transaction();
    try {
        if ($new_section_id) {
            $sql = "UPDATE user SET department = ?, department_id = ?, division = ?, division_id = ?, section = ?, section_id = ?, hodid = ? WHERE id IN ($idList)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sissisi', $deptRow['name'], $new_department_id, $deptRow['div_name'], $deptRow['div_id'], $sectionName, $new_section_id, $hodid);
        } else {
            $sql = "UPDATE user SET department = ?, department_id = ?, division = ?, division_id = ?, section = '', section_id = NULL, hodid = ? WHERE id IN ($idList)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sisii', $deptRow['name'], $new_department_id, $deptRow['div_name'], $deptRow['div_id'], $hodid);
        }
        $stmt->execute();
        $conn->commit();
        respond(['message' => 'transfer', 'count' => count($ids)]);
    } catch (Exception $e) {
        $conn->rollback();
        respond(['message' => 'error', 'detail' => $e->getMessage()]);
    }
}

respond(['message' => 'error', 'detail' => 'Unknown action.']);
?>
