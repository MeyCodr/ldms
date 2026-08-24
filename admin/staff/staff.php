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
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
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
						<li><a href="staff.php">STAFF LIST</a></li>
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
                        <li><a href="../organization/org.php">ORGANIZATION</a></li>
                        <li><a href="../archive/archive.php">ARCHIVE</a></li>
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
                <div class="col-sm-12">
                    <table class="table table-bordered table-striped">
                        <th>
                            <fieldset>
                                <legend align="left">Legend: </legend>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <label>Edit Staff Button</label>
                                        <button type="submit" class="btn btn-warning btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-edit"></i></button>
                                    </div>
                                    <div class="col-sm-4">
                                        <label>Delete Staff Button</label>
                                        <button type="submit" class="btn btn-danger btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-trash"></i></button>
                                    </div>
                                    <div class="col-sm-4">
                                        <label>Reset Password Button</label>
                                        <button type="submit" class="btn btn-success btn-sm" style="margin-left:5px;opacity:1;" disabled><i class="fa fa-edit"></i></button>
                                    </div>
                                </div>
                            </fieldset>
                        </th>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body" style="padding:12px 14px;">
                            <strong style="margin-right:10px;"><i class="fa fa-file-import"></i> Import / Update Staff</strong>
                            <a href="export_staff_csv.php" class="btn btn-default btn-sm" title="Every current staff record as CSV - edit and upload it straight back">
                                <i class="fa fa-download"></i> Current Staff (CSV)
                            </a>
                            <a href="export_datejoin_template.php" class="btn btn-default btn-sm" title="Same data as an Excel workbook, with dropdowns in each cell">
                                <i class="fa fa-download"></i> Current Staff (Excel)
                            </a>
                            <a href="export_staff_csv_template.php" class="btn btn-default btn-sm" title="Empty CSV with just the column headers">
                                <i class="fa fa-download"></i> Blank CSV
                            </a>
                            <span style="margin-left:10px;">
                                <input type="file" id="staff-import-input" accept=".csv,.txt,.xls,.xlsx" style="display:inline-block;width:auto;">
                                <button class="btn btn-primary btn-sm" id="btn-preview-staff-import" onclick="previewStaffImport()">
                                    <i class="fa fa-search"></i> Upload &amp; Preview
                                </button>
                            </span>
                            <div class="text-muted" style="font-size:11px;margin-top:6px;">
                                Accepts <strong>.csv, .xls and .xlsx</strong>. Column headers are detected automatically from the first row, so columns can be in any order and extra columns are ignored &mdash; the templates are a convenience, not a requirement. Recognised headers: <strong>Staff No</strong> (required), Staff Name, Email, Gender, Designation, Division, Department, Section, Status, Date Join, Plant, Grade, HOD. A Staff No that does not exist yet is created (default password <code>P@ss1234</code>); one that already exists is updated, and blank cells are left unchanged. Nothing is saved until you confirm the preview.
                                <br>The <strong>HOD</strong> column accepts a staff number (<code>M0361</code>), a user id (<code>150</code>) or an exact staff name, and must match a real staff &mdash; a HOD that cannot be resolved is rejected, never saved blindly. Leave it out and the HOD is taken from the department as before.
                                <br>To fill in HOD for everyone: download <em>Current Staff (CSV)</em>, fill the <strong>HOD Staff No</strong> column, upload it back. Use <em>Current Staff (Excel)</em> instead if you want guided dropdowns, or <em>Blank CSV</em> to add new staff only.
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
                                                <th>Designation</th>
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

        <div class="modal fade" id="staff-import-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Import Preview &mdash; <span id="staff-import-filename"></span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Detected columns</strong>
                                <div id="staff-import-mapping" style="font-size:12px;margin-top:5px;"></div>
                            </div>
                            <div class="col-sm-6">
                                <strong>Summary</strong>
                                <div id="staff-import-counts" style="font-size:13px;margin-top:5px;"></div>
                            </div>
                        </div>
                        <hr style="margin:12px 0;">
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                            <table class="table table-bordered table-condensed" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th width="60">Line</th>
                                        <th width="90">Staff No.</th>
                                        <th width="200">Staff Name</th>
                                        <th width="80">Action</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody id="staff-import-rows"></tbody>
                            </table>
                        </div>
                        <div class="text-muted" style="font-size:11px;">
                            Rows marked <span class="label label-danger">error</span> are skipped; everything else is written when you confirm.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="btn-confirm-staff-import" onclick="confirmStaffImport()">
                            <i class="fa fa-check"></i> Confirm Import
                        </button>
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

        /* ================= Staff import - CSV or Excel, preview then confirm ================= */
        var staffImportToken = null;

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : value).html();
        }

        function previewStaffImport() {
            var fileInput = document.getElementById('staff-import-input');
            if (!fileInput.files || fileInput.files.length === 0) {
                swal('Error', 'Please choose a CSV file to upload first.', 'error');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'preview');
            formData.append('import_file', fileInput.files[0]);

            var $btn = $('#btn-preview-staff-import');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Reading...');

            $.ajax({
                url: 'import_staff.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.message !== 'done') {
                        swal('Error', res.detail || 'Could not read the file.', 'error');
                        return;
                    }
                    staffImportToken = res.token;
                    renderStaffImportPreview(res);
                    $('#staff-import-modal').modal('show');
                },
                error: function () {
                    swal('Error', 'Upload failed. Please try again.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Upload &amp; Preview');
                }
            });
        }

        function renderStaffImportPreview(res) {
            $('#staff-import-filename').text(res.filename);

            var mappingHtml = '';
            $.each(res.mapping, function (i, map) {
                mappingHtml += '<div><code>' + escapeHtml(map.header) + '</code> &rarr; <strong>' + escapeHtml(map.label) + '</strong></div>';
            });
            if (res.ignored && res.ignored.length > 0) {
                mappingHtml += '<div class="text-muted" style="margin-top:5px;">Ignored: ' + escapeHtml(res.ignored.join(', ')) + '</div>';
            }
            $('#staff-import-mapping').html(mappingHtml);

            $('#staff-import-counts').html(
                '<span class="label label-success">' + res.counts.insert + ' to insert</span> ' +
                '<span class="label label-primary">' + res.counts.update + ' to update</span> ' +
                '<span class="label label-default">' + res.counts.unchanged + ' unchanged</span> ' +
                '<span class="label label-danger">' + res.counts.error + ' error</span>'
            );

            var labelFor = { insert: 'label-success', update: 'label-primary', unchanged: 'label-default', error: 'label-danger' };
            var rowsHtml = '';
            $.each(res.rows, function (i, row) {
                rowsHtml += '<tr>' +
                    '<td>' + row.line + '</td>' +
                    '<td>' + escapeHtml(row.staffno) + '</td>' +
                    '<td>' + escapeHtml(row.staffname) + '</td>' +
                    '<td><span class="label ' + labelFor[row.action] + '">' + row.action + '</span></td>' +
                    '<td>' + escapeHtml(row.detail) +
                        (row.matched ? '<div class="text-muted" style="font-size:11px;">' + escapeHtml(row.matched) + '</div>' : '') +
                    '</td>' +
                    '</tr>';
            });
            if (rowsHtml === '') {
                rowsHtml = '<tr><td colspan="5" class="text-center text-muted">No data rows found in the file.</td></tr>';
            }
            $('#staff-import-rows').html(rowsHtml);

            var nothingToDo = (res.counts.insert + res.counts.update) === 0;
            $('#btn-confirm-staff-import').prop('disabled', nothingToDo);
        }

        function confirmStaffImport() {
            if (!staffImportToken) return;

            var $btn = $('#btn-confirm-staff-import');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');

            $.ajax({
                url: 'import_staff.php',
                type: 'POST',
                data: { action: 'commit', token: staffImportToken },
                dataType: 'json',
                success: function (res) {
                    if (res.message !== 'done') {
                        swal('Error', res.detail || 'Import failed.', 'error');
                        return;
                    }
                    staffImportToken = null;
                    document.getElementById('staff-import-input').value = '';
                    $('#staff-import-modal').modal('hide');

                    var summaryText = res.inserted + ' staff created, ' + res.updated + ' updated, ' +
                        res.unchanged + ' unchanged, ' + res.skipped + ' skipped.';
                    if (res.errors && res.errors.length > 0) {
                        summaryText += '\n\n' + res.errors.length + ' skipped row(s):\n' + res.errors.join('\n');
                    }
                    swal('Import Complete', summaryText, (res.errors && res.errors.length > 0) ? 'warning' : 'success');

                    $('#userlist').DataTable().destroy();
                    fetch_data('load_staff');
                },
                error: function () {
                    swal('Error', 'Import failed. Please try again.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Import');
                }
            });
        }

        // Dismissing the preview without confirming - Cancel, the X, or Esc -
        // releases the file the server parked for the confirm step.
        $('#staff-import-modal').on('hidden.bs.modal', function () {
            if (!staffImportToken) return;
            $.post('import_staff.php', { action: 'cancel' });
            staffImportToken = null;
            document.getElementById('staff-import-input').value = '';
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
                "order": [[ 10, "asc" ]],
                "ajax":{
                    url:"fetch_staff.php",
                    type:"POST",
                    dataSrc: '',
                    data : {action:action},
                },
                "drawCallback": function (settings) {
                    var api = this.api();
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1 + api.context[0]._iDisplayStart;
                    });
                },
                "columns": [
                    {
                        "data": "id",
                        "orderable": false
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
                        "data": "designation"
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
                    { className: 'text-center', targets: [0,8,9,10,11] }
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

        $(document).on('click', '.linkparty', function(){
            var staffid = $(this).attr("id");
            localStorage.setItem("setstaffid", staffid);
            window.location = "view_training.php";
        });

        $(document).on('click', '.resetpass', function(){
            var id = $(this).attr("id");
            var btn_action = 'resetpassword';
            swal({
                title: "Reset User's Password?",
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
                            if((response.message) == 'reset') {
                                swal(
                                    'Password Reset!',
                                    'The password has been reset.',
                                    'success'
                                )
                                $('#userlist').DataTable().destroy();
                                fetch_data('load_staff');
                            }
                            else if((response.message) == 'error') {
                                swal(
                                    'Password Not Reset!',
                                    'The password cannot be reset. Please refer the IT.',
                                    'error'
                                )
                                $('#userlist').DataTable().destroy();
                                fetch_data('load_staff');
                            }
                        }
                    });
                }else{
                    swal("Cancelled", "The password has not been reset", "error");
                }
            })
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
