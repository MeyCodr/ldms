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
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
        <link rel="stylesheet" href="../../../asset/css/datepicker.css">
        <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
        <script type="text/javascript" src="../../../asset/js/pdfmake.min.js"></script>
		<script type="text/javascript" src="../../../asset/js/html2canvas.min.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

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
                                <li><a href="../public/training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="../ojt/training_ojt.php">OJT</a></li>
                                <li><a href="training_dept.php">DEPARTMENTAL</a></li>
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
            <div class="row">
                <div class="col-md-12">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-12" style="margin-top: 10px;">
                                    <strong>All Training \ <a href="training_dept.php">DEPARTMENTAL</a></strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-striped">
                                        <th>
                                            <div class="col-md-1"></div>
                                            <div class="col-md-10">
                                                <div class="col-md-3">
                                                    <select name="department" id="department" class="form-control"></select>
                                                </div>
												<div class="col-md-3">
                                                    <select name="section" id="section" class="form-control"></select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" name="startdate" id="startdate" class="form-control" placeholder="Insert Start Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" name="enddate" id="enddate" class="form-control" placeholder="Insert End Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-1" align="right">
                                                    <button type="button" name="filter_date" id="filter_date" class="btn btn-info btn-md">Filter <i class="fa fa-search"></i> </button>
                                                </div>
                                                <div class="col-md-1" align="left">
                                                    <button type="button" name="clear_filter" id="clear_filter" class="btn btn-info btn-md">Clear <i class="fa fa-times"></i> </button>
                                                </div>
                                            </div>
                                            <div class="col-md-1"></div>
                                        </th>
                                    </table>
                                </div>
							</div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <table id="traininglist" class="table table-bordered table-striped">
                                        <thead>
                                            <!-- <tr id="reportheader"><th colspan="11">gg</th></tr> -->
                                            <tr>
                                                <th>No.</th>
                                                <th>Department</th>
												<th>Type</th>
                                                <th>Title</th>
                                                <th width="70px">Start Date</th>
                                                <th width="70px">End Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Venue</th>
                                                <th>Total Days</th>
                                                <th>Total Hours</th>
                                                <th>Participant</th>
                                                <th>Total Man Hour</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="9">Summary Calculation</th>
                                                <th><span id="totalallday"></span></th>
                                                <th><span id="totalallhour"></span></th>
                                                <th><span id="totalallparty"></span></th>
                                                <th><span id="totalmanpower"></span></th>
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
		<div id="partyModal" class="modal fade">
			<div class="modal-dialog modal-lg" style="width: 1400px;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-plus"></i> View Participant</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12 table-responsive">
								<legend>List of Permanent Staff</legend>
								<table id="participantlist" class="table table-bordered table-striped" style="width:100%;">
									<thead>
										<tr>
											<th>No.</th>
											<th>Staff Number</th>
											<th>Staff Name</th>
											<th>Department</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 table-responsive">
								<legend>List of Contract Staff</legend>
								<table id="contractlist" class="table table-bordered table-striped" style="width:100%;">
									<thead>
										<tr>
											<th>No.</th>
											<th>Staff Number</th>
											<th>Staff Name</th>
											<th>Department</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info btn-xm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
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

        $(function(){
            $.post("fetch_training.php",{action:"load_department"},function(data){
                $('#department').html(data);
            });
        });
		
		$('#department').on('change', function() {
            var department = this.value;
            $(function(){
                $.post("fetch_training.php",{action:"load_section",department:department},function(data){
                    $('#section').html(data);
                });
            });
        });

        $("#pdfbutton").hide();

        function fetch_data(action,department,startdate,enddate){
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
                    data : {action:action,department:department,startdate:startdate,enddate:enddate},
                },
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
                        "data": "trtype"
                    },
                    {
                        "data": "title"
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
                        "data": "venue"
                    },
                    {
                        "data": "totalday"
                    },
                    {
                        "data": "totalhour"
                    },
                    {
                        "data": "totalman"
                    },
                    {
                        "data": "totalmanhour"
                    }
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,4,5,6,7,9,10,11,12] }
                ],
                fnFooterCallback: function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    var api = this.api(), data;
 
                    // converting to interger to find total
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
                    };
 
                    // computing column Total of the complete result 
                    var totalallday = api.column(9).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);    

                    var totalallhour = api.column(10).data().reduce(function (a, b) {
                        return parseFloat(intVal(a) + intVal(b)).toFixed(2);
                    }, 0); 

                    var totalallmanpower = api.column(12).data().reduce(function (a, b) {
                        return (intVal(a) + intVal(b)).toFixed(2);
                    }, 0);
                    
                    // Update footer by showing the total with the reference of the column index 
	                $( api.column(9).footer() ).html(totalallday);
                    $( api.column(10).footer() ).html(totalallhour);
                    $( api.column(12).footer() ).html(totalallmanpower);
                },
            });
        }

        function getTotalSummary(action,department,startdate,enddate){
            $.ajax({
                url:"fetch_training.php",
                type:"POST",
                data:{action:action,department:department,startdate:startdate,enddate:enddate},
                dataType:"JSON",
                success:function(data)
                {
                    $('#totalallparty').text(data.totalmans);
                }
            })
        }

        $('#filter_date').click(function(){
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();
            var department = $('#department').val();

            if (department != '' && startdate == '') {
                $('#traininglist').DataTable().destroy();
                fetch_data('load_dept',department);
                getTotalSummary('load_deptsummary',department);
                $("#pdfbutton").show();
            }else if (department != '' && startdate != '') {
                $('#traininglist').DataTable().destroy();
                fetch_data('load_dept',department,startdate,enddate);
                getTotalSummary('load_deptsummary',department,startdate,enddate);
                $("#pdfbutton").show();
            }else {
                alert('Please select which department to filter department only or select start date and end date to filter by date only or select all filter to filter by department and date!');
            }
        });

        $('#clear_filter').click(function(){
            $('#traininglist').DataTable().destroy();
            fetch_data('load_dept','','','');
            $('#totalallparty').text('0');
            $('#startdate').val('');
            $('#enddate').val('');
            $('#department').val('');
            $("#pdfbutton").hide();
        });

        fetch_data('load_dept','','','');
        $('#totalallparty').text('0');
		
		$(document).on('click', '.linkparty', function(){
            $("#partyModal").modal('show');
			var trainingid = $(this).attr("id");
            var department = $('#department').val();
            $('#participantlist').DataTable().destroy();
			fetch_permanent('load_participant',trainingid,department);
            $('#contractlist').DataTable().destroy();
			fetch_contract('load_contract',trainingid,department);
        });
        
        function fetch_permanent(action,trainingid,department){
            var participantdataTable = $('#participantlist').DataTable({
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
                    data : {action:action,trainingid:trainingid,department:department},
                },
                "columns": [
                    {
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
                        "data": "department"
                    },
                    {
                        "data": "status"
                    },
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,1,4] }
                ]
            });
        }

        function fetch_contract(action,trainingid,department){
            var contractdataTable = $('#contractlist').DataTable({
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
                    data : {action:action,trainingid:trainingid,department:department},
                },
                "columns": [
                    {
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
                        "data": "department"
                    },
                    {
                        "data": "status"
                    },
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,1,4] }
                ]
            });
        }

        // $('#pdfbutton').click(function() {
        //     var department = $('#department').val();
        //     var startdate = $('#startdate').val();
        //     var enddate = $('#enddate').val();

        //     if (department != '' && startdate == '') {
        //         title = "OJT Report ("+department+").pdf";

        //     }else if (department == '' && startdate != '') {
        //         title = "OJT Report ("+startdate+" - "+enddate+").pdf";

        //     }else if (department != '' && startdate != '') {
        //         title = "OJT Report ("+department+")("+startdate+" - "+enddate+").pdf";

        //     }else {
        //         title = "OJT Report (ALL DEPARTMENT).pdf";
        //     }

        //     html2canvas($("#traininglist")[0],{
		// 		onrendered:function(canvas){
		// 			var data=canvas.toDataURL();
		// 			var docDefinition={
		// 				content:[{
		// 					image:data,
		// 					width:515
		// 				}]
		// 			};
		// 			pdfMake.createPdf(docDefinition).download(title);
		// 		}
		// 	})
        // });
    </script>
</html>
<?php
    }else{
         header("Location: ../../../login.php");
         exit();
    }
?>
