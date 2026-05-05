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
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

    <body onload="startTime()" style="background-image:url('../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
			<div class="row">
				<div class="col-md-10">
					<img src= "../../asset/image/lndlogo.gif" height="50" width="290">
				</div>
				<div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

				</div>
			</div>
			<nav class="navbar navbar-inverse" >
				<div class="container-fluid ">
					<ul class="nav navbar-nav">
						<li><a href="../dashboard.php">HOME</a></li>
						<li><a href="../staff/staff.php">STAFF LIST</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> ALL TRAINING </a>
							<ul class="dropdown-menu">
								<li><a href="../training/public/training.php">PUBLIC/INHOUSE</a></li>
								<li><a href="../training/ojt/training_ojt.php">OJT</a></li>
								<li><a href="../training/departmental/training_dept.php">DEPARTMENTAL</a></li>
							</ul>
						</li>
						<li><a href="../attendance/training.php">MY TRAINING</a></li>
						<li><a href="../tna/tna_list.php">TNA</a></li>
						<li><a href="../tni/tni_list.php">TNI</a></li>
						<li><a href="../tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
						<li><a href="../password/password.php">CHANGE PASSWORD</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname']?> </a>
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
                        <div class="panel-body" align="center">
							<div class="row">
                                <div class="col-md-1">
                                    <button type="button" name="back_dash" id="back_dash" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Home</button>
                                </div>
								<div class="col-md-5" align="left" style="margin-top:10px;">
                                    <span id="datarecord"></span>
                                </div>
								<div class="col-md-6" align="right">
									<div class="col-md-4">
										<select class="form-control" id="mode" name="mode">
											<option value="manhour">TOTAL MAN HOUR</option>
											<option value="totalhour">AVERAGE TOTAL HOUR</option>
										</select>
									</div>
									<div class="col-md-2">
										<input type="text" name="startdate" id="startdate" class="form-control" placeholder="Insert Start Date" autocomplete="off" />
									</div>
									<div class="col-md-2">
										<input type="text" name="enddate" id="enddate" class="form-control" placeholder="Insert End Date" autocomplete="off" />
									</div>
									<div class="col-md-2" align="right">
										<button type="button" name="filter_date" id="filter_date" class="btn btn-info btn-md">Filter <i class="fa fa-search"></i> </button>
									</div>
									<div class="col-md-2" align="left">
										<button type="button" name="clear_filter" id="clear_filter" class="btn btn-info btn-md">Clear <i class="fa fa-times"></i> </button>
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
                            <strong>Cost Engineering Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="ceChart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Program Management Proton Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="pmpChart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>ESG Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="esgChart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>HMS Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="hmsChart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>SHE Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="sheChart" style="width:100%;max-width:700px"></canvas>
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
					</br>All Rights Reserved.  |  Web design by PHN IT Department
					</h6>
				</div>
				<div class="col-md-2"></div>
			</div>
        </div>
	</footer>
    <script>
        function startTime() {
    		var today=new Date();
    		var h=today.getHours();
    		var m=today.getMinutes();
    		var s=today.getSeconds();
    		// add a zero in front of numbers<10
    		h=checkTime(h);
    		m=checkTime(m);
    		s=checkTime(s);
    		document.getElementById('txt').innerHTML=h+":"+m+":"+s;
    		t=setTimeout(function(){startTime()},500);
		}

		function checkTime(i) {
            if (i<10) {
                i="0" + i;
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

        $('#back_dash').click(function(){
            window.location = "../dashboard.php";
        });

		var fd = localStorage.getItem("setstart");
		var ld = localStorage.getItem("setend");
		var modeprev = localStorage.getItem("setmode");
		$('#mode').val(modeprev);

        $('#datarecord').fadeIn().html('<label>Data summary : '+fd+' - '+ld+'</label');

		var canvasCe = document.getElementById("ceChart");
		var ceChart;

		function makecechart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_transform.php",
				method:"POST",
				data:{action:'fetch_ce',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxCe = canvasCe.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    ceChart = new Chart(ctxCe, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [
                                {
                                    label: "Total Hours",
                                    backgroundColor: colorplant,
                                    data: totalsend
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes:[{
									min:0,
									ticks:{
										min:0,
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

		function destroyChartCe() {
            ceChart.destroy();
        }

		makecechart(modeprev,fd,ld);

		var canvasPmp = document.getElementById("pmpChart");
		var pmpChart;

		function makepmpchart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_transform.php",
				method:"POST",
				data:{action:'fetch_pmp',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxPmp = canvasPmp.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    pmpChart = new Chart(ctxPmp, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [
                                {
                                    label: "Total Hours",
                                    backgroundColor: colorplant,
                                    data: totalsend
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes:[{
									min:0,
									ticks:{
										min:0,
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

		function destroyChartPmp() {
            pmpChart.destroy();
        }

		makepmpchart(modeprev,fd,ld);

		var canvasEsg = document.getElementById("esgChart");
		var esgChart;

		function makeesgchart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_transform.php",
				method:"POST",
				data:{action:'fetch_esg',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxEsg = canvasEsg.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    esgChart = new Chart(ctxEsg, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [
                                {
                                    label: "Total Hours",
                                    backgroundColor: colorplant,
                                    data: totalsend
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes:[{
									min:0,
									ticks:{
										min:0,
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

		function destroyChartEsg() {
            esgChart.destroy();
        }

		makeesgchart(modeprev,fd,ld);

		var canvasHms = document.getElementById("hmsChart");
		var hmsChart;

		function makehmschart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_transform.php",
				method:"POST",
				data:{action:'fetch_hms',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxHms = canvasHms.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    hmsChart = new Chart(ctxHms, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [
                                {
                                    label: "Total Hours",
                                    backgroundColor: colorplant,
                                    data: totalsend
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes:[{
									min:0,
									ticks:{
										min:0,
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

		function destroyChartHms() {
            hmsChart.destroy();
        }

		makehmschart(modeprev,fd,ld);

		var canvasShe = document.getElementById("sheChart");
		var sheChart;

		function makeshechart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_transform.php",
				method:"POST",
				data:{action:'fetch_she',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxShe = canvasShe.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    sheChart = new Chart(ctxShe, {
                        type: 'bar',
                        data: {
                            labels: category,
                            datasets: [
                                {
                                    label: "Total Hours",
                                    backgroundColor: colorplant,
                                    data: totalsend
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes:[{
									min:0,
									ticks:{
										min:0,
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

		function destroyChartShe() {
            sheChart.destroy();
        }

		makeshechart(modeprev,fd,ld);

		$('#filter_date').click(function(){
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();
			var mode = $('#mode').val();

			$('#datarecord').fadeIn().html('<label>Data summary : '+startdate+' - '+enddate+'</label');
			destroyChartCe();
			makecechart(mode,startdate,enddate);
			destroyChartPmp();
			makepmpchart(mode,startdate,enddate);
			destroyChartEsg();
			makeesgchart(mode,startdate,enddate);
			destroyChartHms();
			makehmschart(mode,startdate,enddate);
			destroyChartShe();
			makeshechart(mode,startdate,enddate);
        });

        $('#clear_filter').click(function(){
			$('#datarecord').fadeIn().html('<label>Data summary : '+fd+' - '+ld+'</label');
			destroyChartCe();
			makecechart('manhour',fd,ld);
			destroyChartPmp();
			makepmpchart('manhour',fd,ld);
			destroyChartEsg();
			makeesgchart('manhour',fd,ld);
			destroyChartHms();
			makehmschart('manhour',fd,ld);
			destroyChartShe();
			makeshechart('manhour',fd,ld);
            $('#startdate').val('');
            $('#enddate').val('');
			$('#mode').val('manhour');
        });
    </script>
</html>
<?php
    }else{
         header("Location: ../../login.php");
         exit();
    }
?>