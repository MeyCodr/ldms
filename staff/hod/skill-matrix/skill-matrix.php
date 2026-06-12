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

    return isset($_SESSION['fullname'], $_SESSION['role'], $_SESSION['designation'], $_SESSION['usertype'], $_SESSION['hodid'])
        && $_SESSION['role'] == ''
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && (int) $_SESSION['hodid'] != 0
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Learning and Development Management System</title>
    <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
    <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
    <script src="../../../asset/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
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
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php if (count($records) == 0) { ?>
                    <div class="alert alert-info">No skill matrix records are waiting for approval.</div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
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
</script>
</html>
<?php
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>
