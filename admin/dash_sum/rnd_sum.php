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
                            <strong>Engineering Management 1 Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="em1Chart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Engineering Management 2 Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="em2Chart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>Facility Management Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="fmChart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Plant Engineering 1 (SA 1, BB & TM) Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="pe1Chart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>Plant Engineering 2 (SA 2, MLK & SBG) Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="pe2Chart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Process & Industrial Engineering Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="pnieChart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>Research & Development Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="rndChart" style="width:100%;max-width:700px"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Tooling Engineering Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="teChart" style="width:100%;max-width:700px"></canvas>
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
                            <strong>Tooling Maintenance Department</strong>
                        </div>
                        <div class="panel-body" align="center">
							<div class="row">
								<div class="col-md-12">
									<canvas id="tmChart" style="width:100%;max-width:700px"></canvas>
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

		var canvasEm1 = document.getElementById("em1Chart");
		var em1Chart;

		function makeem1chart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_em1',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxEm1 = canvasEm1.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    em1Chart = new Chart(ctxEm1, {
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

		function destroyChartEm1() {
            em1Chart.destroy();
        }

		makeem1chart(modeprev,fd,ld);

		var canvasEm2 = document.getElementById("em2Chart");
		var em2Chart;

		function makeem2chart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_em2',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxEm2 = canvasEm2.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    em2Chart = new Chart(ctxEm2, {
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

		function destroyChartEm2() {
            em2Chart.destroy();
        }

		makeem2chart(modeprev,fd,ld);

		var canvasFm = document.getElementById("fmChart");
		var fmChart;

		function makefmchart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_fm',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxFm = canvasFm.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    fmChart = new Chart(ctxFm, {
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

		function destroyChartFm() {
            fmChart.destroy();
        }

		makefmchart(modeprev,fd,ld);

		var canvasPe1 = document.getElementById("pe1Chart");
		var pe1Chart;

		function makepe1chart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_pe1',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxPe1 = canvasPe1.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    pe1Chart = new Chart(ctxPe1, {
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

		function destroyChartPe1() {
            pe1Chart.destroy();
        }

		makepe1chart(modeprev,fd,ld);

		var canvasPe2 = document.getElementById("pe2Chart");
		var pe2Chart;

		function makepe2chart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_pe2',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxPe2 = canvasPe2.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    pe2Chart = new Chart(ctxPe2, {
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

		function destroyChartPe2() {
            pe2Chart.destroy();
        }

		makepe2chart(modeprev,fd,ld);

		var canvasPnie = document.getElementById("pnieChart");
		var pnieChart;

		function makepniechart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_pnie',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxPnie = canvasPnie.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    pnieChart = new Chart(ctxPnie, {
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

		function destroyChartPnie() {
            pnieChart.destroy();
        }

		makepniechart(modeprev,fd,ld);

		var canvasRnd = document.getElementById("rndChart");
		var rndChart;

		function makerndchart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_rnd',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxRnd = canvasRnd.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    rndChart = new Chart(ctxRnd, {
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

		function destroyChartRnd() {
            rndChart.destroy();
        }

		makerndchart(modeprev,fd,ld);

		var canvasTe = document.getElementById("teChart");
		var teChart;

		function maketechart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_te',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxTe = canvasTe.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    teChart = new Chart(ctxTe, {
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

		function destroyChartTe() {
            teChart.destroy();
        }

		maketechart(modeprev,fd,ld);

		var canvasTm = document.getElementById("tmChart");
		var tmChart;

		function maketmchart(mode,startdate,enddate) {
			$.ajax({
				url:"fetch_rnd.php",
				method:"POST",
				data:{action:'fetch_tm',mode:mode,startdate:startdate,enddate:enddate},
				dataType:"JSON",
				success:function(data)
				{
                    var ctxTm = canvasTm.getContext('2d');
                    var category = [];
					var totalsend = [];
					var colorplant = [];

					for(var count = 0; count < data.length; count++) {
						category.push(data[count].category);
						totalsend.push(data[count].totalsend);
						colorplant.push(data[count].colorplant);
					}

                    tmChart = new Chart(ctxTm, {
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

		function destroyChartTm() {
            tmChart.destroy();
        }

		maketmchart(modeprev,fd,ld);

		$('#filter_date').click(function(){
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();
			var mode = $('#mode').val();

			$('#datarecord').fadeIn().html('<label>Data summary : '+startdate+' - '+enddate+'</label');
			destroyChartEm1();
			makeem1chart(mode,startdate,enddate);
			destroyChartEm2();
			makeem2bchart(mode,startdate,enddate);
			destroyChartFm();
			makefmchart(mode,startdate,enddate);
			destroyChartPe1();
			makepe1chart(mode,startdate,enddate);
			destroyChartPe2();
			makepe2chart(mode,startdate,enddate);
			destroyChartPnie();
			makepniechart(mode,startdate,enddate);
			destroyChartRnd();
			makerndchart(mode,startdate,enddate);
			destroyChartTe();
			maketechart(mode,startdate,enddate);
			destroyChartTm();
			maketmchart(mode,startdate,enddate);
        });

        $('#clear_filter').click(function(){
			$('#datarecord').fadeIn().html('<label>Data summary : '+fd+' - '+ld+'</label');
			destroyChartEm1();
			makeem1chart('manhour',fd,ld);
			destroyChartEm2();
			makeem2bchart('manhour',fd,ld);
			destroyChartFm();
			makefmchart('manhour',fd,ld);
			destroyChartPe1();
			makepe1chart('manhour',fd,ld);
			destroyChartPe2();
			makepe2chart('manhour',fd,ld);
			destroyChartPnie();
			makepniechart('manhour',fd,ld);
			destroyChartRnd();
			makerndchart('manhour',fd,ld);
			destroyChartTe();
			maketechart('manhour',fd,ld);
			destroyChartTm();
			maketmchart('manhour',fd,ld);
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