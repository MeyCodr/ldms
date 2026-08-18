<?php
    session_start();

    $canViewSkillMatrix = !empty($_SESSION['is_sm_user']) || (
        isset($_SESSION['designation'], $_SESSION['hodid'], $_SESSION['role'], $_SESSION['usertype'])
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && (int) $_SESSION['hodid'] != 0
        && $_SESSION['role'] == 'CLERK'
        && $_SESSION['usertype'] == 'MAIN'
    );

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
						<li><a href="../attendance/pme.php">PME</a></li>
                        <li><a href="../tna/tna_list.php">TNA</a></li>
                        <?php if ($canViewSkillMatrix) { ?><li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li><?php } ?>
                        <li><a href="../../../admin/archive/archive.php">ARCHIVE</a></li>
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
                        <div class="panel-body" style="padding:12px 14px;">
                            <strong style="margin-right:10px;"><i class="fa fa-file-excel"></i> Bulk Update Contract Staff via Excel</strong>
                            <a href="export_staff_template.php" class="btn btn-default btn-sm">
                                <i class="fa fa-download"></i> Download Template
                            </a>
                            <span style="margin-left:10px;">
                                <input type="file" id="staff-file-input" accept=".xls,.xlsx" style="display:inline-block;width:auto;">
                                <button class="btn btn-primary btn-sm" id="btn-upload-staff" onclick="uploadStaffTemplate()">
                                    <i class="fa fa-upload"></i> Upload &amp; Update
                                </button>
                            </span>
                            <div class="text-muted" style="font-size:11px;margin-top:6px;">
                                Download the template first, fill in any of Staff Name, Gender, Division, Department, Section and/or Status columns without changing the Staff No column, then upload it here to bulk-update contract staff. Use the dropdowns provided in each cell &mdash; picking a Division narrows the Department dropdown to that division, and picking a Department narrows the Section dropdown to that department. Blank cells are left unchanged.
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
                                    <table id="userlist" class="table table-bordered table-striped" style="width:100%;">
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

        function uploadStaffTemplate() {
            var fileInput = document.getElementById('staff-file-input');
            if (!fileInput.files || fileInput.files.length === 0) {
                swal('Error', 'Please choose a file to upload first.', 'error');
                return;
            }

            var formData = new FormData();
            formData.append('import_file', fileInput.files[0]);

            var $btn = $('#btn-upload-staff');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

            $.ajax({
                url: 'import_staff.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    fileInput.value = '';
                    if (res.message !== 'done') {
                        swal('Error', res.detail || 'Import failed.', 'error');
                        return;
                    }
                    var summaryText = res.updated + ' staff record(s) updated, ' + res.skipped + ' row(s) skipped (no changes).';
                    if (res.errors && res.errors.length > 0) {
                        summaryText += '\n\n' + res.errors.length + ' issue(s):\n' + res.errors.join('\n');
                    }
                    swal('Import Complete', summaryText, (res.errors && res.errors.length > 0) ? 'warning' : 'success');
                    $('#userlist').DataTable().destroy();
                    fetch_data('load_staff');
                },
                error: function () {
                    swal('Error', 'Upload failed. Please try again.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Upload &amp; Update');
                }
            });
        }

        function fetch_data(action){
            var userdataTable = $('#userlist').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
                "pageLength": 10,
                "info": true,
                "order": [[9, "asc"]],
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
            swal({
                title: "Delete User?",
                text: "Are you sure?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Confirm"]
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
