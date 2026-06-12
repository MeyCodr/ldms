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
        <script src="../../../asset/js/bootstrap.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

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
						<li><a href="../../organization/org.php">ORGANIZATION</a></li>
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
            <div class="row">
                <div class="col-md-12">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-1">
                                    <button type="button" name="back_training" id="back_training" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Training List</button>
                                </div>
                                <div class="col-md-10">
                                    
                                </div>
                                <div class="col-md-1" align="right">
                                    <button type="button" name="add_session" id="add_session" class="btn btn-success btn-md">Add Session <i class="far fa-arrow-alt-circle-right"></i> </button>
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
                                <div class="col-md-12">
                                    <strong>Session List</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <table id="sessionlist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Description</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Venue</th>
                                                <th>External/Internal</th>
                                                <th>Trainer</th>
                                                <th width="150px">Action</th>
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

        $('#back_training').click(function(){
            window.location = "../training.php";
        });

        var trainingid = localStorage.getItem("setid");

        $('#add_session').click(function(){
            localStorage.setItem("setaction", 'addsession');
            localStorage.setItem("settrainingid", trainingid);
            window.location = "manage_session.php";
        });

        function fetch_data(action,trainingid){
            var sessiondataTable = $('#sessionlist').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "pageLength": 10,
                "info": true,
                "ajax":{
                    url:"fetch_session.php",
                    type:"POST",
                    dataSrc: '',
                    data : {action:action,trainingid:trainingid},
                },
                "columns": [
                    {
                        "data": "id",
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        "data": "description"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "starttime"
                    },
                    {
                        "data": "endtime"
                    },
                    {
                        "data": "venue"
                    },
                    {
                        "data": "trainer"
                    },
                    {
                        "data": "trainername"
                    },
                    {
                        "data": "btnedit"
                    }
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,2,3,4,6,8] }
                ]
            });
        }

        fetch_data('load_session',trainingid);

        $(document).on('click', '.edit', function(){
            var id = $(this).attr("id");
            localStorage.setItem("setaction", 'editsession');
            localStorage.setItem("setid", id);
            window.location = "manage_session.php";
        });

        $(document).on('click', '.delete', function(){
            var id = $(this).attr("id");
            var btn_action = 'deletesession';
            swal({
                title: "Delete Session?",
                text: "Are you sure?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Confirm"]
            })
            .then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        url:"session_action.php",
                        method:"POST",
                        data:{id:id,btn_action:btn_action},
                        success:function(data)
                        {
                            var response = JSON.parse(data)
                            if((response.message) == 'delete') {
                                swal(
                                    'Deleted!',
                                    'The session has been deleted.',
                                    'success'
                                )
                                $('#sessionlist').DataTable().destroy();
                                fetch_data('load_session',trainingid);
                            }
                            else if((response.message) == 'error') {
                                swal(
                                    'Not Deleted!',
                                    'The session cannot be deleted. Please refer the IT.',
                                    'error'
                                )
                                $('#sessionlist').DataTable().destroy();
                                fetch_data('load_session',trainingid);
                            }
                        }
                    });
                }else{
                    swal("Cancelled", "The session has not been deleted", "error");
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
