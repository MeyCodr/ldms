<?php
session_start();
include "../../dbconn.php";

header('Content-Type: application/json; charset=utf-8');

function skillMatrixRespondJson($data)
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($data);
    exit();
}

function skillMatrixUserCanUse()
{
    global $conn;

    if (isset($_SESSION['id']) && (!isset($_SESSION['designation']) || !isset($_SESSION['hodid']))) {
        $sessionUserId = (int) $_SESSION['id'];
        $sessionUserQuery = mysqli_query($conn, "SELECT designation, hodid FROM user WHERE id = '$sessionUserId' LIMIT 1");
        if ($sessionUserQuery && $sessionUserRow = mysqli_fetch_assoc($sessionUserQuery)) {
            $_SESSION['designation'] = $sessionUserRow['designation'];
            $_SESSION['hodid'] = $sessionUserRow['hodid'];
        }
    }

    if (!empty($_SESSION['is_sm_user']) && isset($_SESSION['fullname'])) return true;

    return isset($_SESSION['fullname'], $_SESSION['role'], $_SESSION['designation'], $_SESSION['usertype'], $_SESSION['hodid'])
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && (int) $_SESSION['hodid'] != 0
        && (
            ($_SESSION['role'] == '' && $_SESSION['usertype'] == '') ||
            ($_SESSION['role'] == 'CLERK' && $_SESSION['usertype'] == 'MAIN')
        );
}

if (!isset($_SESSION['fullname']) || !isset($_POST["action"]) || ($_SESSION['role'] != 'ADMIN' && !skillMatrixUserCanUse())) {
    skillMatrixRespondJson(array());
}

if ($_POST["action"] == "load_non_executive_staff") {
    $department = isset($_POST["department"]) ? $_POST["department"] : "ALL";
    $currentYear = (int) date('Y');
    $currentQuarter = (int) ceil(date('n') / 3);
    $output = array();

    if (skillMatrixUserCanUse()) {
        if (!isset($_SESSION['department']) || $_SESSION['department'] == '') {
            $sessionUserId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
            $departmentStmt = $conn->prepare("SELECT department FROM user WHERE id = ?");
            if ($departmentStmt) {
                $departmentStmt->bind_param("i", $sessionUserId);
                $departmentStmt->execute();
                $departmentRow = $departmentStmt->get_result()->fetch_assoc();
                if ($departmentRow) {
                    $_SESSION['department'] = $departmentRow['department'];
                }
            }
        }

        $department = isset($_SESSION['department']) ? $_SESSION['department'] : "";
        if ($department == "") {
            skillMatrixRespondJson($output);
        }
    }

    $sql = "SELECT
                u.id,
                u.staffno,
                u.staffname,
                u.designation,
                u.grade,
                u.status,
                COALESCE(dp.name, u.department) AS department,
                COALESCE(s.name, u.section) AS section,
                EXISTS (
                    SELECT 1
                    FROM skill_matrix_evaluations sme
                    WHERE sme.staffid = u.id
                    AND YEAR(sme.evaluation_date) = ?
                    AND QUARTER(sme.evaluation_date) = ?
                ) AS has_current_quarter_evaluation
            FROM user u
            LEFT JOIN departments dp ON u.department_id = dp.id
            LEFT JOIN sections s ON u.section_id = s.id
            WHERE u.designation IN (?, ?)
            AND u.status != ?";

    if ($department != "" && $department != "ALL") {
        $sql .= " AND (dp.name = ? OR u.department = ?)";
    }

    $sql .= " ORDER BY department, u.staffname";

    $stmt = $conn->prepare($sql);
    $designation1 = "NON EXECUTIVE";
    $designation2 = "CONTRACT";
    $inactiveStatus = "RESIGN";

    if ($department != "" && $department != "ALL") {
        $stmt->bind_param("iisssss", $currentYear, $currentQuarter, $designation1, $designation2, $inactiveStatus, $department, $department);
    } else {
        $stmt->bind_param("iisss", $currentYear, $currentQuarter, $designation1, $designation2, $inactiveStatus);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'RESIGN') {
            $status = '<span class="label label-pill label-danger">NOT ACTIVE</span>';
        } else {
            $status = '<span class="label label-pill label-success">ACTIVE</span>';
        }

        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'ADMIN';
        if ($row['has_current_quarter_evaluation']) {
            $action = '<div style="display: flex; gap: 6px; justify-content: center; white-space: nowrap;">';
            $action .= '<a href="evaluation-matrix.php?staffid=' . $row['id'] . '" class="btn btn-default btn-sm"><i class="fa fa-search"></i> VIEW</a>';
            if ($isAdmin || skillMatrixUserCanUse()) {
                $action .= '<a href="duplicate-matrix.php?staffid=' . $row['id'] . '" class="btn btn-warning btn-sm"><i class="fa fa-copy"></i> DUPLICATE</a>';
            }
            $action .= '</div>';
        } else if ($isAdmin || skillMatrixUserCanUse()) {
            $action = '<a href="evaluation-matrix.php?staffid=' . $row['id'] . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> FILL SKILL MATRIX</a>';
        } else {
            $action = '<span class="label label-pill label-warning">PENDING HOS</span>';
        }

        $output[] = array(
            'id' => $row['id'],
            'staffno' => $row['staffno'],
            'staffname' => $row['staffname'],
            'department' => $row['department'],
            'section' => $row['section'],
            'grade' => $row['grade'],
            'status' => $status,
            'action' => $action,
        );
    }

    skillMatrixRespondJson($output);
}

skillMatrixRespondJson(array());
?>
