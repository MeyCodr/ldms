<?php
    session_start();

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Training Management System</title>
        
        <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
        <script src="../../../asset/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="../../../asset/css/datepicker.css">
        <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
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

    <body onload="startTime()" style="background-image:url('../../../asset/image/bg.jpg');zoom: 100%;">
        <br>
        <div class="container-fluid">
            <div class="row">
				<div class="col-md-10">
    				<!-- <img src= "../../../asset/image/ecrms-logo.png" height="60" width="120"> -->
				</div>
				<div id="txt" align="right" class="col-md-2" style="margin-top:43px;">

				</div>
			</div>
            <nav class="navbar navbar-inverse" >
                <div class="container-fluid ">
    				<ul class="nav navbar-nav">
                        <li><a href="../../dashboard.php">HOME</a></li>
						<li><a href="../../staff/staff.php">STAFF LIST</a></li>
						<li><a href="../training.php">TRAINING LIST</a></li>
						<li><a href="../../attendance/training.php">MY TRAINING</a></li>
						<li><a href="../../tna/tna_list.php">TNA</a></li>
						<li><a href="../../tni/tni_list.php">TNI</a></li>
						<li><a href="../../tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li class="dropdown">
    						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span>REPORT</a>
    						<ul class="dropdown-menu">
                                <li><a href="../../report/bsreport.php">BUDGET SUMMARY</a></li>
                                <li><a href="../../report/gtreport.php">GROUP TRAINING RECORD</a></li>
    						</ul>
    					</li>
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
                                    <button type="button" name="back_session" id="back_session" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Session List</button>
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
                                    <strong id="title">Add New Session</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">

                                </div>
                                <div class="col-md-6">
                                    <form method="post" id="session_form">
                                        <fieldset style="border-radius:10px;">
                                            <legend id="leg1">Add Session</legend>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Description</label>
                                                    <input type="text" name="description" id="description" class="form-control" placeholder="Insert Description" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Session Date</label>
                                                    <input type="text" name="date1" id="date1" class="form-control" placeholder="Insert Session Date" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Start Time</label>
                                                    <input class="form-control" name="starttime" id="starttime" placeholder="Select Start Time" type="text"/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>End Time</label>
                                                    <input type="text" name="endtime" id="endtime" class="form-control" placeholder="Insert End Time" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Venue</label>
                                                    <input type="text" name="venue" id="venue" class="form-control" placeholder="Insert Venue" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>External/Internal Trainer</label>
                                                <select name="trainertype" id="trainertype" class="form-control">
                                                    <option selected disabled="disabled">-- Select Type --</option>
                                                    <option value="EXTERNAL">EXTERNAL</option>
                                                    <option value="INTERNAL">INTERNAL</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="external">
                                                <label>Trainer Name</label>
                                                <input type="text" name="externalname" id="externalname" class="form-control" placeholder="Insert Trainer Name" autocomplete="off" />
                                            </div>
                                            <div class="form-group" id="internal">
                                                <label>Trainer Name</label>
                                                <select name="internalname" id="internalname" class="form-control"></select>
                                            </div>
                                            <div class="form-group" align="right">
                                                <input type="hidden" name="id" id="id" />
                                                <input type="hidden" name="trainingid" id="trainingid" />
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
				<div class="col-md-4" align="right">
                    <div class="col-md-6" align="center">
                        <img src= "../../../asset/image/drbhcm.png" style="margin-top:-35px;margin-left:60px" width="160px">
                    </div>
					<div class="col-md-6" align="left">
                        <img src= "../../../asset/image/phn_logo.png" style="margin-top:-7px;margin-left:-30px" width="130px">
                    </div>
				</div>
                <div class="col-md-4">
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

        $(function(){
            $.post("fetch_session.php",{action:"load_staff"},function(data){
                $('#internalname').html(data);
            });
        });

        var trainingid = localStorage.getItem("settrainingid");

        $(document).ready(function(){
            var action1 = localStorage.getItem("setaction");

            if (action1 == 'addsession') {
                $('#trainingid').val(trainingid);
                $('#btn_action').val(action1);
            }else if (action1 == 'editsession') {
                var legend1 = document.getElementById("leg1");
                legend1 .innerHTML = "Edit Session";
                var title1 = document.getElementById("title");
                title1 .innerHTML = "Edit Session Info";
                var id = localStorage.getItem("setid");
                var action = 'fetch_session';
                $.ajax({
                    url:"fetch_session.php",
                    method:"POST",
                    data:{id:id,action:action},
                    dataType:"json",
                    success:function(data) {
                        $('#description').val(data.description);
                        $('#date1').val(data.date);
                        $('#starttime').val(data.starttime);
                        $('#endtime').val(data.endtime);
                        $('#venue').val(data.venue);
                        $('#trainertype').val(data.trainer);
                        if (data.trainer == 'EXTERNAL') {
                            $('#externalname').val(data.trainername);
                            $('#external').show();
                            $('#internal').hide();
                        }else if (data.trainer == 'INTERNAL') {
                            $('#external').hide();
                            $('#internal').show();
                            $('#internalname').val(data.trainername);
                        }
                        $('#btn_action').val(action1);
                        $('#id').val(id);
                    }
                })
            }
        });

        $('#external').hide();
        $('#internal').hide();

        $('#trainertype').on('change', function() {
            var type = this.value;
            if (type == 'EXTERNAL') {
                $('#external').show();
                $('#internal').hide();
            }else if (type == 'INTERNAL') {
                $('#external').hide();
                $('#internal').show();
            }
        });

        $('#date1').datepicker({
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

        

        $('#back_session').click(function(){
            window.location = "view_session.php";
            localStorage.setItem("setid", trainingid);
        });

        $(document).on('submit', '#session_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"session_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The session has been added.',
                            'success'
                        ).then(function() {
                            $('#session_form')[0].reset();
        				})
                    }
                    else if((response.message) == 'update') {
                        swal(
                            'Edited!',
                            'The session has been edited.',
                            'success'
                        ).then(function() {
                            window.location = "view_session.php";
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#session_form')[0].reset();
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
