<?php
session_start();
include "../../../../dbconn.php";

if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

    ?>
    <!DOCTYPE html>

    <html lang="en">

    <head>
        <title>Learning and Development Management System</title>

        <!-- <script src="../../../asset/js/jquery-1.10.2.min.js"></script> -->
        <script src="../../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../../asset/css/bootstrap.min.css" />
        <script src="../../../../asset/js/bootstrap.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="../../../../asset/css/datepicker.css">
        <script src="../../../../asset/js/bootstrap-datepicker1.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css" />
        <script type="text/javascript"
            src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>

        <script>
            function startTime() {
                var today = new Date();
                var h = today.getHours();
                var m = today.getMinutes();
                var s = today.getSeconds();
                // add a zero in front of numbers<10
                h = checkTime(h);
                m = checkTime(m);
                s = checkTime(s);
                document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
                t = setTimeout(function () { startTime() }, 500);
            }

            function checkTime(i) {
                if (i < 10) {
                    i = "0" + i;
                }
                return i;
            }

        </script>
    </head>

    <style>
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        }

        .button-container button {
            width: calc(50% - 5px);
            /* Ensures 2 buttons per row */
        }

        .right-buttons {
            display: flex;
            justify-content: flex-end;
            /* Move buttons to the right */
            gap: 20px;
            /* Add space between buttons */
        }

        .right-buttons button {
            min-width: 180px;
            /* Ensure buttons have consistent width */
        }
    </style>

    <body onload="startTime()" style="background-image:url('../../../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <img src="../../../../asset/image/lndlogo.gif" height="50" width="290">
                </div>
                <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

                </div>
            </div>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid ">
                    <ul class="nav navbar-nav">
                        <li><a href="../../../dashboard.php">HOME</a></li>
                        <li><a href="../../../staff/staff.php">STAFF LIST</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> ALL TRAINING </a>
                            <ul class="dropdown-menu">
                                <li><a href="../training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="../../ojt/training_ojt.php">OJT</a></li>
                                <li><a href="../../departmental/training_dept.php">DEPARTMENTAL</a></li>
                            </ul>
                        </li>
                        <li><a href="../../../attendance/training.php">MY TRAINING</a></li>
                        <li><a href="../../../tna/tna_list.php">TNA</a></li>
                        <li><a href="../../../tni/tni_list.php">TNI</a></li>
                        <li><a href="../../../tna/tna_summary.php">TNA SUMMARY</a></li>
                        <li><a href="../../../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="../../../password/password.php">CHANGE PASSWORD</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="../../../../logout.php">LOGOUT</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row align-items-center">
                                <div class="col-md-7" style="margin-top: 10px;">
                                    <strong>All Training \ PUBLIC/INHOUSE \ <a href="certificate.php">TRAINING
                                            CERTIFICATE</a></strong>
                                </div>
                                <div class="col-md-5">
                                    <div class="right-buttons">
                                        <button type="button" name="remove_cert" id="remove_cert"
                                            class="btn btn-danger btn-md">
                                            Delete Certificate <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" name="download_cert" id="download_cert"
                                            class="btn btn-info btn-md">
                                            Download Certificate <i class="far fa-arrow-alt-circle-down"></i>
                                        </button>
                                        <button type="button" name="importcert" id="importcert" data-toggle="modal"
                                            data-target="#certupload" class="btn btn-success btn-xm"><i
                                                class="fa fa-plus"></i> Upload Certificate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <!-- <img src="../../../../asset/image/lndlogo.gif" class="img-fluid" alt="...">
                                      -->
                                    <?php
                                    // Fetch certificates from DB
                                    $sql = "SELECT file_name FROM certificate WHERE adminid = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $_SESSION['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = $result->fetch_assoc()) {
                                        $filePath = "../../../../asset/certificates/" . $row['file_name'];
                                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                                        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                                            echo "<img src='$filePath' alt='Certificate' margin:10px;'>";
                                        } elseif ($ext === 'pdf') {
                                            echo "<embed src='$filePath' type='application/pdf' width='500' height='600'>";
                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>

                        <div id="certupload" class="modal fade">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"><i class="fa fa-plus"></i> Import Certificate</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <form id="upload_cert" align="center" class="form-horizontal" method="post"
                                                    enctype="multipart/form-data">
                                                    <div class="col-md-3"></div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="file" name="import_file" class="form-control"> <br>
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="hidden" name="adminid" id="adminid"
                                                                value="<?php echo $_SESSION['id'] ?>" />
                                                            <input type="submit" name="action" id="action"
                                                                class="btn btn-success btn-block btn-xm"
                                                                value="Upload Certificate">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" name="id" id="id" />
                                        <input type="hidden" name="btn_action" id="btn_action" />
                                        <button type="button" class="btn btn-info btn-xm" data-dismiss="modal"><i
                                                class="fa fa-times"></i> Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <footer>
        <div class="col-md-12" style="margin-bottom:15px;">
            <div class="row">
                <br>
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h6 align="center" style="margin-top:15px;">
                        Copyright &copy; 2023 PHN Industry Sdn. Bhd.
                        Use with Google Chrome or Javascript enabled IE and Firefox.
                        </br>All Rights Reserved. | Web design by PHN IT Department
                    </h6>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </footer>
    <script>
        document.getElementById('download_cert').addEventListener('click', function (e) {
            e.preventDefault();

            swal({
                title: "Download this certificate?",
                text: "Are you sure you want to download your certificate?",
                icon: "info",
                buttons: ["Cancel", "Download"],
                dangerMode: false,
            }).then((willDownload) => {
                if (willDownload) {
                    // Check if file exists via AJAX
                    $.ajax({
                        url: 'certificate_action.php?download=1',
                        method: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                // Create hidden link to trigger actual file download
                                const link = document.createElement('a');
                                link.href = 'certificate_action.php?download=1&force=1';
                                link.style.display = 'none';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            } else {
                                swal("Error", response.message || "No certificate uploaded.", "error");
                            }
                        },
                        error: function () {
                            swal("Error", "Failed to check certificate status.", "error");
                        }
                    });
                } else {
                    swal("Cancelled", "Download cancelled.", "info");
                }
            });
        });

        $(document).ready(function () {
            $('#remove_cert').click(function (e) {
                e.preventDefault();

                swal({
                    title: "Delete this certificate?",
                    text: "Are you sure you want to delete your certificate?",
                    icon: "warning",
                    buttons: ["Cancel", "Confirm"],
                    dangerMode: true,
                }).then((isConfirm) => {
                    if (isConfirm) {
                        $.ajax({
                            url: 'certificate_action.php',
                            type: 'GET',
                            data: { delete: 1 },
                            dataType: 'json',  // Expect JSON response
                            success: function (response) {
                                if (response.status === 'success') {
                                    swal("Deleted!", response.message, "success")
                                        .then(() => {
                                            location.reload();
                                        });
                                } else if (response.status === 'error') {
                                    swal("Error", response.message, "error");
                                } else {
                                    swal("Error", "Unexpected response from server.", "error");
                                }
                            },
                            error: function () {
                                swal("Error", "Failed to delete the certificate.", "error");
                            }
                        });
                    } else {
                        swal("Cancelled", "Your certificate is safe.", "info");
                    }
                });
            });
        });

        $(document).on("submit", '#upload_cert', function (e) {
            event.preventDefault();
            swal({
                title: "Upload this certificate?",
                text: "Are you sure?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Confirm"]
            })
                .then((isConfirm) => {
                    if (isConfirm) {
                        swal(
                            'Imported!',
                            'Your certificate has been uploaded.',
                            'success'
                        )
                            .then(function () {
                                location.reload();
                            });
                        $.ajax({
                            url: "certificate_action.php",
                            method: "POST",
                            data: new FormData(this),
                            contentType: false,          // The content type used when sending data to the server.
                            cache: false,                // To unable request pages to be cached
                            processData: false,          // To send DOMDocument or non processed data file it is set to false
                            success: function (data) {

                            }
                        });
                    } else {
                        swal("Cancelled", "The file is not imported", "error");
                    }
                })
        });
    </script>

    </html>

    <?php
} else {
    header("Location: ../../../../login.php");
    exit();
}
?>
