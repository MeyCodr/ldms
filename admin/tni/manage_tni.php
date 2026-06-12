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

    <style>
        #spinner-div {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 2;
        }
    </style>

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
                        <li><a href="tni_list.php">TNI</a></li>
						<li><a href="../tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="../organization/org.php">ORGANIZATION</a></li>
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
            <div id="spinner-div">
                <img src="../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..." style="margin-top: 350px;"/>
            </div>
            <div class="row">
                <div class="col-md-12">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-2" align="left">
                                    <button type="button" name="back_training" id="back_training" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> BACK TO TNI LIST</button>
                                </div>
                                <div class="col-md-8" style="margin-top:10px" align="center">
                                    <strong id="title">Training Need Identification (TNI) Form - (FY 2025)</strong>
                                </div>
                                <div class="col-md-2"></div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <form method="post" id="tni_form">
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10" align="right">
                                        <h5 id="yeartraining"></h5>
                                        <h5 id="totalhours"></h5>
                                        <h5 id="ojthours"></h5>
                                        <h5 id="publichours"></h5>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10 table-responsive" align="left">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="2">Skill Level Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1 - Fundamental Awareness</td>
                                                    <td>Basic knowledge</td>
                                                </tr>
                                                <tr>
                                                    <td>2 - Novice</td>
                                                    <td>Little experience or competence in the skill</td>
                                                </tr>
                                                <tr>
                                                    <td>3 - Intermediate</td>
                                                    <td>Has some competence but remains below level required</td>
                                                </tr>
                                                <tr>
                                                    <td>4 - Proficient</td>
                                                    <td>Competent and confident in the area</td>
                                                </tr>
                                                <tr>
                                                    <td>5 - Expert</td>
                                                    <td>An expert in that skill</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10 table-responsive">
                                        <table id="tnilist" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="10">
                                                        <div class="row">
                                                            <div class="col-sm-6" align="left" style="margin-top:5px;">
                                                                a. Mandatory (Within 1st 3 month in the role)
                                                            </div>
                                                            <div class="col-sm-6" align="right">
                                                                <button type="button" name="add_mand" id="add_mand" class="btn btn-info btn-sm">Add Training <i class="fa fa plus"></i> </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th rowspan="2" width="10px">No.</th>
                                                    <th rowspan="2" width="420px">Performance Indicator (Critical to Quality/Process)</th>
                                                    <th colspan="2">Performance</th>
                                                    <th rowspan="2" width="80px">Gap</th>
                                                    <th rowspan="2">Possible Causes</th>
                                                    <th rowspan="2">Attitude / Skill / Knowledge Required</th>
                                                    <th rowspan="2">L&D Method</th>
                                                    <th rowspan="2">Evaluation Method</th>
                                                    <th rowspan="2">Action</th>
                                                </tr>
                                                <tr>
                                                    <th width="80px">Expected</th>
                                                    <th width="80px">Actual</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group" align="right">
                                            <input type="hidden" name="userid" id="userid" />
                                            <input type="hidden" name="btn_action" id="btn_action" />
                                            <input type="hidden" name="mandatory" id="mandatory" />
                                            <button type="submit" name="action" id="action" class="btn btn-success btn-xm"><i class="fa fa-save"></i> Save</button>
                                        </div>
                                    </div>
                                    <div class="col-md-1"></div>
                                </div>
                            </form>
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

        $('#back_training').click(function(){
            window.location = "tni_list.php";
        });

        var userid = localStorage.getItem("setid");
        var action = 'gettni';
        var tni = 0;
        var countmand = 1;
        var curyear = new Date().getFullYear();

        $.ajax({
            url:"fetch_tni.php",
            method:"POST",
            data:{userid:userid,action:action},
            dataType:"json",
            success:function(data) {
                console.log("data: ", data);
                tni = parseInt(data[0].tni);
                $('#yeartraining').text('Training History '+curyear);
                $('#totalhours').text('Current Total Training Hours : '+data[0].totalhour);
                $('#ojthours').text('Training Hours (OJT) : '+data[0].ojthour);
                $('#publichours').text('Training Hours (Public / In-house) : '+data[0].publichour);

                if (tni == 0) {
                    $('#btn_action').val('addtni');
                    $('#userid').val(userid);

                    $("#tnilist").append('<tr><td style="text-align:center;">'+countmand+'</td><td><textarea rows="2" name="task'+countmand+'" id="task'+countmand+'" class="form-control" placeholder="Insert Training" required></textarea></td><td><select name="targetsk'+countmand+'" id="targetsk'+countmand+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsk'+countmand+'" id="currentsk'+countmand+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gap'+countmand+'" id="gap'+countmand+'" class="form-control" readonly /></td><td><textarea rows="2" name="cause'+countmand+'" id="cause'+countmand+'" class="form-control" placeholder="Insert Cause" required></textarea></td><td><textarea rows="2" name="ask'+countmand+'" id="ask'+countmand+'" class="form-control" placeholder="Insert A/S/K" required></textarea></td><td><select name="trtype'+countmand+'" id="trtype'+countmand+'" class="form-control"><option selected disabled="disabled">-- Select L&D Method --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="2">3 - External / In-house</option></select></td><td><textarea rows="2" name="evaluate'+countmand+'" id="evaluate'+countmand+'" class="form-control" placeholder="Insert Evaluation Method" required></textarea></td><td><button type="button" id="removebtn'+countmand+'" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>');
                    $('#removebtn'+countmand).hide();
                    $('#gap'+countmand).val(0);
                    $('#targetsk'+countmand).on('change', function() {
                        var target = parseInt($('#targetsk'+countmand).val());
                        var current = parseInt($('#currentsk'+countmand).val());

                        var gap = target - current;
                        $('#gap'+countmand).val(gap);
                    });

                    $('#currentsk'+countmand).on('change', function() {
                        var target = parseInt($('#targetsk'+countmand).val());
                        var current = parseInt($('#currentsk'+countmand).val());

                        var gap = target - current;
                        $('#gap'+countmand).val(gap);
                    });
                }else if (tni > 0) {
                    $('#btn_action').val('addtni');
                    $('#userid').val(userid);
                    var action1 = 'getlisttni';
                    var targetsk = [];
                    var currentsk = [];
                    var trtype = [];

                    $.ajax({
                        url:"fetch_tni.php",
                        method:"POST",
                        data:{userid:userid,action:action1},
                        dataType:"json",
                        success:function(data) {
                            console.log("data2: ", data);
                            var trHTML = '';
						    var noid = 1;
                            $.each(data, function (i, data) {
                                trHTML += '<tr><td style="text-align:center;">'+noid+'</td><td><textarea rows="2" name="task'+noid+'" id="task'+noid+'" class="form-control" required>'+data.training+'</textarea></td><td><select name="targetsk'+noid+'" id="targetsk'+noid+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsk'+noid+'" id="currentsk'+noid+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gap'+noid+'" id="gap'+noid+'" class="form-control" value="'+data.gap+'" readonly /></td><td><textarea rows="2" name="cause'+noid+'" id="cause'+noid+'" class="form-control" required>'+data.cause+'</textarea></td><td><textarea rows="2" name="ask'+noid+'" id="ask'+noid+'" class="form-control" required>'+data.ask+'</textarea></td><td><select name="trtype'+noid+'" id="trtype'+noid+'" class="form-control"><option selected disabled="disabled">-- Select Training Type --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="2">3 - External / In-house</option></select></td><td><textarea rows="2" name="evaluate'+noid+'" id="evaluate'+noid+'" class="form-control" required>'+data.evaluation+'</textarea></td><td><button type="button" id="removebtn'+noid+'" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>';
                                targetsk.push(data.expected);
                                currentsk.push(data.actual);
                                trtype.push(data.method);
                                noid++;
                            });

                            $('#tnilist').append(trHTML);

                            for (t=1;t<noid;t++) {
                                $('#targetsk'+t).val(targetsk[t-1]);
                                $('#currentsk'+t).val(currentsk[t-1]);
                                $('#trtype'+t).val(trtype[t-1]);

                                if (t != noid-1) {
                                    $('#removebtn'+(t)).hide();
                                }else if (t == noid-1 && ((noid-1) != 1)){
                                    $('#removebtn'+(t)).show();
                                }else {
                                    $('#removebtn'+(t)).hide();
                                }

                                var target = parseInt($('#targetsk'+t).val());
                                var current = parseInt($('#currentsk'+t).val());

                                $('#targetsk'+t).on('change', function() {
                                    var id = $(this).attr("id").slice(-1);
                                    var target = parseInt($('#targetsk'+id).val());
                                    var current = parseInt($('#currentsk'+id).val());

                                    var gap = target - current;
                                    $('#gap'+id).val(gap);
                                });

                                $('#currentsk'+t).on('change', function() {
                                    var id = $(this).attr("id").slice(-1);
                                    var target = parseInt($('#targetsk'+id).val());
                                    var current = parseInt($('#currentsk'+id).val());

                                    var gap = target - current;
                                    $('#gap'+id).val(gap);
                                });
                            }

                            countmand = noid - 1;

                            $('#removebtn'+countmand).click(function() {
                                countmand--;
                                $('#tnilist tr:last').remove();
                                if (countmand != 1) {
                                    $('#removebtn'+countmand).show();
                                }
                                $('#mandatory').val(countmand);
                            });

                            $('#mandatory').val(countmand);
                        }
                    });
                }
            }
        });

        $('#add_mand').click(function() {
            countmand++;
            if (countmand > 1 && $('#task'+(countmand-1)).val() != '' && $('#training'+(countmand-1)).val() != '' && $('#trtype'+(countmand-1)).val() != null) {
                $('#removebtn'+(countmand-1)).hide();
                $("#tnilist").append('<tr><td style="text-align:center;">'+countmand+'</td><td><textarea rows="2" name="task'+countmand+'" id="task'+countmand+'" class="form-control" placeholder="Insert Training" required></textarea></td><td><select name="targetsk'+countmand+'" id="targetsk'+countmand+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsk'+countmand+'" id="currentsk'+countmand+'" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gap'+countmand+'" id="gap'+countmand+'" class="form-control" readonly /></td><td><textarea rows="2" name="cause'+countmand+'" id="cause'+countmand+'" class="form-control" placeholder="Insert Cause" required></textarea></td><td><textarea rows="2" name="ask'+countmand+'" id="ask'+countmand+'" class="form-control" placeholder="Insert A/S/K" required></textarea></td><td><select name="trtype'+countmand+'" id="trtype'+countmand+'" class="form-control"><option selected disabled="disabled">-- Select L&D Method --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="2">3 - External / In-house</option></select></td><td><textarea rows="2" name="evaluate'+countmand+'" id="evaluate'+countmand+'" class="form-control" placeholder="Insert Evaluation Method" required></textarea></td><td><button type="button" id="removebtn'+countmand+'" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>');
            }else {
                alert('Please complete previous TNI details!');
                countmand--;
            }

            $('#gap'+countmand).val(0);

            $('#targetsk'+countmand).on('change', function() {
                var target = parseInt($('#targetsk'+countmand).val());
                var current = parseInt($('#currentsk'+countmand).val());

                var gap = target - current;
                $('#gap'+countmand).val(gap);
            });

            $('#currentsk'+countmand).on('change', function() {
                var target = parseInt($('#targetsk'+countmand).val());
                var current = parseInt($('#currentsk'+countmand).val());

                var gap = target - current;
                $('#gap'+countmand).val(gap);
            });

            $('#removebtn'+countmand).click(function() {
                countmand--;
                $('#tnalist tr:last').remove();
                if (countmand != 1) {
                    $('#removebtn'+countmand).show();
                }
                $('#mandatory').val(countmand);
            });

            $('#mandatory').val(countmand);
        });

        $('#mandatory').val(countmand);

        $(document).on('submit', '#tni_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"tni_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The TNI has been recorded.',
                            'success'
                        ).then(function() {
                            window.location = "tni_list.php";
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#tni_form')[0].reset();
        				})
                    }
                },
                complete: function () {
                    $("#spinner-div").hide();
                }
            })
        });
    </script>
</html>
<?php
    }else{
         header("Location: ../../login.php");
         exit();
    }
?>
