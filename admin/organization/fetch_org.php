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

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    respond([]);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'load_divisions') {
    $sql = "SELECT d.id, d.name, d.shortname,
                   COUNT(DISTINCT dp.id) AS dept_count,
                   COUNT(DISTINCT u.id) AS user_count
            FROM divisions d
            LEFT JOIN departments dp ON dp.division_id = d.id
            LEFT JOIN user u ON u.division_id = d.id AND u.status != 'RESIGN'
            GROUP BY d.id
            ORDER BY d.name";
    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

if ($action == 'load_departments') {
    $division_id = isset($_POST['division_id']) ? (int) $_POST['division_id'] : 0;
    if (!$division_id) respond([]);

    $sql = "SELECT dp.id, dp.name, dp.shortname, dp.hod_user_id,
                   u.staffname AS hod_name, u.staffno AS hod_staffno,
                   COUNT(DISTINCT s.id) AS section_count,
                   COUNT(DISTINCT staff.id) AS user_count
            FROM departments dp
            LEFT JOIN user u ON u.id = dp.hod_user_id
            LEFT JOIN sections s ON s.department_id = dp.id
            LEFT JOIN user staff ON staff.department_id = dp.id AND staff.status != 'RESIGN'
            WHERE dp.division_id = ?
            GROUP BY dp.id
            ORDER BY dp.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $division_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

if ($action == 'load_sections') {
    $department_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
    if (!$department_id) respond([]);

    $sql = "SELECT s.id, s.name, s.shortname,
                   COUNT(u.id) AS user_count
            FROM sections s
            LEFT JOIN user u ON u.section_id = s.id AND u.status != 'RESIGN'
            WHERE s.department_id = ?
            GROUP BY s.id
            ORDER BY s.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

if ($action == 'load_managers') {
    $department_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
    if (!$department_id) respond([]);

    $sql = "SELECT id, staffno, staffname
            FROM user
            WHERE department_id = ? AND designation = 'MANAGER (AM/HOS & ABOVE)' AND status != 'RESIGN'
            ORDER BY staffname";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

if ($action == 'load_staff_in_dept') {
    $department_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
    if (!$department_id) respond([]);

    $sql = "SELECT u.id, u.staffno, u.staffname, u.designation, u.grade,
                   COALESCE(s.name, u.section) AS section_name
            FROM user u
            LEFT JOIN sections s ON s.id = u.section_id
            WHERE u.department_id = ? AND u.status != 'RESIGN'
            ORDER BY u.staffname";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

if ($action == 'load_all_departments') {
    $sql = "SELECT dp.id, dp.name, dv.name AS division_name
            FROM departments dp
            JOIN divisions dv ON dv.id = dp.division_id
            ORDER BY dv.name, dp.name";
    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    respond($rows);
}

respond([]);
?>
