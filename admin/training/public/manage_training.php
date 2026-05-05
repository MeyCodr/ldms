<?php
    session_start();

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Learning and Development Management System</title>
        
        <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
        <script src="../../../asset/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="../../../asset/css/datepicker.css">
        <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
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

    <body onload="startTime()" style="background-image:url('../../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
				<div class="col-md-10">
    				<img src= "../../../asset/image/lndlogo.gif" height="50" width="290">
				</div>
				<div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

				</div>
			</div>
            <nav class="navbar navbar-inverse" >
                <div class="container-fluid ">
    				<ul class="nav navbar-nav">
                        <li><a href="../../dashboard.php">HOME</a></li>
						<li><a href="../../staff/staff.php">STAFF LIST</a></li>
						<li class="dropdown">
    						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> ALL TRAINING </a>
    						<ul class="dropdown-menu">
                                <li><a href="training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="../ojt/training_ojt.php">OJT</a></li>
                                <li><a href="../departmental/training_dept.php">DEPARTMENTAL</a></li>
    						</ul>
    					</li>
						<li><a href="../../attendance/training.php">MY TRAINING</a></li>
                        <li><a href="../../tna/tna_list.php">TNA</a></li>
						<li><a href="../../tni/tni_list.php">TNI</a></li>
						<li><a href="../../tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="../../password/password.php">CHANGE PASSWORD</a></li>
    				</ul>
    				<ul class="nav navbar-nav navbar-right">
    					<li class="dropdown">
    						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname']?> </a>
    						<ul class="dropdown-menu">
    							<li><a href="../../../logout.php">LOGOUT</a></li>
    						</ul>
    					</li>
    				</ul>
    			</div>
            </nav>
            <div id="spinner-div">
                <img src="../../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..." style="margin-top: 350px;"/>
            </div>
            <div class="row">
                <div class="col-md-12">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-1">
                                    <button type="button" name="back_training" id="back_training" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Training List</button>
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
                                <div class="col-md-11">
                                    <strong id="title">Add New Training</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">

                                </div>
                                <div class="col-md-6">
                                    <form method="post" id="training_form">
                                        <fieldset style="border-radius:10px;">
                                            <legend id="leg1">Add Training</legend>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Title</label>
                                                    <input type="text" name="title1" id="title1" class="form-control" placeholder="Insert Title" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Venue</label>
                                                    <input type="text" name="venue" id="venue" class="form-control" placeholder="Insert Venue" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Cost (RM)</label>
                                                    <input type="text" name="cost" id="cost" class="form-control" placeholder="Insert Training Cost" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label>HRDC</label>
                                                    <select class="form-control" id="hadc" name="hadc">
                                                        <option selected disabled="disabled">-- Select HRDC --</option>
                                                        <option value="YES">YES</option>
                                                        <option value="NO">NO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Platform</label>
                                                    <select class="form-control" id="platform" name="platform">
                                                        <option selected disabled="disabled">-- Select Platform --</option>
                                                        <option value="PHYSICAL">PHYSICAL</option>
                                                        <option value="ONLINE">ONLINE</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Function</label>
                                                    <select class="form-control" id="function" name="function">
                                                        <option selected disabled="disabled">-- Select Function --</option>
                                                        <option value="BUSINESS">BUSINESS</option>
                                                        <option value="DIGITAL">DIGITAL</option>
                                                        <option value="LEADERSHIP">LEADERSHIP</option>
                                                        <option value="PERSONAL EFFECTIVENESS">PERSONAL EFFECTIVENESS</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Start Date</label>
                                                    <input type="text" name="startdate" id="startdate" class="form-control" placeholder="Insert Start Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label>End Date</label>
                                                    <input type="text" name="enddate" id="enddate" class="form-control" placeholder="Insert End Date" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Start Time</label>
                                                    <input class="form-control" name="starttime" id="starttime" placeholder="Select Start Time" type="text" autocomplete="off"/>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>End Time</label>
                                                    <input class="form-control" name="endtime" id="endtime" placeholder="Select End Time" type="text" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Program</label>
                                                    <select class="form-control" id="program" name="program">
                                                        <option selected disabled="disabled">-- Select Program --</option>
                                                        <option value="EXT">EXTERNAL PUBLIC PROGRAM</option>
                                                        <option value="INTX">INTERNAL TRAINING BY EXTERNAL TRAINER</option>
                                                        <option value="INTI">INTERNAL TRAINING BY INTERNAL TRAINER</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6" id="external">
                                                    <label>Trainer Name</label>
                                                    <input type="text" name="externalname" id="externalname" class="form-control" placeholder="Insert Trainer Name" autocomplete="off" />
                                                </div>
                                                <div class="col-md-6" id="internal">
                                                    <label>Trainer Name</label>
                                                    <select name="internalname" id="internalname" class="form-control"></select>
                                                </div>
                                            </div>
                                            <div class="form-group" align="right">
                                                <input type="hidden" name="id" id="id" />
								                <input type="hidden" name="btn_action" id="btn_action" />
                                                <button type="submit" name="action" id="action" class="btn btn-info btn-xm" style="width: 100px;"><i class="fa fa-save"></i> Save</button>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                                <div class="col-md-2">

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

        $('#external').hide();
        $('#internal').hide();

        $('#program').on('change', function() {
            var type = this.value;
            if (type == 'EXT') {
                $('#external').show();
                $('#internal').hide();
            }else if (type == 'INTX') {
                $('#external').show();
                $('#internal').hide();
            }else if (type == 'INTI') {
                $('#external').hide();
                $('#internal').show();
            }
        });

        $('#startdate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        $('#enddate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        $('#starttime').timepicker({
            minuteStep: 1,
            showSeconds: false,
            showMeridian: false
        });

        $('#endtime').timepicker({
            minuteStep: 1,
            showSeconds: false,
            showMeridian: false
        });

        $(function(){
            $.post("fetch_training.php",{action:"load_trainer"},function(data){
                $('#internalname').html(data);
                $('#internalname').val(trainerin);
            });
        });

        var action1 = localStorage.getItem("setaction");
        var trainerin = '';

        if (action1 == 'addtraining') {
            $('#btn_action').val(action1);
        }else if (action1 == 'edittraining') {
            $('#training_form')[0].reset();
            var legend1 = document.getElementById("leg1");
            legend1 .innerHTML = "Edit Training";
            var title1 = document.getElementById("title");
            title1 .innerHTML = "Edit Training Info";
            var id = localStorage.getItem("setid");
            var action = 'fetch_training';
            $.ajax({
                url:"fetch_training.php",
                method:"POST",
                data:{id:id,action:action},
                dataType:"json",
                success:function(data) {
                    $('#title1').val(data.title);
                    $('#program').val(data.program);
                    $('#cost').val(data.cost);
                    $('#venue').val(data.venue);
                    $('#hadc').val(data.hadc);
                    $('#platform').val(data.platform);
                    $('#function').val(data.function);
                    $('#startdate').val(data.startdate);
                    $('#enddate').val(data.enddate);
                    $('#starttime').val(data.starttime);
                    $('#endtime').val(data.endtime);
                    if (data.program == 'EXT') {
                        $('#external').show();
                        $('#internal').hide();
                        $('#externalname').val(data.trainer);
                    }else if (data.program == 'INTX') {
                        $('#external').show();
                        $('#internal').hide();
                        $('#externalname').val(data.trainer);
                    }else if (data.program == 'INTI') {
                        $('#external').hide();
                        $('#internal').show();
                        trainerin = data.trainer;
                    }
                    $('#btn_action').val(action1)
                    $('#id').val(id);
                }
            })
        }

        $('#back_training').click(function(){
            window.location = "training.php";
        });

        $(document).on('submit', '#training_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"training_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The training has been added.',
                            'success'
                        ).then(function() {
                            $('#training_form')[0].reset();
        				})
                    }
                    else if((response.message) == 'update') {
                        swal(
                            'Edited!',
                            'The training has been edited.',
                            'success'
                        ).then(function() {
                            window.location = "training.php";
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#training_form')[0].reset();
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
         header("Location: ../../../login.php");
         exit();
    }
?>
