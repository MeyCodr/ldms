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
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="../../asset/css/datepicker.css">
        <script src="../../asset/js/bootstrap-datepicker1.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

    <body onload="startTime()" style="background-image:url('../../asset/image/bg-new.png');zoom: 75%;">
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
                                <li><a href="training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="training_ojt.php">OJT</a></li>
    						</ul>
    					</li>
						<li><a href="../attendance/training.php">MY TRAINING</a></li>
						<li><a href="../tna/tna_list.php">TNA</a></li>
						<li><a href="../tni/tni_list.php">TNI</a></li>
						<li><a href="../tna/tna_summary.php">TNA SUMMARY</a></li>
						<li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <!-- <li><a href="../report/report.php">SUMMARY REPORT</a></li> -->
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
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-9" style="margin-top: 10px;">
                                    <strong>All Training / Public/Inhouse</strong>
                                </div>
                                <div class="col-md-2" align="right">
                                    <button type="button" name="download" id="download" class="btn btn-info btn-md">Download Report <i class="fa fa-download"></i> </button>
                                </div>
                                <div class="col-md-1" align="right">
                                    <button type="button" name="add_training" id="add_training" class="btn btn-success btn-md">Add Training <i class="far fa-arrow-alt-circle-right"></i> </button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-bordered table-striped">
                                        <th>
                                            <fieldset>
                                                <legend align="left">Legend: </legend>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <label>View Participant Button</label>
                                                        <button type="submit" class="btn btn-info btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-search"></i></button>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label>Edit Training Button</label>
                                                        <button type="submit" class="btn btn-warning btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-edit"></i></button>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label>Delete Training Button</label>
                                                        <button type="submit" class="btn btn-danger btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </th>
                                    </table>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-striped">
                                        <th>
                                            <div class="col-md-3"></div>
                                            <div class="col-md-6">
                                                <div class="col-md-4">
                                                    <input type="text" name="startdate" id="startdate" class="form-control" placeholder="Insert Start Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="enddate" id="enddate" class="form-control" placeholder="Insert End Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-2" align="right">
                                                    <button type="button" name="filter_date" id="filter_date" class="btn btn-info btn-md">Filter <i class="fa fa-search"></i> </button>
                                                </div>
                                                <div class="col-md-2" align="left">
                                                    <button type="button" name="clear_filter" id="clear_filter" class="btn btn-info btn-md">Clear <i class="fa fa-times"></i> </button>
                                                </div>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </th>
                                    </table>
                                </div>
							</div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <table id="traininglist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Training Code</th>
                                                <th>Title</th>
                                                <th>Program</th>
                                                <th width="70px">Start Date</th>
                                                <th width="70px">End Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>HRDC</th>
                                                <th>Platform</th>
                                                <th>Function</th>
                                                <th>Cost (RM)</th>
                                                <th>Total Days</th>
                                                <th>Total Hours</th>
                                                <th>Total Participants</th>
                                                <th>Total Man Hours</th>
                                                <th width="110px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="11">Summary Calculation</th>
                                                <th><span id="totalallcost"></span></th>
                                                <th><span id="totalallday"></span></th>
                                                <th><span id="totalallhour"></span></th>
                                                <th><span id="totalallparty"></span></th>
                                                <th><span id="totalmanpower"></span></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
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

        $('#add_training').click(function(){
            localStorage.setItem("setaction", 'addtraining');
            window.location = "manage_training.php";
        });

        $('#download').click(function(){
            window.location = "report.php";
        });

        function fetch_data(action,startdate,enddate){
            var trainingdataTable = $('#traininglist').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "pageLength": 10,
                "info": true,
                "ajax":{
                    url:"fetch_training.php",
                    type:"POST",
                    dataSrc: '',
                    data : {action:action,startdate:startdate,enddate:enddate},
                },
                "columns": [
                    {
                        "data": "id",
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        "data": "trainingcode"
                    },
                    {
                        "data": "title"
                    },
                    {
                        "data": "program"
                    },
                    {
                        "data": "startdate"
                    },
                    {
                        "data": "enddate"
                    },
                    {
                        "data": "starttime"
                    },
                    {
                        "data": "endtime"
                    },
                    {
                        "data": "hadc"
                    },
                    {
                        "data": "platform"
                    },
                    {
                        "data": "function"
                    },
                    {
                        "data": "cost"
                    },
                    {
                        "data": "totalday"
                    },
                    {
                        "data": "totalhour"
                    },
                    {
                        "data": "participant"
                    },
                    {
                        "data": "totalmanhour"
                    },
                    {
                        "data": "btnedit"
                    }
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,4,5,6,7,8,9,11,12,13,14,15,16] }
                ],
                fnFooterCallback: function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    var api = this.api(), data;
 
                    // converting to interger to find total
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
                    };
 
                    // computing column Total of the complete result 
                    var totalallmanpower = api.column(15).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    
                    // Update footer by showing the total with the reference of the column index 
                    $( api.column(15).footer() ).html(totalallmanpower);
                }
            });
        }

        function getTotalSummary(action,startdate,enddate){
            $.ajax({
                url:"fetch_training.php",
                type:"POST",
                data:{action:action,startdate:startdate,enddate:enddate},
                dataType:"JSON",
                success:function(data)
                {
                    $('#totalallcost').text(data.totalcost);
                    $('#totalallday').text(data.totaldays);
                    $('#totalallhour').text(data.totalhours);
                    $('#totalallparty').text(data.totalmans);
                }
            })
        }

        fetch_data('load_training','','');
        getTotalSummary('load_totalsummary','','');

        $('#filter_date').click(function(){
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();

            $('#traininglist').DataTable().destroy();
            fetch_data('load_training',startdate,enddate);
            getTotalSummary('load_totalsummary',startdate,enddate);
        });

        $('#clear_filter').click(function(){
            $('#traininglist').DataTable().destroy();
            fetch_data('load_training','','');
            getTotalSummary('load_totalsummary','','');
            $('#startdate').val('');
            $('#enddate').val('');
        });
        
        $(document).on('click', '.edit', function(){
            var id = $(this).attr("id");
            localStorage.setItem("setaction", 'edittraining');
            localStorage.setItem("setid", id);
            window.location = "manage_training.php";
        });

        $(document).on('click', '.viewparticipant', function(){
            var id = $(this).attr("id");
            localStorage.setItem("setid", id);
            window.location = "participant/view_participant.php";
        });

        $(document).on('click', '.delete', function(){
            var id = $(this).attr("id");
            var btn_action = 'deletetraining';
            swal({
                title: "Delete Training?",
                text: "Are you sure?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Confirm"]
            })
            .then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        url:"training_action.php",
                        method:"POST",
                        data:{id:id, btn_action:btn_action},
                        success:function(data)
                        {
                            var response = JSON.parse(data)
                            if((response.message) == 'delete') {
                                swal(
                                    'Deleted!',
                                    'The training has been deleted.',
                                    'success'
                                )
                                $('#traininglist').DataTable().destroy();
                                fetch_data('load_training');
                                getTotalSummary('load_totalsummary','','');
                            }
                            else if((response.message) == 'error') {
                                swal(
                                    'Not Deleted!',
                                    'The training cannot be deleted. Please refer the IT.',
                                    'error'
                                )
                                $('#traininglist').DataTable().destroy();
                                fetch_data('load_training');
                                getTotalSummary('load_totalsummary','','');
                            }
                        }
                    });
                }else{
                    swal("Cancelled", "The training has not been deleted", "error");
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
