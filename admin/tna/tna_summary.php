<?php
session_start();

if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

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

    <style>
        .panel-dashboard {
            display: flex;
            align-items: center;
            column-gap: 20px;
        }
        .panel-dashboard-2{
            display: flex;
            align-items: center;
            column-gap: 20px;
        }

        .tna-submission, .tna-summary {
            width: 100%;
            /* overflow-x: hidden; */
        }

        .tna {
            width: 100%;
        }

        .panel-body2 {
            height: 600px;
            /* Adjust the height as needed to match other table heights */
            overflow-y: auto;
            overflow-x: hidden
            /* Add vertical scrolling if content exceeds height */
        }
    </style>

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
                        <li><a href="tna_list.php">TNA LIST</a></li>
                        <li><a href="../tni/tni_list.php">TNI LIST</a></li>
						<li><a href="tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="../organization/org.php">ORGANIZATION</a></li>
                        <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
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
            <br>
    


            <!-- NEW ADDED DASHBOARD TABLE  -->
            <div class="panel-dashboard">
                <div class="row tna-submission">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-11" style="margin-top: 10px;">
                                        <strong>TNA Submission (by Department)</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body panel-body2" align="center">
                                <div class="row">
                                    <div class="col-sm-12 table-responsive">
                                        <table id="submissionlist" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Department</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row tna">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-11" style="margin-top: 10px;">
                                        <strong>TNA Status (by Department)</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body panel-body2" align="center">
                                <div class="row">
                                    <div class="col-sm-12 table-responsive">
                                        <table id="tnastatuslist" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Department</th>
                                                    <th>Head of Department</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<div class="panel-dashboard">
                <div class="row tna-submission">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-11" style="margin-top: 10px;">
                                        <strong>Summary of Section</strong>
                                    </div>
                                </div>
                            </div>
                         
				
				 <div class="panel-body3 tna-summary" align="center">
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="summarytna" style="width:100%;max-width:700px"></canvas>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                </div>

                <div class="row tna">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-11" style="margin-top: 10px;">
                                        <strong>Summary of Training Method</strong>
                                    </div>
                                </div>
                            </div>
                           <div class="panel-body3 tna-summary" align="center">
								<div class="row">
									<div class="col-md-12">
										<canvas id="methodtna" style="width:100%;max-width:700px"></canvas>
									</div>
								</div>
							</div>
                        </div>
                    </div>
                </div>
            </div>
			


            <!-- NEW ADDED DASHBOARD TABLE  -->


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
            t = setTimeout(function () {
                startTime()
            }, 500);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        var userid = <?php echo $_SESSION['id'] ?>;
        var newdept = '';

        $(function () {
            $.post("fetch_tna.php", {
                action: "load_department"
            }, function (data) {
                // console.log("department load: ", data)
                $('#department').html(data);
                $('#department').val(dept);
            });
        });

        $(function () {
            $.post("fetch_tna.php", {
                action: "all_department",
            }, function (data) {
                // Parse the returned JSON data
                var parsedData = JSON.parse(data);

                var gradedataTable = $('#submissionlist').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                    "pageLength": 10,
                    "info": true,
                    "data": parsedData, // Use parsed data
                    "columns": [
                        {
                            "data": "id",
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            "data": "department"
                        },
                        {
                            "data": "quantity"
                        }
                    ],
                    "columnDefs": [
                        {
                            className: 'text-left',
                            targets: [0, 1]
                        }
                    ]
                });
            });
        });

        $(function () {
            $.post("fetch_tna.php", {
                action: "tna_status",
            }, function (data) {
                var parsedData = JSON.parse(data);

                var gradedataTable = $('#tnastatuslist').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                    "pageLength": 6,
                    "info": true,
                    "data": parsedData, // Use parsed data
                    "columns": [
                        {
                            "data": "id",
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            "data": "department"
                        },
                        {
                            "data": "hod"
                        },
                        {
                            "data": "status"
                        }
                    ],
                    "columnDefs": [
                        {
                            className: 'text-left',
                            targets: [0, 1, 2, 3]
                        }
                    ]
                });
            });
        });

        var tnaSummaryPieChart;
        var canvasTnasummary = document.getElementById("summarytna");

        function makepiecharttna() {
            $.ajax({
                url: "fetch_tna.php",
                method: "POST",
                data: {
                    action: 'fetch_summary_tna',
                },
                dataType: "JSON",
                success: function (data) {
                    console.log("data pie chart: ", data);
                    var ctxTnasummary = canvasTnasummary.getContext('2d');
                    var section = [];
                    var no = [];
                    var percentage = [];
                    var colorstatus = [];
                    var label = [];

                    for (var count = 0; count < data.length; count++) {
                        section.push(data[count].section);
                        no.push(data[count].no);
                        percentage.push(data[count].percentage);
                        colorstatus.push(data[count].colorstatus);
                        label.push(data[count].label);
                    }

                    tnaSummaryPieChart = new Chart(ctxTnasummary, {
                        type: 'pie',
                        data: {
                            labels: label,
                            datasets: [{
                                data: no,
                                backgroundColor: colorstatus
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: true
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (tooltipItem) {
                                            let dataset = tooltipItem.dataset;
                                            let index = tooltipItem.dataIndex;

                                            let label = section[index]; // Section name
                                            let count = dataset.data[index]; // No (count)
                                            let percent = percentage[index]; // Percentage

                                            return `${label}: ${count} (${percent}%)`;
                                        }
                                    }
                                },
                                labels: {
                                    render: function (args) {
                                        return `${percentage[args.index]}%`; // Value + Percentage inside the pie
                                    },
                                    fontColor: '#fff',
                                }
                            },
                            legend: {
                                display: true
                            }
                        }
                    });
                }
            })
        }
        makepiecharttna();

        var methodPieChart;
        var canvasMethod =document.getElementById("methodtna");

        function makeMethodPiechart() {
            $.ajax({
                url: "fetch_tna.php",
                method: "POST",
                data: {
                    action: 'fetch_method_tna',
                },
                dataType: "JSON",
                success: function (data) {
                    console.log("data pie  method: ", data);
                    var ctxMethod = canvasMethod.getContext('2d');
                    var section = [];
                    var no = [];
                    var percentage = [];
                    var color = [];
                    var label = [];

                    for (var count = 0; count < data.length; count++) {
                        section.push(data[count].section);
                        no.push(data[count].no);
                        percentage.push(data[count].percentage);
                        color.push(data[count].color);
                        label.push(data[count].label);
                    }

                    methodPieChart = new Chart(ctxMethod, {
                        type: 'pie',
                        data: {
                            labels: label,
                            datasets: [{
                                data: no,
                                backgroundColor: color
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: true
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (tooltipItem) {
                                            let dataset = tooltipItem.dataset;
                                            let index = tooltipItem.dataIndex;

                                            let label = section[index]; // Section name
                                            let count = dataset.data[index]; // No (count)
                                            let percent = percentage[index]; // Percentage

                                            return `${label}: ${count} (${percent}%)`;
                                        }
                                    }
                                },
                                labels: {
                                    render: function (args) {
                                        return `${percentage[args.index]}%`; // Value + Percentage inside the pie
                                    },
                                    fontColor: '#fff',
                                }
                            },
                            legend: {
                                display: true
                            }
                        }
                    });
                }
            })
        }
        makeMethodPiechart();

        var dept = sessionStorage.getItem("setdeptnext");
        console.log("dept: ", dept);
        if (dept != null) {
            newdept = dept;
            fetch_data('load_staff', userid, dept);
            fetch_data1('load_grade', userid, dept);
        }


  
    </script>

    </html>
    <?php
} else {
    header("Location: ../../login.php");
    exit();
}
?>
