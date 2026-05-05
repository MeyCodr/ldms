<?php
session_start();

if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Learning and Development Management System</title>
        
        <script src="../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../asset/css/bootstrap.min.css" />
        <script src="../asset/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
        <script src="../asset/js/chartjs-plugin-labels.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
        <link rel="stylesheet" href="../asset/css/datepicker.css">
        <script src="../asset/js/bootstrap-datepicker1.js"></script>
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css" />
        <script type="text/javascript"
            src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js">
            </script>
    </head>

    <body onload="startTime()" style="background-image:url('../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <img src="../asset/image/lndlogo.gif" height="50" width="290">
                </div>
                <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

                </div>
            </div>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid ">
                    <ul class="nav navbar-nav">
                        <li><a href="dashboard.php">HOME</a></li>
                        <li><a href="staff/staff.php">STAFF LIST</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> ALL TRAINING </a>
                            <ul class="dropdown-menu">
                                <li><a href="training/public/training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="training/ojt/training_ojt.php">OJT</a></li>
                                <li><a href="training/departmental/training_dept.php">DEPARTMENTAL</a></li>
                            </ul>
                        </li>
                        <li><a href="attendance/training.php">MY TRAINING</a></li>
                        <li><a href="tna/tna_list.php">TNA</a></li>
                        <li><a href="tni/tni_list.php">TNI</a></li>
						<li><a href="tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="password/password.php">CHANGE PASSWORD</a></li>

                        
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="../logout.php">LOGOUT</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-6" align="left" style="margin-top:10px;"><span id="datarecord"></span>
                                </div>
                                <div class="col-md-6" align="right">
                                    <div class="col-md-4">
                                        <select class="form-control" id="mode" name="mode">
                                            <option value="manhour">TOTAL MAN HOUR</option>
                                            <option value="totalhour">AVERAGE TOTAL HOUR</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="startdate" id="startdate" class="form-control"
                                            placeholder="Insert Start Date" autocomplete="off" />
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="enddate" id="enddate" class="form-control"
                                            placeholder="Insert End Date" autocomplete="off" />
                                    </div>
                                    <div class="col-md-2" align="right">
                                        <button type="button" name="filter_date" id="filter_date"
                                            class="btn btn-info btn-md">Filter <i class="fa fa-search"></i> </button>
                                    </div>
                                    <div class="col-md-2" align="left">
                                        <button type="button" name="clear_filter" id="clear_filter"
                                            class="btn btn-info btn-md">Clear <i class="fa fa-times"></i> </button>
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
                            <strong>Training Overview</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-3">
                                    <div style="background-color: #0000FF;border-radius: 5px;">
                                        <div class="panel-body" align="center">
                                            <div class="col-sm-4">
                                                <p></p>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60"
                                                    fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16"
                                                    style="color:white;">
                                                    <path d="M2 2h2v2H2V2Z" />
                                                    <path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z" />
                                                    <path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z" />
                                                    <path
                                                        d="M10 0v6h6V0h-6Zm5 1v4h-4V1h4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2Zm-1 0h-1v2h2v-1h-1V9Zm-4 0h2v1h-1v1h-1V9Zm2 3v-1h-1v1h-1v1H9v1h3v-2h1Zm0 0h3v1h-2v1h-1v-2Zm-4-1v1h1v-2H7v1h2Z" />
                                                    <path d="M7 12h1v3h4v1H7v-4Zm9 2v2h-3v-1h2v-1h1Z" />
                                                </svg>
                                            </div>
                                            
                                            <div class="col-sm-8" align="left">
                                                <p style="color:white;margin-top:7px;" align="right">Total Trainings</p>
                                                <h3 id="totaltraining" style="color:white;" align="right"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div style="background-color: #FF0000;border-radius: 5px;">
                                        <div class="panel-body" align="center">
                                            <div class="col-sm-4">
                                                <p></p>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60"
                                                    fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16"
                                                    style="color:white;">
                                                    <path
                                                        d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                                </svg>
                                            </div>
                                            <div class="col-sm-8" align="left">
                                                <p style="color:white;margin-top:7px;" align="right">Total Manpower Training
                                                </p>
                                                <h3 id="totaluser" style="color:white;" align="right"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div style="background-color: #008000;border-radius: 5px;">
                                        <div class="panel-body" align="center">
                                            <div class="col-sm-4">
                                                <p></p>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60"
                                                    fill="currentColor" class="bi bi-calendar2-week" viewBox="0 0 16 16"
                                                    style="color:white;">
                                                    <path
                                                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z" />
                                                    <path
                                                        d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4zM11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z" />
                                                </svg>
                                            </div>
                                            <div class="col-sm-8" align="left">
                                                <p style="color:white;margin-top:7px;" align="right">Total Training Days</p>
                                                <h3 id="totalday" style="color:white;" align="right"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div style="background-color: #E0115F;border-radius: 5px;">
                                        <div class="panel-body" align="center">
                                            <div class="col-sm-4">
                                                <p></p>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60"
                                                    fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16"
                                                    style="color:white;">
                                                    <path
                                                        d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z" />
                                                    <path
                                                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z" />
                                                </svg>
                                            </div>
                                            <div class="col-sm-8" align="left">
                                                <p style="color:white;margin-top:7px;" align="right">Total Hours</p>
                                                <h3 id="totalhour" style="color:white;" align="right"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Public/Inhouse vs OJT Overview (Training Hours)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="publicojtChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Monthly Total Cost (RM)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="costChart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>All Department List (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="top10Chart" style="width:100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Business Development & Strategy Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="businessChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>DHMSB Operations Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="dhmsbChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Quality Management Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="qualityChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Executive Director Division</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="directorChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Finance Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="financeChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Human Capital & ESG (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <canvas id="humanChart" style="width:100%;max-width:700px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Operation Management Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <canvas id="operationChart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Engineering and R&D Division (Total Man Hour)</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <canvas id="rndChart" style="width:100%;max-width:700px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Top 5 Inhouse Trainer</strong>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="trainerdata" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Staff No.</th>
                                                <th>Name</th>
                                                <th>Total Hours</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
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

        $('#startdate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        $('#enddate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        var date = new Date(),
            y = date.getFullYear();
        var fd = y + "-01-01";
        var ld = y + "-12-31";

        var publicojtPieChart;

        getOverview(fd, ld);
        $('#datarecord').fadeIn().html('<label>Data summary : ' + fd + ' - ' + ld + '</label');

        function getOverview(startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                type: "POST",
                data: {
                    action: 'fetch_overview',
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    $('#totaltraining').text(data[0].totaltraining);
                    $('#totaluser').text(data[0].totaluser);
                    $('#totalday').text(data[0].totalday);
                    $('#totalhour').text(data[0].totalhour);
                }
            })
        }

        var canvasPublicojt = document.getElementById("publicojtChart");

        function makechartpublicojt(startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_publicojt',
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxPublicojt = canvasPublicojt.getContext('2d');
                    var status = [];
                    var totalstatus = [];
                    var colorstatus = [];

                    for (var count = 0; count < data.length; count++) {
                        status.push(data[count].status);
                        totalstatus.push(data[count].totalstatus);
                        colorstatus.push(data[count].colorstatus);
                    }

                    publicojtPieChart = new Chart(ctxPublicojt, {
                        type: 'pie',
                        data: {
                            labels: status,
                            datasets: [{
                                data: totalstatus,
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
                                labels: {
                                    render: 'value',
                                    fontColor: '#fff',
                                }
                            }
                        }
                    });
                }
            })
        }

        makechartpublicojt(fd, ld);

        function destroyChartpublicojt() {
            publicojtPieChart.destroy();
        }

        var canvasCost = document.getElementById("costChart");
        var costChart;

        function makecostchart(startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_cost',
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxCost = canvasCost.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    costChart = new Chart(ctxCost, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend,
                                barPercentage: 0.55,
                                categoryPercentage: 0.7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 60,
                                        minRotation: 45
                                    }
                                }],
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 100,
                                        callback: function (value, index, values) {
                                            return 'RM ' + value.toString().replace(
                                                /\B(?=(\d{3})+(?!\d))/g, ",");
                                        }
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: function (args) {
                                        return 'RM ' + args.value.toString().replace(
                                            /\B(?=(\d{3})+(?!\d))/g, ",");
                                    },
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        makecostchart(fd, ld);

        function fetch_data(action, startdate, enddate) {
            var userdataTable = $('#trainerdata').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "pageLength": 10,
                "info": true,
                "ajax": {
                    url: "fetch_dash.php",
                    type: "POST",
                    dataType: "json",
                    dataSrc: '',
                    data: {
                        action: action,
                        startdate: startdate,
                        enddate: enddate
                    },
                    error: function(xhr, status, error) {
                        console.error('DataTables AJAX error:', status, error, xhr.responseText);
                        alert('Failed to load trainer data. Check browser console for details.');
                    }
                },
                "columns": [{
                    "data": "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    "data": "staffno"
                },
                {
                    "data": "staffname"
                },
                {
                    "data": "trainertotalhour"
                }
                ],
                "columnDefs": [{
                    className: 'text-center',
                    targets: [0, 1, 3]
                }]
            });
        }

        fetch_data('load_top5', fd, ld);

        var canvasTop10 = document.getElementById("top10Chart");
        var top10Chart;

        function maketop10chart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_top10',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxTop10 = canvasTop10.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    top10Chart = new Chart(ctxTop10, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 90,
                                        minRotation: 90,
                                        fontSize: 11
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartTop10() {
            top10Chart.destroy();
        }

        maketop10chart('manhour', fd, ld);

        var canvasBusiness = document.getElementById("businessChart");
        var businessChart;

        function makebusinesschart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_business',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxBusiness = canvasBusiness.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    businessChart = new Chart(ctxBusiness, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartbusiness() {
            businessChart.destroy();
        }

        makebusinesschart('manhour', fd, ld);

        var canvasDhmsb = document.getElementById("dhmsbChart");
        var dhmsbChart;

        function makedhmsbchart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_dhmsb',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxDhmsb = canvasDhmsb.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    dhmsbChart = new Chart(ctxDhmsb, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartdhmsb() {
            dhmsbChart.destroy();
        }

        makedhmsbchart('manhour', fd, ld);

        // var canvasDirector = document.getElementById("directorChart");
        // var directorChart;

        // function makedirectorchart(startdate,enddate) {
        // 	$.ajax({
        // 		url:"fetch_dash.php",
        // 		method:"POST",
        // 		data:{action:'fetch_director',startdate:startdate,enddate:enddate},
        // 		dataType:"JSON",
        // 		success:function(data)
        // 		{
        //             var ctxDirector = canvasDirector.getContext('2d');
        //             var category = [];
        // 			var totalsend = [];
        // 			var colorplant = [];

        // 			for(var count = 0; count < data.length; count++) {
        // 				category.push(data[count].category);
        // 				totalsend.push(data[count].totalsend);
        // 				colorplant.push(data[count].colorplant);
        // 			}

        //             directorChart = new Chart(ctxDirector, {
        //                 type: 'bar',
        //                 data: {
        //                     labels: category,
        //                     datasets: [
        //                         {
        //                             label: "Total Hours",
        //                             backgroundColor: colorplant,
        //                             data: totalsend
        //                         }
        //                     ]
        //                 },
        //                 options: {
        //                     responsive: true,
        //                     maintainAspectRatio: false,
        //                     legend: {
        //                         display: false
        //                     },
        //                     scales: {
        //                         yAxes:[{
        // 							min:0,
        // 							ticks:{
        // 								min:0,
        // 								stepSize: 10
        // 							}
        // 						}]
        //                     },
        //                     plugins: {
        //                         labels: {
        //                             render: 'value',
        //                             fontColor: '#000',
        //                         }
        //                     }
        //                 }
        //             });
        // 		}
        // 	})
        // }

        // function destroyChartdirector() {
        //     directorChart.destroy();
        // }

        // makedirectorchart('','');

        var canvasFinance = document.getElementById("financeChart");
        var financeChart;

        function makefinancechart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_finance',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxFinance = canvasFinance.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    financeChart = new Chart(ctxFinance, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartfinance() {
            financeChart.destroy();
        }

        makefinancechart('manhour', fd, ld);

        var canvasHuman = document.getElementById("humanChart");
        var humanChart;

        function makehumanchart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_human',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxHuman = canvasHuman.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    humanChart = new Chart(ctxHuman, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyCharthuman() {
            humanChart.destroy();
        }

        makehumanchart('manhour', fd, ld);

        var canvasOperation = document.getElementById("operationChart");
        var operationChart;

        function makeoperationchart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_operation',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxOperation = canvasOperation.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    operationChart = new Chart(ctxOperation, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartoperation() {
            operationChart.destroy();
        }

        makeoperationchart('manhour', fd, ld);

        // var canvasTransform = $("#transformChart");
        // var transformChart;

        // function maketransformchart(mode, startdate, enddate) {
        //     $(document).ready(function () {
        //         console.log("mode: ", mode);
        //         console.log("start date: ", startdate);
        //         console.log("end date: ", enddate);

        //         $.ajax({
        //             url: "fetch_dash.php",
        //             method: "POST",
        //             data: {
        //                 action: 'fetch_transform',
        //                 mode: mode,
        //                 startdate: startdate,
        //                 enddate: enddate
        //             },
        //             dataType: "JSON",
        //             success: function (data) {
        //                 console.log("data: ", data);

        //                 // Get the canvas element and extract the DOM element using .get(0)
        //                 var canvasTransform = $("#transformChart").get(0);

        //                 if (!canvasTransform) {
        //                     console.error("Canvas element not found");
        //                     return;
        //                 }

        //                 var ctxTransform = canvasTransform.getContext('2d');
        //                 if (!ctxTransform) {
        //                     console.error("Failed to get canvas context");
        //                     return;
        //                 }

        //                 var category = [];
        //                 var totalsend = [];
        //                 var colorplant = [];

        //                 for (var count = 0; count < data.length; count++) {
        //                     category.push(data[count].category);
        //                     totalsend.push(data[count].totalsend);
        //                     colorplant.push(data[count].colorplant);
        //                 }

        //                 // Destroy previous chart instance if it exists
        //                 if (typeof transformChart !== 'undefined' && transformChart) {
        //                     transformChart.destroy();
        //                 }

        //                 transformChart = new Chart(ctxTransform, {
        //                     type: 'bar',
        //                     data: {
        //                         labels: category,
        //                         datasets: [{
        //                             label: "Total Hours",
        //                             backgroundColor: colorplant,
        //                             data: totalsend
        //                         }]
        //                     },
        //                     options: {
        //                         responsive: true,
        //                         maintainAspectRatio: false,
        //                         plugins: {
        //                             legend: {
        //                                 display: false
        //                             },
        //                             tooltip: {
        //                                 enabled: true
        //                             }
        //                         },
        //                         scales: {
        //                             y: {
        //                                 min: 0,
        //                                 ticks: {
        //                                     stepSize: 10
        //                                 }
        //                             }
        //                         },
        //                         layout: {
        //                             padding: {
        //                                 top: 20
        //                             }
        //                         }
        //                     }
        //                 });
        //             }
        //         });
        //     });
        // }

        // function destroyCharttransform() {
        //     if (transformChart) {
        //         transformChart.destroy();
        //     }
        // }

        // // Call the function
        // maketransformchart('manhour', fd, ld);


        var canvasQuality = document.getElementById("qualityChart");
        var qualityChart;

        function makequalitychart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_quality',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxQuality = canvasQuality.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    qualityChart = new Chart(ctxQuality, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartquality() {
            qualityChart.destroy();
        }

        makequalitychart('manhour', fd, ld);

        var canvasRnd = document.getElementById("rndChart");
        var rndChart;

        function makerndchart(mode, startdate, enddate) {
            $.ajax({
                url: "fetch_dash.php",
                method: "POST",
                data: {
                    action: 'fetch_rnd',
                    mode: mode,
                    startdate: startdate,
                    enddate: enddate
                },
                dataType: "JSON",
                success: function (data) {
                    var ctxRnd = canvasRnd.getContext('2d');
                    var category = [];
                    var totalsend = [];
                    var colorplant = [];

                    for (var count = 0; count < data.length; count++) {
                        category.push(data[count].category);
                        totalsend.push(data[count].totalsend);
                        colorplant.push(data[count].colorplant);
                    }

                    rndChart = new Chart(ctxRnd, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [{
                                label: "Total Hours",
                                backgroundColor: colorplant,
                                data: totalsend
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    min: 0,
                                    ticks: {
                                        min: 0,
                                        stepSize: 10
                                    }
                                }]
                            },
                            plugins: {
                                labels: {
                                    render: 'value',
                                    fontColor: '#000',
                                }
                            },
                            layout: {
                                padding: {
                                    top: 20
                                }
                            }
                        }
                    });
                }
            })
        }

        function destroyChartrnd() {
            rndChart.destroy();
        }



        makerndchart('manhour', fd, ld);

        canvasBusiness.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/business_sum.php";
        }

        canvasDhmsb.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/dhmsb_sum.php";
        }

        canvasQuality.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/quality_sum.php";
        }

        canvasFinance.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/finance_sum.php";
        }

        canvasHuman.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/human_sum.php";
        }

        // canvasTransform.onclick = function (e) {
        //     localStorage.setItem("setstart", fd);
        //     localStorage.setItem("setend", ld);
        //     localStorage.setItem("setmode", 'manhour');
        //     window.location = "dash_sum/transform_sum.php";
        // }

        canvasOperation.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/operation_sum.php";
        }

        canvasRnd.onclick = function (e) {
            localStorage.setItem("setstart", fd);
            localStorage.setItem("setend", ld);
            localStorage.setItem("setmode", 'manhour');
            window.location = "dash_sum/rnd_sum.php";
        }

        $('#filter_date').click(function () {
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();
            var mode = $('#mode').val();

            if(!startdate || !enddate){
                alert("Please fill in the start date and end date!");
                return;
            }

            console.log("start date: ", startdate);
            console.log("end date: ", enddate);
            console.log("mode: ", mode);

            $('#datarecord').fadeIn().html('<label>Data summary : ' + startdate + ' - ' + enddate + '</label');
            getOverview(startdate, enddate);
            destroyChartpublicojt();
            makechartpublicojt(startdate, enddate);
            makecostchart(startdate, enddate);
            $('#trainerdata').DataTable().destroy();
            fetch_data('load_top5', startdate, enddate);
            destroyChartTop10();
            maketop10chart(mode, startdate, enddate);
            destroyChartbusiness();
            makebusinesschart(mode, startdate, enddate);
            // destroyChartdhmsb();
            makedhmsbchart(mode, startdate, enddate);
            // destroyChartdirector();
            // makedirectorchart(startdate,enddate);
            destroyChartfinance();
            makefinancechart(mode, startdate, enddate);
            destroyCharthuman();
            makehumanchart(mode, startdate, enddate);
            destroyChartoperation();
            makeoperationchart(mode, startdate, enddate);
            // destroyCharttransform();
            // maketransformchart(mode, startdate, enddate);
            destroyChartquality();
            makequalitychart(mode, startdate, enddate);
            destroyChartrnd();
            makerndchart(mode, startdate, enddate);

            canvasBusiness.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/business_sum.php";
            }

            canvasDhmsb.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/dhmsb_sum.php";
            }

            canvasQuality.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/quality_sum.php";
            }

            canvasFinance.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/finance_sum.php";
            }

            canvasHuman.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/human_sum.php";
            }

            // canvasTransform.onclick = function () {
            //     localStorage.setItem("setstart", startdate);
            //     localStorage.setItem("setend", enddate);
            //     localStorage.setItem("setmode", mode);
            //     window.location = "dash_sum/transform_sum.php";
            // }

            canvasOperation.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/operation_sum.php";
            }

            canvasRnd.onclick = function (e) {
                localStorage.setItem("setstart", startdate);
                localStorage.setItem("setend", enddate);
                localStorage.setItem("setmode", mode);
                window.location = "dash_sum/rnd_sum.php";
            }
        });

        $('#clear_filter').click(function () {
            $('#datarecord').fadeIn().html('<label>Data summary : ' + fd + ' - ' + ld + '</label');
            getOverview(fd, ld);
            destroyChartpublicojt();
            makechartpublicojt(fd, ld);
            makecostchart(fd, ld);
            $('#trainerdata').DataTable().destroy();
            fetch_data('load_top5', fd, ld);
            destroyChartTop10();
            maketop10chart('manhour', fd, ld);
            destroyChartbusiness();
            makebusinesschart('manhour', fd, ld);
            // destroyChartdhmsb();
            makedhmsbchart('manhour', fd, ld);
            // destroyChartdirector();
            // makedirectorchart('','');
            destroyChartfinance();
            makefinancechart('manhour', fd, ld);
            destroyCharthuman();
            makehumanchart('manhour', fd, ld);
            destroyChartoperation();
            makeoperationchart('manhour', fd, ld);
            // destroyCharttransform();
            // maketransformchart('manhour', fd, ld);
            destroyChartquality();
            makequalitychart('manhour', fd, ld);
            destroyChartrnd();
            makerndchart('manhour', fd, ld);
            $('#startdate').val('');
            $('#enddate').val('');
            $('#mode').val('manhour');

            canvasBusiness.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/business_sum.php";
            }

            canvasDhmsb.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/dhmsb_sum.php";
            }

            canvasQuality.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/quality_sum.php";
            }

            canvasFinance.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/finance_sum.php";
            }

            canvasHuman.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/human_sum.php";
            }

            // canvasTransform.onclick = function (e) {
            //     localStorage.setItem("setstart", fd);
            //     localStorage.setItem("setend", ld);
            //     localStorage.setItem("setmode", 'manhour');
            //     window.location = "dash_sum/transform_sum.php";
            // }

            canvasOperation.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/operation_sum.php";
            }

            canvasRnd.onclick = function (e) {
                localStorage.setItem("setstart", fd);
                localStorage.setItem("setend", ld);
                localStorage.setItem("setmode", 'manhour');
                window.location = "dash_sum/rnd_sum.php";
            }
        });
    </script>

    </html>
    <?php
} else {
    header("Location: ../login.php");
    exit();
}
?>
