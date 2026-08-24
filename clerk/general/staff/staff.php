<?php
    session_start();

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'CLERK')) {

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
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

    <body onload="startTime()" style="background-image:url('../../../asset/image/bg-new.png');zoom: 75%;">
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
                        <li><a href="../dashboard.php">HOME</a></li>
						<li><a href="staff.php">CONTRACT STAFF LIST</a></li>
						<li><a href="../training/training_ojt.php">ALL TRAINING</a></li>
						<li><a href="../attendance/training.php">MY TRAINING</a></li>
                        <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
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
                                <div class="col-md-11" style="margin-top: 10px;">
                                    <strong>Staff List</strong>
                                </div>
                                <div class="col-md-1" align="right">
                                    <button type="button" name="add_staff" id="add_staff" class="btn btn-success btn-md">Add Staff <i class="far fa-arrow-alt-circle-right"></i> </button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-sm-12 table-responsive">
                                    <table id="userlist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Staff No.</th>
                                                <th>Staff Name</th>
                                                <th>Gender</th>
                                                <th>Division</th>
                                                <th>Department</th>
												<th>Section</th>
                                                <th>Total Training Hours</th>
                                                <th>Total Trainer's Hours</th>
												<th>Status</th>
                                                <th width="145px">Action</th>
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

        $('#add_staff').click(function(){
            localStorage.setItem("setaction", 'adduser');
            window.location = "manage_staff.php";
        });

        function fetch_data(action){
            var userdataTable = $('#userlist').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "pageLength": 10,
                "info": true,
                "ajax":{
                    url:"fetch_staff.php",
                    type:"POST",
                    dataSrc: '',
                    data : {action:action},
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
                        "data": "gender"
                    },
                    {
                        "data": "division"
                    },
                    {
                        "data": "department"
                    },
					{
                        "data": "section"
                    },
                    {
                        "data": "sumtotalhours"
                    },
                    {
                        "data": "trainertotalhour"
                    },
					{
                        "data": "status"
                    },
                    {
                        "data": "btnedit"
                    }
                ],
                "columnDefs": [
                    { className: 'text-center', targets: [0,6,7,8,9] }
                ]
            });

        console.log("User List", userdataTable);

        }


        fetch_data('load_staff');

        $(document).on('click', '.edit', function(){
            var id = $(this).attr("id");
            localStorage.setItem("setaction", 'edituser');
            localStorage.setItem("setid", id);
            window.location = "manage_staff.php";
        });

        $(document).on('click', '.delete', function(){
            var id = $(this).attr("id");
            var btn_action = 'deleteuser';
            // Ask the server what this delete would destroy before confirming.
            // A staff's skill matrix is removed by an ON DELETE CASCADE, so a
            // bare "Are you sure?" hides real, unrecoverable data loss.
            $.ajax({
                url: "staff_action.php",
                method: "POST",
                data: { id: id, btn_action: 'delete_impact' },
                dataType: 'json'
            })
            .always(function (res) {
                var who = "this user";
                var body = "Are you sure?";
                var danger = false;

                // Blocked outright: say so here rather than letting the admin
                // confirm a delete the server is going to refuse anyway.
                if (res && res.message === 'ok' && res.blockers && res.blockers.length > 0) {
                    swal({
                        title: "Cannot Delete " + res.staffno,
                        text: res.staffname + "\n\nDeleting would lose data:\n\n- "
                            + res.blockers.join("\n- ")
                            + "\n\nEdit the staff and set Status to RESIGN instead. That keeps their"
                            + " training history and removes them from active headcount.",
                        icon: "error",
                        buttons: ["Close", "Edit Staff"]
                    }).then(function (goEdit) {
                        if (goEdit) {
                            localStorage.setItem("setaction", 'edituser');
                            localStorage.setItem("setid", id);
                            window.location = "manage_staff.php";
                        }
                    });
                    return;
                }

                if (res && res.message === 'ok') {
                    who = res.staffno + " - " + res.staffname;
                    if (res.warnings && res.warnings.length > 0) {
                        danger = true;
                        body = who + "\n\nDeleting will:\n\n- " + res.warnings.join("\n- ")
                             + "\n\nThis cannot be undone.";
                    } else {
                        body = who + "\n\nThis staff has no training, TNA, PME or skill matrix records.\nAre you sure?";
                    }
                }
                swal({
                    title: danger ? "Delete User - DATA WILL BE LOST" : "Delete User?",
                    text: body,
                    icon: "warning",
                    dangerMode: true,
                    buttons: ["Cancel", danger ? "Delete anyway" : "Confirm"]
                })
            .then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        url:"staff_action.php",
                        method:"POST",
                        data:{id:id, btn_action:btn_action},
                        success:function(data)
                        {
                            var response = JSON.parse(data)
                            if((response.message) == 'delete') {
                                swal(
                                    'Deleted!',
                                    'The user has been deleted.',
                                    'success'
                                )
                                $('#userlist').DataTable().destroy();
                                fetch_data('load_staff');
                            }
                            else if((response.message) == 'error') {
                                swal(
                                    'Not Deleted!',
                                    'The user cannot be deleted. Please refer the IT.',
                                    'error'
                                )
                                $('#userlist').DataTable().destroy();
                                fetch_data('load_staff');
                            }
                        }
                    });
                }else{
                    swal("Cancelled", "The user has not been deleted", "error");
                }
                });
            });
        });
    </script>
</html>
<?php
    }else{
         header("Location: ../../login.php");
         exit();
    }
?>