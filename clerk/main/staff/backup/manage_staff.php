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
            <div id="spinner-div">
                <img src="../../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..." style="margin-top: 350px;"/>
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
                                                <input type="text" name="staffno" id="staffno" class="form-control" placeholder="Insert Staff Number" autocomplete="off" />
                                            </div>
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" name="staffname" id="staffname" class="form-control" placeholder="Insert Staff Full Name" autocomplete="off" />
                                            </div>
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="form-control" id="gender" name="gender">
                                                    <option selected disabled="disabled">-- Select Gender --</option>
                                                    <option value="MALE">MALE</option>
                                                    <option value="FEMALE">FEMALE</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Division</label>
                                                <select class="form-control" id="division" name="division">
                                                    <option selected disabled="disabled">-- Select Division --</option>
                                                    <option value="BUSINESS DEVELOPMENT">BUSINESS DEVELOPMENT</option>
                                                    <option value="DHMSB/SUBANG">DHMSB/SUBANG</option>
                                                    <option value="FINANCE">FINANCE</option>
                                                    <option value="HUMAN CAPITAL">HUMAN CAPITAL</option>
                                                    <option value="OPERATION">OPERATION</option>
                                                    <option value="OPERATION TRANSFORMATION">OPERATION TRANSFORMATION</option>
                                                    <option value="QUALITY MANAGEMENT">QUALITY MANAGEMENT</option>
                                                    <option value="R&D AND ENGINEERING">R&D AND ENGINEERING</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Department</label>
                                                <select class="form-control" id="department" name="department">
                                                    <option selected disabled="disabled">-- Select Department --</option>
                                                </select>
                                            </div>
											<div class="form-group">
                                                <label>Section</label>
                                                <select class="form-control" id="section" name="section">
                                                    <option selected disabled="disabled">-- Select Section --</option>
                                                </select>
                                            </div>
											<div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option selected disabled="disabled">-- Select Status --</option>
                                                    <option value="">ACTIVE</option>
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

        $("#division").change(function () {
            var val = $(this).val();
            if (val == "BUSINESS DEVELOPMENT") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="BUSINESS DEVELOPMENT">BUSINESS DEVELOPMENT</option><option value="PROGRAM MANAGEMENT 1">PROGRAM MANAGEMENT 1</option><option value="PROGRAM MANAGEMENT 2">PROGRAM MANAGEMENT 2</option><option value="QUALITY DEVELOPMENT">QUALITY DEVELOPMENT</option>');
            }else if (val == "DHMSB/SUBANG") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="OPERATION & PROGRAM MANAGEMENT (DHMSB)">OPERATION & PROGRAM MANAGEMENT (DHMSB)</option><option value="MANUFACTURING & SCM (DHMSB)">MANUFACTURING & SCM (DHMSB)</option>');
            }else if (val == "FINANCE") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="INFORMATION TECHNOLOGY">INFORMATION TECHNOLOGY</option><option value="INFORMATION TECHNOLOGY (DHMSB)">INFORMATION TECHNOLOGY (DHMSB)</option><option value="MANAGEMENT ACCOUNTING">MANAGEMENT ACCOUNTING</option><option value="MANAGEMENT ACCOUNTING (DHMSB)">MANAGEMENT ACCOUNTING (DHMSB)</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)">PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)</option>');
            }else if (val == "HUMAN CAPITAL") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="HUMAN CAPITAL & ADMIN">HUMAN CAPITAL & ADMIN</option><option value="CULTURE & TALENT MANAGEMENT">CULTURE & TALENT MANAGEMENT</option><option value="HUMAN CAPITAL & ADMIN (DHMSB)">HUMAN CAPITAL & ADMIN (DHMSB)</option><option value="CULTURE & TALENT MANAGEMENT (DHMSB)">CULTURE & TALENT MANAGEMENT (DHMSB)</option>');
            }else if (val == "OPERATION") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="BUKIT BERUNTUNG">BUKIT BERUNTUNG</option><option value="PEGOH">PEGOH</option><option value="ASSEMBLY PEKAN">ASSEMBLY PEKAN</option><option value="SHAH ALAM 1">SHAH ALAM 1</option><option value="SHAH ALAM 2">SHAH ALAM 2</option><option value="TANJUNG MALIM 1 (FIF)">TANJUNG MALIM 1 (FIF)</option><option value="TANJUNG MALIM 2 (OSI)">TANJUNG MALIM 2 (OSI)</option><option value="SUPPLY CHAIN MANAGEMENT">SUPPLY CHAIN MANAGEMENT</option><option value="SUPPLY CHAIN MANAGEMENT SA 1 & SA 2">SUPPLY CHAIN MANAGEMENT SA 1 & SA 2</option><option value="OPERATION BUKIT BERUNTUNG - TG MALIM">OPERATION BUKIT BERUNTUNG - TG MALIM</option>');
            }else if (val == "OPERATION TRANSFORMATION") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="COST ENGINEERING">COST ENGINEERING</option><option value="PROGRAM MANAGEMENT PROTON">PROGRAM MANAGEMENT PROTON</option><option value="ESG">ESG</option><option value="HMS">HMS</option><option value="SHE">SHE</option>');
            }else if (val == "QUALITY MANAGEMENT") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="QUALITY MANAGEMENT SYSTEM">QUALITY MANAGEMENT SYSTEM</option><option value="QUALITY MANAGEMENT (BB, TM 1 & TM 2)">QUALITY MANAGEMENT (BB, TM 1 & TM 2)</option><option value="QUALITY MANAGEMENT (MLK & PKN)">QUALITY MANAGEMENT (MLK & PKN)</option><option value="QUALITY MANAGEMENT (SA 1 & SA 2)">QUALITY MANAGEMENT (SA 1 & SA 2)</option>');
            }else if (val == "R&D AND ENGINEERING") {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="ENGINEERING MANAGEMENT 1">ENGINEERING MANAGEMENT 1</option><option value="ENGINEERING MANAGEMENT 2">ENGINEERING MANAGEMENT 2</option><option value="FACILITY MANAGEMENT">FACILITY MANAGEMENT</option><option value="PLANT ENGINEERING 1 (SA 1 & BB)">PLANT ENGINEERING 1 (SA 1 & BB)</option><option value="PLANT ENGINEERING 2 (SA 2, TM1 & TM2)">PLANT ENGINEERING 2 (SA 2, TM1 & TM2)</option><option value="PROCESS & INDUSTRIAL ENGINEERING">PROCESS & INDUSTRIAL ENGINEERING</option><option value="RESEARCH & DEVELOPMENT">RESEARCH & DEVELOPMENT</option><option value="TOOLING ENGINEERING">TOOLING ENGINEERING</option><option value="TOOLING MAINTENANCE">TOOLING MAINTENANCE</option>');
            }else {
                $("#department").html('<option selected disabled="disabled">-- Select Department --</option>');
            }
        });
		
		$("#department").change(function () {
            var val = $(this).val();
            if (val == "ASSEMBLY PEKAN") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="PROJECT MMCMM">PROJECT MMCMM</option>');
            }else if (val == "BUSINESS DEVELOPMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="COST PLANNING & COMMERCIAL">COST PLANNING & COMMERCIAL</option><option value="MARKETING & SALES">MARKETING & SALES</option>');
            }else if (val == "COST ENGINEERING") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "CULTURE & TALENT MANAGEMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CULTURE SECTION">CULTURE SECTION</option><option value="LEARNING & DEVELOPMENT">LEARNING & DEVELOPMENT</option><option value="RECRUITMENT">RECRUITMENT</option>');
            }else if (val == "CULTURE & TALENT MANAGEMENT (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CULTURE SECTION">CULTURE SECTION</option><option value="LEARNING & DEVELOPMENT">LEARNING & DEVELOPMENT</option><option value="RECRUITMENT">RECRUITMENT</option>');
            }else if (val == "ENGINEERING MANAGEMENT 1") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "ENGINEERING MANAGEMENT 2") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "ESG") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "FACILITY MANAGEMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="FACILITY & PLANT MAINTENANCE">FACILITY & PLANT MAINTENANCE</option>');
            }else if (val == "HMS") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="HMS">HMS</option>');
            }else if (val == "HUMAN CAPITAL & ADMIN") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ADMINISTRATION">ADMINISTRATION</option><option value="COMPENSATION & BENEFIT">COMPENSATION & BENEFIT</option>');
            }else if (val == "HUMAN CAPITAL & ADMIN (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ADMINISTRATION">ADMINISTRATION</option><option value="COMPENSATION & BENEFIT">COMPENSATION & BENEFIT</option>');
            }else if (val == "INFORMATION TECHNOLOGY") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="APPLICATION">APPLICATION</option><option value="PROJECT & DEVELOPMENT">PROJECT & DEVELOPMENT</option><option value="SYSTEM ADMINISTRATION">SYSTEM ADMINISTRATION</option>');
            }else if (val == "INFORMATION TECHNOLOGY (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="APPLICATION">APPLICATION</option><option value="PROJECT & DEVELOPMENT">PROJECT & DEVELOPMENT</option><option value="SYSTEM ADMINISTRATION">SYSTEM ADMINISTRATION</option>');
            }else if (val == "MANAGEMENT ACCOUNTING") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ACCOUNT PAYABLE">ACCOUNT PAYABLE</option><option value="ACCOUNT RECEIVABLE">ACCOUNT RECEIVABLE</option><option value="INVENTORY FIXED ASSET MANAGEMENT">INVENTORY FIXED ASSET MANAGEMENT</option><option value="REPORTING TAXATION">REPORTING TAXATION</option><option value="TREASURY & SALES">TREASURY & SALES</option>');
            }else if (val == "MANAGEMENT ACCOUNTING (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ACCOUNT PAYABLE">ACCOUNT PAYABLE</option><option value="ACCOUNT RECEIVABLE">ACCOUNT RECEIVABLE</option><option value="INVENTORY FIXED ASSET MANAGEMENT">INVENTORY FIXED ASSET MANAGEMENT</option><option value="REPORTING TAXATION">REPORTING TAXATION</option><option value="TREASURY & SALES">TREASURY & SALES</option>');
            }else if (val == "MANUFACTURING & SCM (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="MFG - USJ">MFG - USJ</option>');
            }else if (val == "BUKIT BERUNTUNG") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INVENTORY MGMT. AND PLANNING (MFG-BB)">INVENTORY MGMT. AND PLANNING (MFG-BB)</option><option value="MFG - BUKIT BERUNTUNG">MFG - BUKIT BERUNTUNG</option><option value="PROGRESS CONTROL (MFG-BB)">PROGRESS CONTROL (MFG-BB)</option><option value="RECEIVING (MFG-BB)">RECEIVING (MFG-BB)</option>');
            }else if (val == "PEGOH") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - PEGOH">ASSEMBLY - PEGOH</option><option value="PROGRESS CONTROL (MFG-MELAKA)">PROGRESS CONTROL (MFG-MELAKA)</option>');
            }else if (val == "OPERATION & PROGRAM MANAGEMENT (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="FABRICATION">FABRICATION</option>');
            }else if (val == "OPERATION BUKIT BERUNTUNG - TG MALIM") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "SHAH ALAM 1") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY">ASSEMBLY</option><option value="STAMPING">STAMPING</option>');
            }else if (val == "SHAH ALAM 2") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - SA 2">ASSEMBLY - SA 2</option><option value="PAINTING">PAINTING</option><option value="STAMPING- SA 2">STAMPING- SA 2</option><option value="WELDING">WELDING</option>');
            }else if (val == "PLANT ENGINEERING 1 (SA 1 & BB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="EQUIPMENT MNTC ASSEMBLY- FIF">EQUIPMENT MNTC ASSEMBLY- FIF</option><option value="EQUIPMENT MNTC ASSEMBLY- PHN 1">EQUIPMENT MNTC ASSEMBLY- PHN 1</option><option value="EQUIPMENT MNTC ASSEMBLY- TGM">EQUIPMENT MNTC ASSEMBLY- TGM</option><option value="EQUIPMENT MNTC STAMPING- SA1">EQUIPMENT MNTC STAMPING- SA1</option>');
            }else if (val == "PLANT ENGINEERING 2 (SA 2, TM1 & TM2)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="EQUIPMENT MNTC ASSEMBLY- PHN 2">EQUIPMENT MNTC ASSEMBLY- PHN 2</option>');
            }else if (val == "PROCESS & INDUSTRIAL ENGINEERING") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INDUSTRIAL ENGINEERING">INDUSTRIAL ENGINEERING</option><option value="PROCESS DEVELOPMENT">PROCESS DEVELOPMENT</option><option value="PROCESS IMPROVEMENT">PROCESS IMPROVEMENT</option>');
            }else if (val == "PROCUREMENT & VENDOR DEVELOPMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="GENERAL PURCHASES">GENERAL PURCHASES</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="RAW MATERIALS & COMPONENTS">RAW MATERIALS & COMPONENTS</option><option value="VENDOR MANAGEMENT & DEVELOPMENT">VENDOR MANAGEMENT & DEVELOPMENT</option>');
            }else if (val == "PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="GENERAL PURCHASES">GENERAL PURCHASES</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="RAW MATERIALS & COMPONENTS">RAW MATERIALS & COMPONENTS</option><option value="VENDOR MANAGEMENT & DEVELOPMENT">VENDOR MANAGEMENT & DEVELOPMENT</option>');
            }else if (val == "PROGRAM MANAGEMENT 1") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="AEROSPACE">AEROSPACE</option><option value="PRO. MGMT. OTHERS">PRO. MGMT. OTHERS</option><option value="PRO. MGMT. PERODUA">PRO. MGMT. PERODUA</option>');
            }else if (val == "PROGRAM MANAGEMENT 2") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="PRO. MGMT. HONDA">PRO. MGMT. HONDA</option><option value="PRO. MGMT. NON AUTO">PRO. MGMT. NON AUTO</option>');
            }else if (val == "PROGRAM MANAGEMENT PROTON") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="PROGRAM MANAGEMENT PROTON">PROGRAM MANAGEMENT PROTON</option><option value="QUALITY DEVELOPMENT PROTON">QUALITY DEVELOPMENT PROTON</option>');
            }else if (val == "QUALITY DEVELOPMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY RESIDENT ENGINEERING OP1">QUALITY RESIDENT ENGINEERING OP1</option><option value="QUALITY RESIDENT ENGINEERING OP2">QUALITY RESIDENT ENGINEERING OP2</option>');
            }else if (val == "QUALITY MANAGEMENT (BB, TM 1 & TM 2)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY MANAGEMENT- TGM 1 (FIF)">QUALITY MANAGEMENT- TGM 1 (FIF)</option><option value="QUALITY MANAGEMENT- TGM 2">QUALITY MANAGEMENT- TGM 2</option><option value="QUALITY PCC">QUALITY PCC</option><option value="QUALITY PERODUA (MFG-BB)">QUALITY PERODUA (MFG-BB)</option>');
            }else if (val == "QUALITY MANAGEMENT (MLK & PKN)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY MANAGEMENT (PEGOH)">QUALITY MANAGEMENT (PEGOH)</option><option value="QUALITY MANAGEMENT (PEKAN)">QUALITY MANAGEMENT (PEKAN)</option>');
            }else if (val == "QUALITY MANAGEMENT (SA 1 & SA 2)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CUSTOMER SERVICE - SA 1">CUSTOMER SERVICE - SA 1</option><option value="CUSTOMER SERVICES">CUSTOMER SERVICES</option><option value="QUALITY ASSURANCE - SA 1">QUALITY ASSURANCE - SA 1</option><option value="QUALITY ASSURANCE - SA 2">QUALITY ASSURANCE - SA 2</option><option value="QUALITY CONTROL - SA 1">QUALITY CONTROL - SA 1</option><option value="QUALITY CONTROL - SA 2">QUALITY CONTROL - SA 2</option><option value="QUALITY SUPPLIER - SA 1">QUALITY SUPPLIER - SA 1</option>');
            }else if (val == "QUALITY MANAGEMENT SYSTEM") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "RESEARCH & DEVELOPMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="COMPUTER AIDED ENGINEERING">COMPUTER AIDED ENGINEERING</option><option value="DESIGN">DESIGN</option>');
            }else if (val == "SHE") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "SUPPLY CHAIN MANAGEMENT") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
            }else if (val == "SUPPLY CHAIN MANAGEMENT SA 1 & SA 2") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INVENTORY MGMT PLANNING (SA2)">INVENTORY MGMT PLANNING (SA2)</option><option value="INVENTORY MGMT. AND PLANNING (SA1)">INVENTORY MGMT. AND PLANNING (SA1)</option><option value="LOGISTIC">LOGISTIC</option><option value="PROGRESS CONTROL">PROGRESS CONTROL</option><option value="PROGRESS CONTROL- SA 2">PROGRESS CONTROL- SA 2</option><option value="RECEIVING">RECEIVING</option><option value="RECEIVING- SA 2">RECEIVING- SA 2</option>');
            }else if (val == "TANJUNG MALIM 2 (OSI)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - TGM 2">ASSEMBLY - TGM 2</option><option value="SUPPLY CHAIN MANAGEMENT - TGM 2">SUPPLY CHAIN MANAGEMENT - TGM 2</option>');
            }else if (val == "TANJUNG MALIM 1 (FIF)") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - TGM 1 (FIF)">ASSEMBLY - TGM 1 (FIF)</option><option value="SUPPLY CHAIN MGMT- TGM 1 (FIF)">SUPPLY CHAIN MGMT- TGM 1 (FIF)</option>');
            }else if (val == "TOOLING ENGINEERING") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="BIW PROJECT MANAGEMENT">BIW PROJECT MANAGEMENT</option><option value="JIG MAKING">JIG MAKING</option><option value="MFG. PROCESS PLANNING">MFG. PROCESS PLANNING</option><option value="TE - DIES MAKING">TE - DIES MAKING</option>');
            }else if (val == "TOOLING MAINTENANCE") {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="DIES MAINTENANCE">DIES MAINTENANCE</option>');
            }else {
                $("#section").html('<option selected disabled="disabled">-- Select Section --</option>');
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
                        $('#gender').val(data.gender);
                        $('#division').val(data.division);
                        if (data.division == "BUSINESS DEVELOPMENT") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="BUSINESS DEVELOPMENT">BUSINESS DEVELOPMENT</option><option value="PROGRAM MANAGEMENT 1">PROGRAM MANAGEMENT 1</option><option value="PROGRAM MANAGEMENT 2">PROGRAM MANAGEMENT 2</option><option value="QUALITY DEVELOPMENT">QUALITY DEVELOPMENT</option>');
                        }else if (data.division == "DHMSB/SUBANG") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="OPERATION & PROGRAM MANAGEMENT (DHMSB)">OPERATION & PROGRAM MANAGEMENT (DHMSB)</option><option value="MANUFACTURING & SCM (DHMSB)">MANUFACTURING & SCM (DHMSB)</option>');
                        }else if (data.division == "FINANCE") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="INFORMATION TECHNOLOGY">INFORMATION TECHNOLOGY</option><option value="INFORMATION TECHNOLOGY (DHMSB)">INFORMATION TECHNOLOGY (DHMSB)</option><option value="MANAGEMENT ACCOUNTING">MANAGEMENT ACCOUNTING</option><option value="MANAGEMENT ACCOUNTING (DHMSB)">MANAGEMENT ACCOUNTING (DHMSB)</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)">PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)</option>');
                        }else if (data.division == "HUMAN CAPITAL") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="HUMAN CAPITAL & ADMIN">HUMAN CAPITAL & ADMIN</option><option value="CULTURE & TALENT MANAGEMENT">CULTURE & TALENT MANAGEMENT</option><option value="HUMAN CAPITAL & ADMIN (DHMSB)">HUMAN CAPITAL & ADMIN (DHMSB)</option><option value="CULTURE & TALENT MANAGEMENT (DHMSB)">CULTURE & TALENT MANAGEMENT (DHMSB)</option>');
                        }else if (data.division == "OPERATION") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="BUKIT BERUNTUNG">BUKIT BERUNTUNG</option><option value="PEGOH">PEGOH</option><option value="ASSEMBLY PEKAN">ASSEMBLY PEKAN</option><option value="SHAH ALAM 1">SHAH ALAM 1</option><option value="SHAH ALAM 2">SHAH ALAM 2</option><option value="TANJUNG MALIM 1 (FIF)">TANJUNG MALIM 1 (FIF)</option><option value="TANJUNG MALIM 2 (OSI)">TANJUNG MALIM 2 (OSI)</option><option value="SUPPLY CHAIN MANAGEMENT SA 1 & SA 2">SUPPLY CHAIN MANAGEMENT SA 1 & SA 2</option><option value="OPERATION BUKIT BERUNTUNG - TG MALIM">OPERATION BUKIT BERUNTUNG - TG MALIM</option>');
                        }else if (data.division == "OPERATION TRANSFORMATION") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="COST ENGINEERING">COST ENGINEERING</option><option value="PROGRAM MANAGEMENT PROTON">PROGRAM MANAGEMENT PROTON</option><option value="ESG">ESG</option><option value="HMS">HMS</option><option value="SHE">SHE</option>');
                        }else if (data.division == "QUALITY MANAGEMENT") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="QUALITY MANAGEMENT SYSTEM">QUALITY MANAGEMENT SYSTEM</option><option value="QUALITY MANAGEMENT (BB, TM 1 & TM 2)">QUALITY MANAGEMENT (BB, TM 1 & TM 2)</option><option value="QUALITY MANAGEMENT (MLK & PKN)">QUALITY MANAGEMENT (MLK & PKN)</option><option value="QUALITY MANAGEMENT (SA 1 & SA 2)">QUALITY MANAGEMENT (SA 1 & SA 2)</option>');
                        }else if (data.division == "R&D AND ENGINEERING") {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option><option value="ENGINEERING MANAGEMENT 1">ENGINEERING MANAGEMENT 1</option><option value="ENGINEERING MANAGEMENT 2">ENGINEERING MANAGEMENT 2</option><option value="FACILITY MANAGEMENT">FACILITY MANAGEMENT</option><option value="PLANT ENGINEERING 1 (SA 1 & BB)">PLANT ENGINEERING 1 (SA 1 & BB)</option><option value="PLANT ENGINEERING 2 (SA 2, TM1 & TM2)">PLANT ENGINEERING 2 (SA 2, TM1 & TM2)</option><option value="PROCESS & INDUSTRIAL ENGINEERING">PROCESS & INDUSTRIAL ENGINEERING</option><option value="RESEARCH & DEVELOPMENT">RESEARCH & DEVELOPMENT</option><option value="TOOLING ENGINEERING">TOOLING ENGINEERING</option><option value="TOOLING MAINTENANCE">TOOLING MAINTENANCE</option>');
                        }else {
                            $("#department").html('<option selected disabled="disabled">-- Select Department --</option>');
                        }
                        $('#department').val(data.department);
                        if (data.department == "ASSEMBLY PEKAN") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="PROJECT MMCMM">PROJECT MMCMM</option>');
                        }else if (data.department == "BUSINESS DEVELOPMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="COST PLANNING & COMMERCIAL">COST PLANNING & COMMERCIAL</option><option value="MARKETING & SALES">MARKETING & SALES</option>');
                        }else if (data.department == "COST ENGINEERING") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "CULTURE & TALENT MANAGEMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CULTURE SECTION">CULTURE SECTION</option><option value="LEARNING & DEVELOPMENT">LEARNING & DEVELOPMENT</option><option value="RECRUITMENT">RECRUITMENT</option>');
                        }else if (data.department == "CULTURE & TALENT MANAGEMENT (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CULTURE SECTION">CULTURE SECTION</option><option value="LEARNING & DEVELOPMENT">LEARNING & DEVELOPMENT</option><option value="RECRUITMENT">RECRUITMENT</option>');
                        }else if (data.department == "ENGINEERING MANAGEMENT 1") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "ENGINEERING MANAGEMENT 2") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "ESG") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "FACILITY MANAGEMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="FACILITY & PLANT MAINTENANCE">FACILITY & PLANT MAINTENANCE</option>');
                        }else if (data.department == "HMS") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="HMS">HMS</option>');
                        }else if (data.department == "HUMAN CAPITAL & ADMIN") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ADMINISTRATION">ADMINISTRATION</option><option value="COMPENSATION & BENEFIT">COMPENSATION & BENEFIT</option>');
                        }else if (data.department == "HUMAN CAPITAL & ADMIN (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ADMINISTRATION">ADMINISTRATION</option><option value="COMPENSATION & BENEFIT">COMPENSATION & BENEFIT</option>');
                        }else if (data.department == "INFORMATION TECHNOLOGY") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="APPLICATION">APPLICATION</option><option value="PROJECT & DEVELOPMENT">PROJECT & DEVELOPMENT</option><option value="SYSTEM ADMINISTRATION">SYSTEM ADMINISTRATION</option>');
                        }else if (data.department == "INFORMATION TECHNOLOGY (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="APPLICATION">APPLICATION</option><option value="PROJECT & DEVELOPMENT">PROJECT & DEVELOPMENT</option><option value="SYSTEM ADMINISTRATION">SYSTEM ADMINISTRATION</option>');
                        }else if (data.department == "MANAGEMENT ACCOUNTING") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ACCOUNT PAYABLE">ACCOUNT PAYABLE</option><option value="ACCOUNT RECEIVABLE">ACCOUNT RECEIVABLE</option><option value="INVENTORY FIXED ASSET MANAGEMENT">INVENTORY FIXED ASSET MANAGEMENT</option><option value="REPORTING TAXATION">REPORTING TAXATION</option><option value="TREASURY & SALES">TREASURY & SALES</option>');
                        }else if (data.department == "MANAGEMENT ACCOUNTING (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ACCOUNT PAYABLE">ACCOUNT PAYABLE</option><option value="ACCOUNT RECEIVABLE">ACCOUNT RECEIVABLE</option><option value="INVENTORY FIXED ASSET MANAGEMENT">INVENTORY FIXED ASSET MANAGEMENT</option><option value="REPORTING TAXATION">REPORTING TAXATION</option><option value="TREASURY & SALES">TREASURY & SALES</option>');
                        }else if (data.department == "MANUFACTURING & SCM (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="MFG - USJ">MFG - USJ</option>');
                        }else if (data.department == "BUKIT BERUNTUNG") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INVENTORY MGMT. AND PLANNING (MFG-BB)">INVENTORY MGMT. AND PLANNING (MFG-BB)</option><option value="MFG - BUKIT BERUNTUNG">MFG - BUKIT BERUNTUNG</option><option value="PROGRESS CONTROL (MFG-BB)">PROGRESS CONTROL (MFG-BB)</option><option value="RECEIVING (MFG-BB)">RECEIVING (MFG-BB)</option>');
                        }else if (data.department == "PEGOH") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - PEGOH">ASSEMBLY - PEGOH</option><option value="PROGRESS CONTROL (MFG-MELAKA)">PROGRESS CONTROL (MFG-MELAKA)</option>');
                        }else if (data.department == "OPERATION & PROGRAM MANAGEMENT (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="FABRICATION">FABRICATION</option>');
                        }else if (data.department == "OPERATION BUKIT BERUNTUNG - TG MALIM") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "SHAH ALAM 1") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY">ASSEMBLY</option><option value="STAMPING">STAMPING</option>');
                        }else if (data.department == "SHAH ALAM 2") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - SA 2">ASSEMBLY - SA 2</option><option value="PAINTING">PAINTING</option><option value="STAMPING- SA 2">STAMPING- SA 2</option><option value="WELDING">WELDING</option>');
                        }else if (data.department == "PLANT ENGINEERING 1 (SA 1 & BB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="EQUIPMENT MNTC ASSEMBLY- FIF">EQUIPMENT MNTC ASSEMBLY- FIF</option><option value="EQUIPMENT MNTC ASSEMBLY- PHN 1">EQUIPMENT MNTC ASSEMBLY- PHN 1</option><option value="EQUIPMENT MNTC ASSEMBLY- TGM">EQUIPMENT MNTC ASSEMBLY- TGM</option><option value="EQUIPMENT MNTC STAMPING- SA1">EQUIPMENT MNTC STAMPING- SA1</option>');
                        }else if (data.department == "PLANT ENGINEERING 2 (SA 2, TM1 & TM2)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="EQUIPMENT MNTC ASSEMBLY- PHN 2">EQUIPMENT MNTC ASSEMBLY- PHN 2</option>');
                        }else if (data.department == "PROCESS & INDUSTRIAL ENGINEERING") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INDUSTRIAL ENGINEERING">INDUSTRIAL ENGINEERING</option><option value="PROCESS DEVELOPMENT">PROCESS DEVELOPMENT</option><option value="PROCESS IMPROVEMENT">PROCESS IMPROVEMENT</option>');
                        }else if (data.department == "PROCUREMENT & VENDOR DEVELOPMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="GENERAL PURCHASES">GENERAL PURCHASES</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="RAW MATERIALS & COMPONENTS">RAW MATERIALS & COMPONENTS</option><option value="VENDOR MANAGEMENT & DEVELOPMENT">VENDOR MANAGEMENT & DEVELOPMENT</option>');
                        }else if (data.department == "PROCUREMENT & VENDOR DEVELOPMENT (DHMSB)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="GENERAL PURCHASES">GENERAL PURCHASES</option><option value="PROCUREMENT & VENDOR DEVELOPMENT">PROCUREMENT & VENDOR DEVELOPMENT</option><option value="RAW MATERIALS & COMPONENTS">RAW MATERIALS & COMPONENTS</option><option value="VENDOR MANAGEMENT & DEVELOPMENT">VENDOR MANAGEMENT & DEVELOPMENT</option>');
                        }else if (data.department == "PROGRAM MANAGEMENT 1") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="AEROSPACE">AEROSPACE</option><option value="PRO. MGMT. OTHERS">PRO. MGMT. OTHERS</option><option value="PRO. MGMT. PERODUA">PRO. MGMT. PERODUA</option>');
                        }else if (data.department == "PROGRAM MANAGEMENT 2") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="PRO. MGMT. HONDA">PRO. MGMT. HONDA</option><option value="PRO. MGMT. NON AUTO">PRO. MGMT. NON AUTO</option>');
                        }else if (data.department == "PROGRAM MANAGEMENT PROTON") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="PROGRAM MANAGEMENT PROTON">PROGRAM MANAGEMENT PROTON</option><option value="QUALITY DEVELOPMENT PROTON">QUALITY DEVELOPMENT PROTON</option>');
                        }else if (data.department == "QUALITY DEVELOPMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY RESIDENT ENGINEERING OP1">QUALITY RESIDENT ENGINEERING OP1</option><option value="QUALITY RESIDENT ENGINEERING OP2">QUALITY RESIDENT ENGINEERING OP2</option>');
                        }else if (data.department == "QUALITY MANAGEMENT (BB, TM 1 & TM 2)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY MANAGEMENT- TGM 1 (FIF)">QUALITY MANAGEMENT- TGM 1 (FIF)</option><option value="QUALITY MANAGEMENT- TGM 2">QUALITY MANAGEMENT- TGM 2</option><option value="QUALITY PCC">QUALITY PCC</option><option value="QUALITY PERODUA (MFG-BB)">QUALITY PERODUA (MFG-BB)</option>');
                        }else if (data.department == "QUALITY MANAGEMENT (MLK & PKN)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="QUALITY MANAGEMENT (PEGOH)">QUALITY MANAGEMENT (PEGOH)</option><option value="QUALITY MANAGEMENT (PEKAN)">QUALITY MANAGEMENT (PEKAN)</option>');
                        }else if (data.department == "QUALITY MANAGEMENT (SA 1 & SA 2)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="CUSTOMER SERVICE - SA 1">CUSTOMER SERVICE - SA 1</option><option value="CUSTOMER SERVICES">CUSTOMER SERVICES</option><option value="QUALITY ASSURANCE - SA 1">QUALITY ASSURANCE - SA 1</option><option value="QUALITY ASSURANCE - SA 2">QUALITY ASSURANCE - SA 2</option><option value="QUALITY CONTROL - SA 1">QUALITY CONTROL - SA 1</option><option value="QUALITY CONTROL - SA 2">QUALITY CONTROL - SA 2</option><option value="QUALITY SUPPLIER - SA 1">QUALITY SUPPLIER - SA 1</option>');
                        }else if (data.department == "QUALITY MANAGEMENT SYSTEM") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "RESEARCH & DEVELOPMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="COMPUTER AIDED ENGINEERING">COMPUTER AIDED ENGINEERING</option><option value="DESIGN">DESIGN</option>');
                        }else if (data.department == "SHE") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "SUPPLY CHAIN MANAGEMENT") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option>');
                        }else if (data.department == "SUPPLY CHAIN MANAGEMENT SA 1 & SA 2") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="INVENTORY MGMT PLANNING (SA2)">INVENTORY MGMT PLANNING (SA2)</option><option value="INVENTORY MGMT. AND PLANNING (SA1)">INVENTORY MGMT. AND PLANNING (SA1)</option><option value="LOGISTIC">LOGISTIC</option><option value="PROGRESS CONTROL">PROGRESS CONTROL</option><option value="PROGRESS CONTROL- SA 2">PROGRESS CONTROL- SA 2</option><option value="RECEIVING">RECEIVING</option><option value="RECEIVING- SA 2">RECEIVING- SA 2</option>');
                        }else if (data.department == "TANJUNG MALIM 2 (OSI)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - TGM 2">ASSEMBLY - TGM 2</option><option value="SUPPLY CHAIN MANAGEMENT - TGM 2">SUPPLY CHAIN MANAGEMENT - TGM 2</option>');
                        }else if (data.department == "TANJUNG MALIM 1 (FIF)") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="ASSEMBLY - TGM 1 (FIF)">ASSEMBLY - TGM 1 (FIF)</option><option value="SUPPLY CHAIN MGMT- TGM 1 (FIF)">SUPPLY CHAIN MGMT- TGM 1 (FIF)</option>');
                        }else if (data.department == "TOOLING ENGINEERING") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="BIW PROJECT MANAGEMENT">BIW PROJECT MANAGEMENT</option><option value="JIG MAKING">JIG MAKING</option><option value="MFG. PROCESS PLANNING">MFG. PROCESS PLANNING</option><option value="TE - DIES MAKING">TE - DIES MAKING</option>');
                        }else if (data.department == "TOOLING MAINTENANCE") {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option><option value="-">-</option><option value="DIES MAINTENANCE">DIES MAINTENANCE</option>');
                        }else {
                            $("#section").html('<option selected disabled="disabled">-- Select Section --</option>');
                        }
                        $('#btn_action').val(action1);
						$('#section').val(data.section);
						$('#status').val(data.status);
                        $('#id').val(id);
                    }
                })
            }
        });

        $('#back_staff').click(function(){
            window.location = "staff.php";
        });

        $(document).on('submit', '#staff_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"staff_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The staff has been added.',
                            'success'
                        ).then(function() {
                            $('#staff_form')[0].reset();
        				})
                    }
                    else if((response.message) == 'update') {
                        swal(
                            'Edited!',
                            'The staff has been edited.',
                            'success'
                        ).then(function() {
                            window.location = "staff.php";
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#staff_form')[0].reset();
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
