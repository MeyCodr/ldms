<?php
session_start();
include "../../../dbconn.php";

function canApproveSkillMatrix()
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

    // A department head's own hodid is intentionally left at 0 by
    // trg_departments_hod_update (self-loop guard) - it is not a signal
    // of whether they head a department. Check departments.hod_user_id
    // directly instead.
    if (isset($_SESSION['id']) && !isset($_SESSION['is_department_hod'])) {
        $sessionUserId = (int) $_SESSION['id'];
        $deptHodQuery = mysqli_query($conn, "SELECT 1 FROM departments WHERE hod_user_id = '$sessionUserId' LIMIT 1");
        $_SESSION['is_department_hod'] = ($deptHodQuery && mysqli_num_rows($deptHodQuery) > 0) ? 1 : 0;
    }

    return isset($_SESSION['fullname'], $_SESSION['role'], $_SESSION['designation'], $_SESSION['usertype'])
        && $_SESSION['role'] == ''
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && !empty($_SESSION['is_department_hod'])
        && $_SESSION['usertype'] == 'HOD';
}

if (isset($_SESSION['fullname']) && canApproveSkillMatrix()) {
    $hodId = (int) $_SESSION['id'];
    $currentYear = (int) date('Y');
    $currentQuarter = (int) ceil(date('n') / 3);
    $records = array();

    $stmt = $conn->prepare("SELECT
                                sme.id,
                                sme.evaluation_date,
                                sme.approval_status,
                                target.staffno AS target_staffno,
                                target.staffname AS target_staffname,
                                target.department AS target_department,
                                target.section AS target_section,
                                creator.staffname AS created_by_name
                            FROM skill_matrix_evaluations sme
                            INNER JOIN user target ON target.id = sme.staffid
                            INNER JOIN user creator ON creator.id = sme.created_by
                            WHERE creator.hodid = ?
                            AND (
                                (
                                    creator.designation = ?
                                    AND (
                                        (creator.roletype = '' AND creator.usertype = '')
                                        OR (creator.roletype = 'CLERK' AND creator.usertype = 'MAIN')
                                    )
                                )
                                OR EXISTS (SELECT 1 FROM skill_matrix_whitelist w WHERE w.staffno = creator.staffno COLLATE utf8mb4_0900_ai_ci)
                            )
                            AND sme.approval_status IS NOT NULL
                            AND YEAR(sme.evaluation_date) = ?
                            AND QUARTER(sme.evaluation_date) = ?
                            ORDER BY FIELD(sme.approval_status, 'PENDING', 'APPROVED'), sme.evaluation_date DESC, target.staffname");
    $creatorDesignation = "MANAGER (AM/HOS & ABOVE)";
    $stmt->bind_param("isii", $hodId, $creatorDesignation, $currentYear, $currentQuarter);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // Department-wide overview (Total Staff / Completion Rate / Approved /
    // Waiting Approval / Draft / Not Submitted) - a different population
    // than $records above. $records only covers evaluations that were
    // actually submitted (approval_status IS NOT NULL) by creators reporting
    // to this HOD, so it has no way to know about staff with a draft or no
    // evaluation at all this quarter. This mirrors the same status-derivation
    // logic as admin/skill-matrix/fetch_skill_matrix.php's
    // load_non_executive_staff (APPROVED / PENDING / has-eval-but-no-status
    // = DRAFT / no-eval-at-all = NOT SUBMITTED), but scoped to the
    // department(s) this HOD actually heads rather than resolved from a
    // session var, since this page is reached by the HOD directly.
    $hodDepartments = array();
    $deptNameStmt = $conn->prepare("SELECT name FROM departments WHERE hod_user_id = ?");
    $deptNameStmt->bind_param("i", $hodId);
    $deptNameStmt->execute();
    $deptNameResult = $deptNameStmt->get_result();
    while ($deptRow = $deptNameResult->fetch_assoc()) {
        $hodDepartments[] = $deptRow['name'];
    }

    $overview = array('total' => 0, 'approved' => 0, 'waiting' => 0, 'draft' => 0, 'not_submitted' => 0);

    if (count($hodDepartments) > 0) {
        $placeholders = implode(',', array_fill(0, count($hodDepartments), '?'));
        $overviewSql = "SELECT
                            (
                                SELECT sme.approval_status
                                FROM skill_matrix_evaluations sme
                                WHERE sme.staffid = u.id
                                AND YEAR(sme.evaluation_date) = ?
                                AND QUARTER(sme.evaluation_date) = ?
                                ORDER BY sme.evaluation_date DESC, sme.id DESC
                                LIMIT 1
                            ) AS approval_status,
                            EXISTS (
                                SELECT 1 FROM skill_matrix_evaluations sme
                                WHERE sme.staffid = u.id
                                AND YEAR(sme.evaluation_date) = ?
                                AND QUARTER(sme.evaluation_date) = ?
                            ) AS has_current_quarter_evaluation
                        FROM user u
                        LEFT JOIN departments dp ON u.department_id = dp.id
                        WHERE u.designation IN ('NON EXECUTIVE', 'CONTRACT')
                        AND u.status != 'RESIGN'
                        AND (dp.name IN ($placeholders) OR u.department IN ($placeholders))";
        // Two separate IN() clauses rather than COALESCE(dp.name, u.department)
        // IN (...) - dp.name and u.department carry different collations in
        // this schema, and COALESCE-ing them together throws "Illegal mix of
        // collations". Comparing each column against the bound params
        // separately (same pattern as admin/skill-matrix/fetch_skill_matrix.php)
        // avoids that, so each department name is bound twice, once per side.
        $overviewTypes = 'iiii' . str_repeat('s', count($hodDepartments) * 2);
        $overviewParams = array_merge([$currentYear, $currentQuarter, $currentYear, $currentQuarter], $hodDepartments, $hodDepartments);
        $overviewStmt = $conn->prepare($overviewSql);
        $overviewStmt->bind_param($overviewTypes, ...$overviewParams);
        $overviewStmt->execute();
        $overviewResult = $overviewStmt->get_result();

        while ($orow = $overviewResult->fetch_assoc()) {
            $overview['total']++;
            if ($orow['approval_status'] == 'APPROVED') {
                $overview['approved']++;
            } else if ($orow['approval_status'] == 'PENDING') {
                $overview['waiting']++;
            } else if ($orow['has_current_quarter_evaluation']) {
                $overview['draft']++;
            } else {
                $overview['not_submitted']++;
            }
        }
    }

    $overviewCompletionRate = $overview['total'] > 0 ? round(($overview['approved'] / $overview['total']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Learning and Development Management System</title>
    <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
    <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
    <script src="../../../asset/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    <style>
        :root {
            --status-good: #0ca30c;
            --status-warning: #fab219;
            --status-serious: #ec835a;
            --status-critical: #d03b3b;
        }

        .dash-tile {
            background: #fcfcfb;
            border: 1px solid #e1e0d9;
            border-left: 4px solid #c3c2b7;
            border-radius: 4px;
            padding: 12px 10px;
            text-align: center;
            margin-bottom: 15px;
        }

        .dash-tile-value {
            font-size: 26px;
            font-weight: bold;
            color: #0b0b0b;
            line-height: 1.2;
        }

        .dash-tile-label {
            font-size: 11px;
            font-weight: bold;
            color: #52514e;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 4px;
        }
    </style>
</head>
<body onload="startTime()" style="background-image:url('../../../asset/image/bg-try.png');zoom: 75%;">
    <br>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10">
                <img src="../../../asset/image/lndlogo.gif" height="50" width="290">
            </div>
            <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;"></div>
        </div>
        <nav class="navbar navbar-inverse">
            <div class="container-fluid ">
                <ul class="nav navbar-nav">
                    <li><a href="../dashboard.php">HOME</a></li>
                    <li><a href="../attendance/training.php">MY TRAINING</a></li>
                    <li><a href="../pme/pme.php">PME</a></li>
                    <li><a href="../tna/staff_list.php">TNA</a></li>
                    <li class="active"><a href="skill-matrix.php">SKILL MATRIX</a></li>
                    <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['fullname']; ?></a>
                        <ul class="dropdown-menu">
                            <li><a href="../../../logout.php">LOGOUT</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8" style="margin-top: 10px;">
                        <strong>Skill Matrix - Q<?php echo $currentQuarter; ?> <?php echo $currentYear; ?></strong>
                    </div>
                    <div class="col-md-4" align="right">
                        <a href="matrix-chart.php" class="btn btn-info btn-md">
                            <i class="fa fa-chart-bar"></i> Matrix Chart
                        </a>
                        <a href="export_skill_matrix_list.php" class="btn btn-success btn-md">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php if (count($records) == 0) { ?>
                    <div class="alert alert-info">No skill matrix records are waiting for approval.</div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table id="skillMatrixTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Staff No.</th>
                                    <th>Staff Name</th>
                                    <th>Department</th>
                                    <th>Section</th>
                                    <th>Filled By</th>
                                    <th>Approval Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $index => $record) { ?>
                                    <tr>
                                        <td class="text-center"><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($record['target_staffno']); ?></td>
                                        <td><?php echo htmlspecialchars($record['target_staffname']); ?></td>
                                        <td><?php echo htmlspecialchars($record['target_department']); ?></td>
                                        <td><?php echo htmlspecialchars($record['target_section']); ?></td>
                                        <td><?php echo htmlspecialchars($record['created_by_name']); ?></td>
                                        <td class="text-center">
                                            <?php if ($record['approval_status'] == 'APPROVED') { ?>
                                                <span class="label label-success">APPROVED</span>
                                            <?php } else { ?>
                                                <span class="label label-warning">WAITING APPROVAL</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="evaluation-matrix.php?evaluation_id=<?php echo (int) $record['id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fa fa-search"></i> VIEW
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Skill Matrix Overview - Q<?php echo $currentQuarter; ?> <?php echo $currentYear; ?></strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile">
                            <div class="dash-tile-value"><?php echo $overview['total']; ?></div>
                            <div class="dash-tile-label">Total Staff</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile" style="border-left-color: var(--status-good);">
                            <div class="dash-tile-value"><?php echo $overviewCompletionRate; ?>%</div>
                            <div class="dash-tile-label">Completion Rate</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile" style="border-left-color: var(--status-good);">
                            <div class="dash-tile-value"><?php echo $overview['approved']; ?></div>
                            <div class="dash-tile-label">Approved</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile" style="border-left-color: var(--status-warning);">
                            <div class="dash-tile-value"><?php echo $overview['waiting']; ?></div>
                            <div class="dash-tile-label">Waiting Approval</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile" style="border-left-color: var(--status-serious);">
                            <div class="dash-tile-value"><?php echo $overview['draft']; ?></div>
                            <div class="dash-tile-label">Draft</div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="dash-tile" style="border-left-color: var(--status-critical);">
                            <div class="dash-tile-value"><?php echo $overview['not_submitted']; ?></div>
                            <div class="dash-tile-label">Not Submitted</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
<script>
    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
        setTimeout(startTime, 500);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    $(document).ready(function () {
        $('#skillMatrixTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "pageLength": 10,
            "columnDefs": [
                { className: 'text-center', targets: [0, 6, 7] },
                { orderable: false, targets: [7] }
            ]
        });
    });
</script>
</html>
<?php
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>
