<?php
    session_start();

    if (isset($_SESSION['fullname']) && ($_SESSION['usertype'] == 'HOD')) {

        include_once "../../../dbconn.php";
        include_once __DIR__ . "/../includes/skill_matrix_access.php";
        $showSkillMatrixNav = canApproveSkillMatrix();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Learning and Development Management System</title>
        <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
        <script src="../../../asset/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
        <link rel="stylesheet" href="../../../asset/css/datepicker.css">
        <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
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
                        <li><a href="../dashboard.php">HOME</a></li>
                        <li><a href="../attendance/training.php">MY TRAINING</a></li>
                        <li><a href="../pme/pme.php">PME</a></li>
                        <li><a href="../tna/staff_list.php">TNA</a></li>
                        <li><a href="tni.php">TNI</a></li>
						<?php if ($showSkillMatrixNav) { ?>
                        <li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <?php } ?>
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
                                <div class="col-md-12" style="margin-top:10px" align="center">
                                    <strong id="title">Training Need Identification (TNI) Form - (FY <?php include_once __DIR__ . '/../../../planning_year.php'; echo ldmsPlanningYear(); ?>)</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" align="center">
                            <form method="post" id="tni_form">
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10" align="right">
                                        <h5 id="yeartraining"></h5>
                                        <h5 id="totalhours"></h5>
                                        <h5 id="ojthours"></h5>
                                        <h5 id="publichours"></h5>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10 table-responsive" align="left">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="2">Level Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1 - Fundamental Awareness</td>
                                                    <td>Basic knowledge</td>
                                                </tr>
                                                <tr>
                                                    <td>2 - Novice</td>
                                                    <td>Little experience or competence in the skill</td>
                                                </tr>
                                                <tr>
                                                    <td>3 - Intermediate</td>
                                                    <td>Has some competence but remains below level required</td>
                                                </tr>
                                                <tr>
                                                    <td>4 - Proficient</td>
                                                    <td>Competent and confident in the area</td>
                                                </tr>
                                                <tr>
                                                    <td>5 - Expert</td>
                                                    <td>An expert in that skill</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-10 table-responsive">
                                        <table id="tnilist" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="10">
                                                        <div class="row">
                                                            <div class="col-sm-6" align="left" style="margin-top:5px;">
                                                                a. Mandatory (Within 1st 3 month in the role)
                                                            </div>
                                                            <div class="col-sm-6" align="right">
                                                                <button type="button" name="add_mand" id="add_mand" class="btn btn-info btn-sm">Add Training <i class="fa fa plus"></i> </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th rowspan="2" width="10px">No.</th>
                                                    <th rowspan="2" width="420px">Performance Indicator (Critical to Quality/Process)</th>
                                                    <th colspan="2">Performance</th>
                                                    <th rowspan="2" width="80px">Gap</th>
                                                    <th rowspan="2">Possible Causes</th>
                                                    <th rowspan="2">Attitude / Skill / Knowledge</th>
                                                    <th rowspan="2">L&D Method</th>
                                                    <th rowspan="2">Evaluation Method</th>
                                                    <th rowspan="2">Action</th>
                                                </tr>
                                                <tr>
                                                    <th width="80px">Expected</th>
                                                    <th width="80px">Actual</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-10">
                                        <div class="form-group" align="right">
                                            <input type="hidden" name="userid" id="userid" />
                                            <input type="hidden" name="btn_action" id="btn_action" />
                                            <input type="hidden" name="mandatory" id="mandatory" />
                                            <button type="submit" name="action" id="action" class="btn btn-success btn-xm"><i class="fa fa-save"></i> Save</button>
                                        </div>
                                    </div>
                                    <div class="col-md-1"></div>
                                </div>
                            </form>
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

        var userid = <?php echo (int) $_SESSION['id']?>;
        var curyear = new Date().getFullYear();

        var levelOptions = '<option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option>';

        // Every row is built here and filled through .val(), so saved text
        // containing quotes or HTML cannot break the row. Rows carry no ids:
        // renumberRows() assigns the No. column and the task1/task2/... field
        // names from the row's position, so the numbers always match what is
        // on screen whichever row was added or deleted.
        function buildRow(data) {
            var $row = $('<tr class="tni-row">'
                + '<td class="tni-no" style="text-align:center;"></td>'
                + '<td><input type="text" class="form-control tni-task" maxlength="500" placeholder="Insert Training" /></td>'
                + '<td><select class="form-control tni-target">' + levelOptions + '</select></td>'
                + '<td><select class="form-control tni-current">' + levelOptions + '</select></td>'
                + '<td style="text-align:center;"><input type="text" class="form-control tni-gap" readonly /></td>'
                + '<td><input type="text" class="form-control tni-cause" maxlength="100" placeholder="Insert Cause" /></td>'
                + '<td><input type="text" class="form-control tni-ask" maxlength="100" placeholder="Insert A/S/K" /></td>'
                + '<td><select class="form-control tni-trtype"><option value="" selected disabled="disabled">-- Select L&D Method --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="3">3 - External / In-house</option></select></td>'
                + '<td><input type="text" class="form-control tni-evaluate" maxlength="100" placeholder="Insert Evaluation Method" /></td>'
                + '<td><button type="button" class="btn btn-danger tni-remove"><i class="fa fa-trash"></i></button></td>'
                + '</tr>');

            if (data) {
                $row.find('.tni-task').val(data.training);
                $row.find('.tni-target').val(data.expected);
                $row.find('.tni-current').val(data.actual);
                $row.find('.tni-cause').val(data.cause);
                $row.find('.tni-ask').val(data.ask);
                $row.find('.tni-trtype').val(data.method);
                $row.find('.tni-evaluate').val(data.evaluation);
            }

            updateGap($row);
            return $row;
        }

        function updateGap($row) {
            var target = parseInt($row.find('.tni-target').val()) || 0;
            var current = parseInt($row.find('.tni-current').val()) || 0;
            $row.find('.tni-gap').val(target - current);
        }

        function renumberRows() {
            var $rows = $('#tnilist tbody tr.tni-row');
            $rows.each(function (index) {
                var no = index + 1;
                var $row = $(this);
                $row.find('.tni-no').text(no);
                $row.find('.tni-task').attr('name', 'task' + no);
                $row.find('.tni-target').attr('name', 'targetsk' + no);
                $row.find('.tni-current').attr('name', 'currentsk' + no);
                $row.find('.tni-gap').attr('name', 'gap' + no);
                $row.find('.tni-cause').attr('name', 'cause' + no);
                $row.find('.tni-ask').attr('name', 'ask' + no);
                $row.find('.tni-trtype').attr('name', 'trtype' + no);
                $row.find('.tni-evaluate').attr('name', 'evaluate' + no);
            });
            $('#mandatory').val($rows.length);
        }

        function addRow(data) {
            $('#tnilist tbody').append(buildRow(data));
            renumberRows();
        }

        function rowIsComplete($row) {
            return $.trim($row.find('.tni-task').val()) != '' && $row.find('.tni-trtype').val();
        }

        $('#btn_action').val('addtni');
        $('#userid').val(userid);

        $.ajax({
            url:"fetch_tni.php",
            method:"POST",
            data:{userid:userid,action:'gettni'},
            dataType:"json",
            success:function(data) {
                $('#yeartraining').text('Training History '+curyear);
                $('#totalhours').text('Current Total Training Hours : '+data[0].totalhour);
                $('#ojthours').text('Training Hours (OJT) : '+data[0].ojthour);
                $('#publichours').text('Training Hours (Public / In-house) : '+data[0].publichour);

                if (parseInt(data[0].tni) > 0) {
                    $.ajax({
                        url:"fetch_tni.php",
                        method:"POST",
                        data:{userid:userid,action:'getlisttni'},
                        dataType:"json",
                        success:function(list) {
                            $.each(list || [], function (i, item) {
                                addRow(item);
                            });
                        }
                    });
                } else {
                    addRow();
                }
            }
        });

        $('#add_mand').click(function() {
            var $last = $('#tnilist tbody tr.tni-row:last');
            if ($last.length && !rowIsComplete($last)) {
                alert('Please complete previous TNI details!');
                return;
            }
            addRow();
        });

        // Delegated, so the trash button works on every row - saved or newly
        // added. The row is only removed from the form; Save makes it final.
        $('#tnilist').on('click', '.tni-remove', function() {
            $(this).closest('tr').remove();
            renumberRows();
        });

        $('#tnilist').on('change', '.tni-target, .tni-current', function() {
            updateGap($(this).closest('tr'));
        });

        $(document).on('submit', '#tni_form', function(event){
            event.preventDefault();

            var $rows = $('#tnilist tbody tr.tni-row');
            var incomplete = false;
            $rows.each(function () {
                if (!rowIsComplete($(this))) {
                    incomplete = true;
                }
            });
            if (incomplete) {
                alert('Please complete the training and L&D method for every row, or delete the empty row.');
                return;
            }
            if ($rows.length == 0 && !confirm('There is no training in the list. Saving will clear the TNI for this year. Continue?')) {
                return;
            }

            renumberRows();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"tni_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Saved!',
                            'The TNI has been recorded.',
                            'success'
                        ).then(function() {
                            window.location = "tni.php";
        				})
                    }else if((response.message) == 'toolong') {
                        swal(
                            'Failed!',
                            'One of the entries is too long. Nothing was changed - please shorten it and save again.',
                            'error'
                        )
                    }else if((response.message) == 'error') {
                        // The form is left as it is: nothing was saved or
                        // deleted, so the HOD can retry without retyping.
                        swal(
                            'Failed!',
                            'The operation cannot be done. Nothing was changed. Please refer to IT',
                            'error'
                        )
                    }
                },
                error: function () {
                    swal('Failed!', 'The operation cannot be done. Please refer to IT', 'error');
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