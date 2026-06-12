<?php
    session_start();
    include "../../dbconn.php";
    include_once __DIR__ . '/../../division_department_section.php';

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {
        $dbOrgStructure = getDbOrgStructure();
        $divisionOptions = array_keys($dbOrgStructure);

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
            <div id="spinner-div">
                <img src="../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..." style="margin-top: 350px;"/>
            </div>
            <div class="row">
                <div class="col-md-12">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-1">
                                    <button type="button" name="back_staff" id="back_staff" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to Staff List</button>
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
                                    <strong id="title">Add New Staff</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">

                                </div>
                                <div class="col-md-6">
                                    <form method="post" id="staff_form">
                                        <fieldset style="border-radius:10px;">
                                            <legend id="leg1">Add Staff</legend>
                                            <div class="form-group">
                                                <label>Staff Number</label>
                                                <input type="text" name="staffno" id="staffno" class="form-control" placeholder="Insert Staff Number" autocomplete="off" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" name="staffname" id="staffname" class="form-control" placeholder="Insert Staff Full Name" autocomplete="off" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="text" name="email" id="email" class="form-control" placeholder="Insert Staff Email" autocomplete="off">
                                            </div>
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option selected disabled="disabled">-- Select Gender --</option>
                                                    <option value="MALE">MALE</option>
                                                    <option value="FEMALE">FEMALE</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Designation</label>
                                                <select class="form-control" id="designation" name="designation" required>
                                                    <option selected disabled="disabled">-- Select Designation --</option>
                                                    <option value="CONTRACT">CONTRACT</option>
                                                    <option value="EXECUTIVE">EXECUTIVE</option>
                                                    <option value="MANAGER (AM/HOS & ABOVE)">MANAGER (AM/HOS & ABOVE)</option>
                                                    <option value="NON EXECUTIVE">NON EXECUTIVE</option>
													<option value="TRAINEE">TRAINEE</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Division</label>
                                                <select class="form-control" id="division" name="division" required>
                                                    <option selected disabled="disabled">-- Select Division --</option>
                                                    <?php foreach ($divisionOptions as $div): ?>
                                                        <option value="<?= htmlspecialchars($div, ENT_QUOTES) ?>"><?= htmlspecialchars($div) ?></option>
                                                    <?php endforeach; ?>

                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Department</label>
                                                <select class="form-control" id="department" name="department" required>
                                                    <option selected disabled="disabled">-- Select Department --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Section</label>
                                                <select class="form-control" id="section" name="section" required>
                                                    <option selected disabled="disabled">-- Select Section --</option>
                                                </select>
                                            </div>
											<div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option selected disabled="disabled">-- Select Status --</option>
                                                    <option value="ACTIVE">ACTIVE</option>
                                                    <option value="RESIGN">NOT ACTIVE</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="id" id="id" />
								                <input type="hidden" name="btn_action" id="btn_action" />
                                                <input type="submit" name="action" id="action" class="btn btn-info" style="width:100px;" value="Save" />
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
    <script>        var ORG_STRUCTURE = <?php echo json_encode($dbOrgStructure, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
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

        $("#division").change(function () {
            var division = $(this).val();
            var departmentSelect = $("#department");
            var sectionSelect = $("#section");

            departmentSelect.html('<option selected disabled="disabled">-- Select Department --</option>');
            sectionSelect.html('<option selected disabled="disabled">-- Select Section --</option>');

            if (division && ORG_STRUCTURE[division]) {
                Object.keys(ORG_STRUCTURE[division]).forEach(function (dept) {
                    departmentSelect.append('<option value="' + dept + '">' + dept + '</option>');
                });
            }
        });

        $("#department").change(function () {
            var division = $("#division").val();
            var department = $(this).val();
            var sectionSelect = $("#section");

            sectionSelect.html('<option selected disabled="disabled">-- Select Section --</option>');

            if (division && department && ORG_STRUCTURE[division] && ORG_STRUCTURE[division][department]) {
                ORG_STRUCTURE[division][department].forEach(function (section) {
                    sectionSelect.append('<option value="' + section + '">' + section + '</option>');
                });
            }
        });

        $(document).ready(function(){
            var action1 = localStorage.getItem("setaction");

            if (action1 == 'adduser') {
                $('#btn_action').val(action1);
            }else if (action1 == 'edituser') {
                var legend1 = document.getElementById("leg1");
                legend1 .innerHTML = "Edit Staff";
                var title1 = document.getElementById("title");
                title1 .innerHTML = "Edit Staff Info";
                var id = localStorage.getItem("setid");
                var action = 'fetch_user';
                $.ajax({
                    url:"fetch_staff.php",
                    method:"POST",
                    data:{id:id,action:action},
                    dataType:"json",
                    success:function(data) {
                        $('#staffno').val(data.staffno);
                        $('#staffname').val(data.staffname);
                        $('#email').val(data.email);
                        $('#gender').val(data.gender);
                        $('#designation').val(data.designation);
                        $('#division').val(data.division);
                        $('#department').html('<option selected disabled="disabled">-- Select Department --</option>');
                        if (data.division && ORG_STRUCTURE[data.division]) {
                            Object.keys(ORG_STRUCTURE[data.division]).forEach(function (dept) {
                                $('#department').append('<option value="' + dept + '">' + dept + '</option>');
                            });
                        }
                        $('#department').val(data.department);
                        // Populate section from ORG_STRUCTURE mapping
                        $('#section').html('<option selected disabled="disabled">-- Select Section --</option>');
                        if (data.division && data.department && ORG_STRUCTURE[data.division] && ORG_STRUCTURE[data.division][data.department]) {
                            ORG_STRUCTURE[data.division][data.department].forEach(function (section) {
                                $('#section').append('<option value="' + section + '">' + section + '</option>');
                            });
                        }
                        $('#section').html('<option selected disabled="disabled">-- Select Section --</option>');
                        if (data.division && data.department && ORG_STRUCTURE[data.division] && ORG_STRUCTURE[data.division][data.department]) {
                            ORG_STRUCTURE[data.division][data.department].forEach(function (section) {
                                $('#section').append('<option value="' + section + '">' + section + '</option>');
                            });
                        }
                        $('#section').val(data.section);
                        $('#status').val(data.status);
                        $('#btn_action').val(action1);
                        $('#id').val(id);
                    }
                })
            }
        });

        $('#back_staff').click(function(){
            window.location = "staff.php";
        });

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("staff_form").addEventListener("submit", function(event) {
                event.preventDefault(); // ⛔ Prevent default submission

                let requiredFields = document.querySelectorAll("#staff_form [required]");
                let firstError = null;
                let isValid = true;

                requiredFields.forEach(field => {
                    let errorMessage = field.nextElementSibling; 

                    if (!errorMessage || !errorMessage.classList.contains("error-message")) {
                        errorMessage = document.createElement("span");
                        errorMessage.classList.add("error-message");
                        errorMessage.style.color = "red";
                        errorMessage.style.fontSize = "12px";
                        errorMessage.style.display = "block";
                        errorMessage.style.marginTop = "5px";
                        field.parentNode.appendChild(errorMessage);
                    }

                    // Validation logic
                    if (field.tagName === "SELECT" && field.value.startsWith("--")) {
                        isValid = false;
                        errorMessage.textContent = "Please select an option.";
                        field.style.border = "2px solid red";
                        if (!firstError) firstError = field;
                    } else if (!field.value.trim()) {
                        isValid = false;
                        errorMessage.textContent = "This field is required.";
                        field.style.border = "2px solid red";
                        if (!firstError) firstError = field;
                    } else {
                        errorMessage.textContent = "";
                        field.style.border = "";
                    }
                });

                if (!isValid) {
                    firstError.scrollIntoView({ behavior: "smooth", block: "center" }); 
                    return; // ⛔ Stop function if form is invalid
                }

                // ✅ If form is valid, proceed to AJAX submission
                submitForm();
            });

            document.querySelectorAll("#staff_form select").forEach(select => {
                select.addEventListener("change", function () {
                    if (this.value !== "" && !this.value.startsWith("--")) {
                        this.style.border = ""; 
                        let errorMessage = this.nextElementSibling;
                        if (errorMessage && errorMessage.classList.contains("error-message")) {
                            errorMessage.textContent = ""; 
                        }
                    }
                });
            });


            function submitForm() {
                $("#spinner-div").show();
                var form_data = $("#staff_form").serialize();
                $.ajax({
                    url: "staff_action.php",
                    method: "POST",
                    data: form_data,
                    success: function(data) {
                        var response = JSON.parse(data);
                        console.log("response : ", response);
                        if (response.message === "insert") {
                            swal("Added!", "The staff has been added.", "success").then(function() {
                                $("#staff_form")[0].reset();
                            });
                        } else if (response.message === "update") {
                            swal("Edited!", "The staff has been edited.", "success").then(function() {
                                window.location = "staff.php";
                            });
                        } else if (response.message === "error") {
                            swal("Failed!", "The operation cannot be done. Please refer to IT.", "error").then(function() {
                                $("#staff_form")[0].reset();
                            });
                        }
                    },
                    complete: function () {
                        $("#spinner-div").hide();
                    }
                });
            }
        });


    </script>
</html>
<?php
    }else{
         header("Location: ../../login.php");
         exit();
    }
?>
