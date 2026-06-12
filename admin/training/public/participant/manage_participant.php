<?php
    session_start();

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Learning and Development Management System</title>
        
        <script src="../../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../../asset/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
        <script src="../../../../asset/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="../../../../asset/css/datepicker.css">
        <script src="../../../../asset/js/bootstrap-datepicker1.js"></script>
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

    <body onload="startTime()" style="background-image:url('../../../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
				<div class="col-md-10">
    				<img src= "../../../../asset/image/lndlogo.gif" height="50" width="290">
				</div>
				<div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

				</div>
			</div>
            <nav class="navbar navbar-inverse" >
                <div class="container-fluid ">
    				<ul class="nav navbar-nav">
                        <li><a href="../../../dashboard.php">HOME</a></li>
						<li><a href="../../../staff/staff.php">STAFF LIST</a></li>
						<li class="dropdown">
    						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> ALL TRAINING </a>
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
                        <li><a href="../../../organization/org.php">ORGANIZATION</a></li>
                        <li><a href="../../../password/password.php">CHANGE PASSWORD</a></li>
    				</ul>
    				<ul class="nav navbar-nav navbar-right">
    					<li class="dropdown">
    						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname']?> </a>
    						<ul class="dropdown-menu">
    							<li><a href="../../../../logout.php">LOGOUT</a></li>
    						</ul>
    					</li>
    				</ul>
    			</div>
            </nav>
            <div id="spinner-div">
                <img src="../../../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..." style="margin-top: 350px;"/>
            </div>
            <div class="row">
                <div class="col-md-12">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-1">
                                    <button type="button" name="back_participant" id="back_participant" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Participant List</button>
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
                                    <strong id="title">Add New Participant</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">

                                </div>
                                <div class="col-md-6">
                                    <form method="post" id="participant_form">
                                        <fieldset style="border-radius:10px;">
                                            <legend id="leg1">Add Participant</legend>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Department</label>
                                                    <select name="department" id="department" class="form-control"></select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <label>Participant</label>
                                                    <input type="text" name="participant" id="participant" list="participant1" class="form-control" placeholder="-- Select Participant --" autocomplete="off">
                                                    <datalist id="participant1"></datalist>
                                                </div>
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

        var action1 = localStorage.getItem("setaction");
        var trainingid = localStorage.getItem("settrainingid");

        $(function(){
            $.post("fetch_participant.php",{action:"load_department"},function(data){
                $('#department').html(data);
            });
        });

        $('#department').change(function () {
            var department = $('#department').val();
            $('#participant').val('');
            $.post("fetch_participant.php",{action:"load_user",department:department},function(data){
                $('#participant1').html(data);
            });
        });

        if (action1 == 'addparticipant') {
            $('#trainingid').val(trainingid);
            $('#btn_action').val(action1);
        }

        $('#back_participant').click(function(){
            window.location = "view_participant.php";
        });

        $(document).on('submit', '#participant_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"participant_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The participant has been added.',
                            'success'
                        ).then(function() {
                            $('#participant_form')[0].reset();
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#participant_form')[0].reset();
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
         header("Location: ../../../../login.php");
         exit();
    }
?>