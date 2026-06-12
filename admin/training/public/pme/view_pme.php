<?php
    session_start();
    
    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {

    
       // Check if trainingid is in the URL
        if (!isset($_GET['trainingid'])) {
            die("Error: Training ID is missing.");
        }

        // Retrieve trainingid
        $trainingid = $_GET['trainingid'];

        // Store in session for back navigation later
        $_SESSION['trainingid'] = $trainingid;

        // Database connection
        include "../../../../dbconn.php";

        $sql = "SELECT title FROM training WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $trainingid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            die("Error: Training ID not found in database.");
        }

        $title = $row['title'];
        
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Learning and Development Management System</title>
        
        <script src="../../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../../asset/css/bootstrap.min.css" />
        <script src="../../../../asset/js/bootstrap.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js"></script>
    </head>

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
                                <div class="col-md-12" style="margin-top: 10px;">
                                    <strong>Performance Monitoring Evaluation List (<?= htmlspecialchars($title) ?>)</strong>
                                </div>
                            </div>
                        </div>
                        
                        <?php
                            // $sql = "SELECT pme.*, training.title, user.staffname, user.staffno, user.department 
                            //         FROM pme
                            //         INNER JOIN training ON pme.trainingid = training.id
                            //         INNER JOIN user ON pme.userid = user.id
                            //         WHERE pme.trainingid = ? 
                            //         AND pme.designation IN ('Executive', 'MANAGER (AM/HOS & ABOVE)')
                            //         ORDER BY 
                            //         CASE 
                            //             WHEN pme.status = 'completed' THEN 1
                            //             WHEN pme.status = 'approved' THEN 2
                            //             WHEN pme.status = 'pending' THEN 3
                            //             WHEN pme.status = 'verified' THEN 4
                            //             ELSE 5
                            //         END";

                            //ENHANCE - AMIR ANWAR
                            $sql = "SELECT 
                                    pme.*, 
                                    training.title, 
                                    user.staffname, 
                                    user.staffno, 
                                    user.department,
                                    hod.staffname AS hod_staffname,
                                    hod.staffno AS hod_staffno,
                                    hod.department AS hod_department
                                FROM pme
                                INNER JOIN training ON pme.trainingid = training.id
                                INNER JOIN user ON pme.userid = user.id
                                LEFT JOIN user AS hod ON pme.hodid = hod.id
                                INNER JOIN participation ON pme.participationid = participation.id
                                WHERE pme.trainingid = ? 
                                AND pme.designation IN ('Executive', 'MANAGER (AM/HOS & ABOVE)')
                                AND participation.attendance = 'completed'
                                ORDER BY 
                                    CASE 
                                        WHEN pme.status = 'completed' THEN 1
                                        WHEN pme.status = 'approved' THEN 2
                                        WHEN pme.status = 'pending' THEN 3
                                        WHEN pme.status = 'verified' THEN 4
                                        ELSE 5
                                    END;
                                    ";

                            $stmt = $conn->prepare($sql);
                            if (!$stmt) {
                                die("SQL Prepare Error: " . $conn->error);
                            }
                            $stmt->bind_param("i", $trainingid);
                            if (!$stmt->execute()) {
                                die("SQL Execution Error: " . $stmt->error);
                            }
                            $result = $stmt->get_result();
                            if ($result->num_rows === 0) {
                                die("No records found for trainingid: " . $trainingid);
                            }

                            // Count query (also include both designations)
                            $count_sql = "SELECT COUNT(*) AS total_records 
                                        FROM pme 
                                        WHERE trainingid = ? 
                                        AND designation IN ('Executive', 'MANAGER (AM/HOS & ABOVE)')";
                            $count_stmt = $conn->prepare($count_sql);
                            $count_stmt->bind_param("i", $trainingid);
                            $count_stmt->execute();
                            $count_result = $count_stmt->get_result();
                            $count_row = $count_result->fetch_assoc();
                            $total_records = $count_row['total_records'];

                            // Debugging: Check the total number of records
                            if ($total_records == 0) {
                                die("No records found.");
                            }

                            // Set pagination variables
                            $limit = 10;
                            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                            $offset = ($page - 1) * $limit;
                            $no = $offset + 1;

                         ?>

                        <div class="panel-body" align="center">
                            <div class="row">
                                <div class="col-sm-2">

                                </div>
                                <div class="col-sm-8 table-responsive">
                                    <table id="pmelist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Staff Number</th>
                                                <th>Staff Name</th>
                                                <th>HOD</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $no = $offset + 1;
                                                while ($row = $result->fetch_assoc()) {
                                                    // Assign label class based on status
                                                    $status_label = '';
                                                    if ($row['status'] == 'verified') {
                                                        $status_label = '<label class="label label-success">COMPLETED</span>';
                                                    } elseif ($row['status'] == 'completed' || $row['status'] == 'approved' || $row['status'] == 'pending') {
                                                        $status_label = '<span class="label label-warning">PENDING</span>';
                                                    } else {
                                                        $status_label = '<span class="label label-default">' . htmlspecialchars($row['status']) . '</span>';
                                                    }
                                            ?>
                                                <tr>
                                                    <td ><?= $no; ?></td>
                                                    <td><?= $row['staffno']; ?></td>
                                                    <td><?= $row['staffname']; ?></td>
                                                    <td><?= $row['hod_staffname']; ?></td>
                                                    <td><?= $row['department']; ?></td>
                                                    <td><?= $status_label; ?></td>
                                                    <td>
                                                        <?php if ($row['status'] == 'completed' || $row['status'] == 'verified'): ?>
                                                            <!-- View PME Button -->
                                                            <a href="edit_pme.php?trainingid=<?= $row['trainingid']; ?>&userid=<?= $row['userid']; ?>&participationid=<?= $row['participationid']; ?>" 
                                                            class="btn btn-success btn-sm">
                                                                <i class="fa fa-eye"></i> View PME
                                                            </a>
                                                        <?php elseif ($row['status'] == 'pending'): ?>
                                                            <!-- Pending Status: Show Disabled Button with Tooltip -->
                                                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Waiting for HOD evaluation">
                                                                <i class="fa fa-exclamation-circle"></i> Action Required
                                                            </button>
                                                        <?php elseif ($row['status'] == 'approved'): ?>
                                                            <!-- Approved Status: Participant Action Required -->
                                                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Waiting for participant to agree">
                                                                <i class="fa fa-exclamation-circle"></i> Action Required
                                                            </button>
                                                        <?php else: ?>
                                                            <!-- Default Status (if status is unknown) -->
                                                            <button class="btn btn-dark btn-sm" disabled>
                                                                <i class="fa fa-question-circle"></i> Unknown
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php
                                                    $no++;
                                                }
                                            ?>
                                        </tbody>

                                    </table>
                                </div>
                                <div class="col-sm-2">

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

        $('#back_training').click(function(){
            window.location = "../training.php";
        });

        $(document).ready(function () {
            var table = $('#pmelist').DataTable({
                "paging": true,         // Enables pagination
                "searching": true,      // Enables search bar
                "ordering": true,       // Enables column sorting
                "info": true,           // Shows table info (e.g., "Showing 1 to 10 of 50 entries")
                "lengthMenu": [100],  // Allows user to select number of records per page
                "columnDefs": [
                    { "className": "text-center", "targets": [0, 4, 5] } // Center align specific columns
                ]
            });

            // Initialize tooltips
            function initTooltips() {
                $('[data-toggle="tooltip"]').tooltip(); // Reinitialize Bootstrap tooltips
            }

            // Call tooltips on initial load
            initTooltips();

            // Reinitialize tooltips on every DataTable draw event (pagination, sorting, search)
            table.on('draw.dt', function () {
                initTooltips();
            });
        });

    </script>
</html>
<?php
    }else{
         header("Location: ../../../login.php");
         exit();
    }
?>