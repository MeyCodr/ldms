<?php
session_start();
include "../../dbconn.php";

if (isset($_SESSION['id']) && (!isset($_SESSION['designation']) || !isset($_SESSION['hodid']))) {
    $sessionUserId = (int) $_SESSION['id'];
    $sessionUserQuery = mysqli_query($conn, "SELECT designation, hodid FROM user WHERE id = '$sessionUserId' LIMIT 1");
    if ($sessionUserQuery && $sessionUserRow = mysqli_fetch_assoc($sessionUserQuery)) {
        $_SESSION['designation'] = $sessionUserRow['designation'];
        $_SESSION['hodid'] = $sessionUserRow['hodid'];
    }
}

$canUseSkillMatrix = (
    !empty($_SESSION['is_sm_user']) && isset($_SESSION['fullname'])
) || (
    isset($_SESSION['fullname'], $_SESSION['role'], $_SESSION['designation'], $_SESSION['usertype'], $_SESSION['hodid'])
    && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
    && (int) $_SESSION['hodid'] != 0
    && (
        ($_SESSION['role'] == '' && $_SESSION['usertype'] == '') ||
        ($_SESSION['role'] == 'CLERK' && $_SESSION['usertype'] == 'MAIN')
    )
);
$isClerkMatrixUser = $canUseSkillMatrix && $_SESSION['role'] == 'CLERK';

if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN' || $canUseSkillMatrix)) {

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Learning and Development Management System</title>
        <script src="../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../asset/css/bootstrap.min.css" />
        <script src="../../asset/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
        <script src="../../asset/js/chartjs-plugin-labels.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
        <link rel="stylesheet" href="../../asset/css/datepicker.css">
        <script src="../../asset/js/bootstrap-datepicker1.js"></script>
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css" />
        <script type="text/javascript"
            src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js">
            </script>
    </head>

    <body onload="startTime()" style="background-image:url('../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <img src="../../asset/image/lndlogo.gif" height="50" width="290">
                </div>
                <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

                </div>
            </div>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid ">
                    <ul class="nav navbar-nav">
                        <?php if ($canUseSkillMatrix) { ?>
                            <?php if ($isClerkMatrixUser) { ?>
                                <li><a href="../../clerk/main/dashboard.php">HOME</a></li>
                                <li><a href="../../clerk/main/staff/staff.php">CONTRACT STAFF LIST</a></li>
                                <li><a href="../../clerk/main/training/training_ojt.php">ALL TRAINING</a></li>
                                <li><a href="../../clerk/main/attendance/training.php">MY TRAINING</a></li>
                                <li><a href="../../clerk/main/tna/tna_list.php">TNA LIST</a></li>
                            <?php } else { ?>
                                <li><a href="../../staff/office/dashboard.php">HOME</a></li>
                                <li><a href="../../staff/office/attendance/training.php">MY TRAINING</a></li>
                                <li><a href="../../staff/office/tna/tna.php">TNA</a></li>
                            <?php } ?>
                            <li class="active"><a href="skill-matrix.php">SKILL MATRIX</a></li>
                            <?php if ($isClerkMatrixUser) { ?>
                                <li><a href="../../clerk/main/password/password.php">CHANGE PASSWORD</a></li>
                            <?php } else { ?>
                                <li><a href="../../staff/office/password/password.php">CHANGE PASSWORD</a></li>
                            <?php } ?>
                        <?php } else { ?>
                            <li><a href="../dashboard.php">HOME</a></li>
                            <li><a href="../staff/staff.php">STAFF LIST</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                        class="label label-pill label-danger count"></span> ALL TRAINING </a>
                                <ul class="dropdown-menu">
                                    <li><a href="../training/public/training.php">PUBLIC/INHOUSE</a></li>
                                    <li><a href="../training/ojt/training_ojt.php">OJT</a></li>
                                    <li><a href="../training/departmental/training_dept.php">DEPARTMENTAL</a></li>
                                </ul>
                            </li>
                            <li><a href="../attendance/training.php">MY TRAINING</a></li>
                            <li><a href="../tna/tna_list.php">TNA LIST</a></li>
                            <li><a href="../tni/tni_list.php">TNI LIST</a></li>
                            <li><a href="../tna/tna_summary.php">TNA SUMMARY</a></li>
                            <li class="active"><a href="skill-matrix.php">SKILL MATRIX</a></li>
                            <li><a href="../organization/org.php">ORGANIZATION</a></li>
                        <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
                        <?php } ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="../../logout.php">LOGOUT</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-8" style="margin-top: 10px;">
                                    <strong>Department Filter</strong>
                                </div>
                                <?php if (!$canUseSkillMatrix) { ?>
                                    <div class="col-md-4" align="right">
                                        <a href="#" id="matrix_chart_btn" class="btn btn-info btn-md disabled" style="pointer-events: none; opacity: 0.65;">
                                            <i class="fa fa-chart-bar"></i> Matrix Chart
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="department" id="department" class="form-control"></select>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group">
                                        <button type="button" name="filter_dept" id="filter_dept"
                                            class="btn btn-info btn-md">FILTER <i class="fa fa-search"></i></button>
                                        <button type="button" name="reset_filter" id="reset_filter"
                                            class="btn btn-default btn-md">RESET</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-12" style="margin-top: 10px;">
                                    <strong>Staff List</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <table id="skillmatrixlist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Staff No.</th>
                                                <th>Employee Name</th>
                                                <th>Department</th>
                                                <th>Section</th>
                                                <th>Grade</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
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

        var skillMatrixTable = $('#skillmatrixlist').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "responsive": true,
            "pageLength": 10,
            "info": true,
            "ajax": {
                url: "fetch_skill_matrix.php",
                type: "POST",
                dataSrc: '',
                data: function (data) {
                    data.action = "load_non_executive_staff";
                    data.department = $('#department').val() || "ALL";
                }
            },
            "columns": [
                {
                    "data": null
                },
                {
                    "data": "staffno"
                },
                {
                    "data": "staffname"
                },
                {
                    "data": "department"
                },
                {
                    "data": "section"
                },
                {
                    "data": "grade"
                },
                {
                    "data": "status"
                },
                {
                    "data": "action"
                }
            ],
            "columnDefs": [{
                className: 'text-center',
                targets: [0, 1, 5, 6, 7]
            },
            {
                orderable: false,
                searchable: false,
                targets: [0, 7]
            }],
            "drawCallback": function () {
                var api = this.api();
                var pageInfo = api.page.info();

                api.column(0, {
                    page: 'current'
                }).nodes().each(function (cell, index) {
                    cell.innerHTML = pageInfo.start + index + 1;
                });
            },
            "language": {
                "emptyTable": "No non executive staff records found."
            }
        });

        $(function () {
            $.post("../tna/fetch_tna.php", {
                action: "load_department"
            }, function (data) {
                $('#department').html(data);
                disableMatrixChartButton();
            });
        });

        function disableMatrixChartButton() {
            $('#matrix_chart_btn')
                .attr('href', '#')
                .addClass('disabled')
                .css({
                    'pointer-events': 'none',
                    'opacity': '0.65'
                });
        }

        function enableMatrixChartButton(department) {
            $('#matrix_chart_btn')
                .attr('href', 'matrix-chart.php?department=' + encodeURIComponent(department))
                .removeClass('disabled')
                .css({
                    'pointer-events': 'auto',
                    'opacity': '1'
                });
        }

        $('#filter_dept').click(function () {
            var department = $('#department').val();

            if (!department || department == 'ALL') {
                disableMatrixChartButton();
                swal("Department required", "Please select and filter a department first.", "warning");
                return;
            }

            enableMatrixChartButton(department);
            skillMatrixTable.ajax.reload();
        });

        $('#department').change(function () {
            disableMatrixChartButton();
        });

        $('#reset_filter').click(function () {
            $('#department').val('ALL');
            disableMatrixChartButton();
            skillMatrixTable.ajax.reload();
        });

    </script>

    </html>
    <?php
} else {
    header("Location: ../../login.php");
    exit();
}
?>
