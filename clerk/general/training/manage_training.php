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
        <link rel="stylesheet" href="../../../asset/css/datepicker.css">
        <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
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
                        <li><a href="../staff/staff.php">CONTRACT STAFF LIST</a></li>
						<li><a href="training_ojt.php">ALL TRAINING</a></li>
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
                                <div class="col-md-11">
                                    <strong id="title">Add New Training</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">

                                </div>
                                <div class="col-md-6">
                                    <form method="post" id="training_form">
                                        <fieldset style="border-radius:10px;">
                                            <legend>Training Information</legend>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Title <span style="color:red;">*</span></label>
                                                    <input type="text" name="title1" id="title1" class="form-control" placeholder="Insert Title" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Venue <span style="color:red;">*</span></label>
                                                    <input type="text" name="venue" id="venue" class="form-control" placeholder="Insert Venue" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Start Date <span style="color:red;">*</span></label>
                                                    <input type="text" name="startdate" id="startdate" class="form-control" placeholder="Insert Start Date" autocomplete="off" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label>End Date <span style="color:red;">*</span></label>
                                                    <input type="text" name="enddate" id="enddate" class="form-control" placeholder="Insert End Date" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>Start Time (24-Hour Format)<span style="color:red;">*</span></label>
                                                    <input class="form-control" name="starttime" id="starttime" placeholder="Select Start Time" type="text"/>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>End Time (24-Hour Format)<span style="color:red;">*</span></label>
                                                    <input class="form-control" name="endtime" id="endtime" placeholder="Select End Time" type="text"/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label>External/Internal Trainer <span style="color:red;">*</span></label>
                                                    <select name="trainertype" id="trainertype" class="form-control">
                                                        <option selected disabled="disabled">-- Select Type --</option>
                                                        <option value="EXTERNAL">EXTERNAL</option>
                                                        <option value="INTERNAL">INTERNAL</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6" id="external">
                                                    <label>Trainer Name <span style="color:red;">*</span></label>
                                                    <input type="text" name="externalname" id="externalname" class="form-control" placeholder="Insert Trainer Name" autocomplete="off" />
                                                </div>
                                                <div class="col-md-6" id="internal">
                                                    <label>Trainer Name <span style="color:red;">*</span></label>
                                                    <select name="internalname" id="internalname" class="form-control"></select>
                                                </div>
                                            </div>
                                            <br>
                                            <legend>List of Permanent Staff <button class="btn btn-info" type="submit" id="append" name="append" style="margin-left:10px;"><i class="fa fa-plus"></i></button></legend>
                                            <div class="inc">
                                                
                                            </div>
                                            <br>
                                            <legend>List of Contract Staff <button class="btn btn-info" type="submit" id="appendcon" name="appendcon" style="margin-left:10px;"><i class="fa fa-plus"></i></button></legend>
                                            <div class="conc">
                                                
                                            </div>
                                            <div class="form-group" align="right">
                                                <input type="hidden" name="id" id="id" />
                                                <input type="hidden" name="partycount" id="partycount" />
                                                <input type="hidden" name="partyconc" id="partyconc" />
                                                <input type="hidden" name="clerkid" id="clerkid" />
								                <input type="hidden" name="btn_action" id="btn_action" />
                                                <button type="submit" name="action" id="action" class="btn btn-info btn-xm" style="width: 100px;"><i class="fa fa-save"></i> Save</button>
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

        var userid = <?php echo $_SESSION['id']?>;

        function attachStaffSearch(selectId) {
            var $select = $('#' + selectId);
            var name = $select.attr('name');
            var currentVal = $select.val() || '';
            var currentText = $select.find('option:selected').text() || '';

            var allOptions = [];
            $select.find('option').each(function() {
                if ($(this).val()) allOptions.push({ value: $(this).val(), text: $(this).text() });
            });

            var $wrapper = $('<div style="position:relative;"></div>');
            var $input = $('<input type="text" class="form-control" placeholder="Type to search staff..." autocomplete="off">');
            var $hidden = $('<input type="hidden" name="' + name + '">');
            var $list = $('<ul style="display:none;position:absolute;z-index:9999;width:100%;max-height:300px;overflow-y:auto;background:#fff;border:1px solid #ccd0d4;border-top:none;list-style:none;padding:0;margin:0;border-radius:0 0 4px 4px;box-shadow:0 4px 6px rgba(0,0,0,.1);"></ul>');

            if (currentVal) {
                $input.val(currentText);
                $hidden.val(currentVal);
            }

            $wrapper.append($input).append($hidden).append($list);
            $select.replaceWith($wrapper);

            function renderList(q) {
                $list.empty();
                var filtered = allOptions.filter(function(o) {
                    return !q || o.text.toLowerCase().indexOf(q.toLowerCase()) !== -1;
                });
                if (!filtered.length) { $list.hide(); return; }
                filtered.forEach(function(o) {
                    var $item = $('<li style="padding:6px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;">' + o.text + '</li>');
                    $item.on('mouseenter', function() { $(this).css('background-color','#e8f4f8'); });
                    $item.on('mouseleave', function() { $(this).css('background-color',''); });
                    $item.on('mousedown', function(e) {
                        e.preventDefault();
                        $input.val(o.text);
                        $hidden.val(o.value);
                        $list.hide();
                    });
                    $list.append($item);
                });
                $list.show();
            }

            $input.on('focus', function() { renderList($(this).val()); });
            $input.on('input', function() { $hidden.val(''); renderList($(this).val()); });
            $input.on('blur', function() { setTimeout(function() { $list.hide(); }, 200); });
        }

        $('#external').hide();
        $('#internal').hide();

        $('#trainertype').on('change', function() {
            var type = this.value;
            if (type == 'EXTERNAL') {
                $('#external').show();
                $('#internal').hide();
            }else if (type == 'INTERNAL') {
                $('#external').hide();
                $('#internal').show();
            }
        });

        $('#startdate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        $('#enddate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

        $('#starttime').timepicker({
            minuteStep: 1,
            showSeconds: false,
            showMeridian: false
        });

        $('#endtime').timepicker({
            minuteStep: 1,
            showSeconds: false,
            showMeridian: false
        });

        var edittrainer = '';
        var counter = '';
        var counterconc = '';

        var action1 = localStorage.getItem("setaction");
        var clerkid = localStorage.getItem("setclerkid");
        var starttrdate = localStorage.getItem("setstart");
        var endtrdate = localStorage.getItem("setend");

        if (action1 == 'addtraining') {
            counter = 0;
            counterconc = 0;
            edittrainer = 'start';
            $('#btn_action').val(action1);
            $('#clerkid').val(clerkid);

            $("#append").click( function(e) {
                counter++;
                $('#partycount').val(counter);
                e.preventDefault();
                $(".inc").append('<div class="form-group row">\
                    <div class="col-md-10">\
                        <select name="participant'+counter+'" id="participant'+counter+'" class="form-control"></select>\
                    </div>\
                    <a href="#" class="remove_this btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                </div>');
                $(function(){
                    $.post("fetch_training.php",{action:"load_party"},function(data){
                        $('#participant'+counter).html(data);
                        attachStaffSearch('participant'+counter);
                    });
                });
                return false;
            });

            $(document).on('click', '.remove_this', function() {
                counter--;
                $('#partycount').val(counter);
                $(this).parent().remove();
                return false;
            });

            $("#appendcon").click( function(e) {
                counterconc++;
                $('#partyconc').val(counterconc);
                e.preventDefault();
                $(".conc").append('<div class="form-group row">\
                    <div class="col-md-10">\
                        <select name="contractstaff'+counterconc+'" id="contractstaff'+counterconc+'" class="form-control"></select>\
                    </div>\
                    <a href="#" class="remove_con btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                </div>');
                $(function(){
                    $.post("fetch_training.php",{action:"load_contract"},function(data){
                        $('#contractstaff'+counterconc).html(data);
                        attachStaffSearch('contractstaff'+counterconc);
                    });
                });
                return false;
            });

            $(document).on('click', '.remove_con', function() {
                counterconc--;
                $('#partyconc').val(counterconc);
                $(this).parent().remove();
                return false;
            });

            $(function(){
                $.post("fetch_training.php",{action:"load_staff"},function(data){
                    $('#internalname').html(data);
                    attachStaffSearch('internalname');
                });
            });
        }else if (action1 == 'edittraining') {
           $('#training_form')[0].reset();
            var title1 = document.getElementById("title");
            title1 .innerHTML = "Edit Training Info";
            var id = localStorage.getItem("setid");
            var action = 'fetch_training';
            var department1 = '';
            $.ajax({
                url:"fetch_training.php",
                method:"POST",
                data:{id:id,action:action},
                dataType:"json",
                success:function(data) {
                    $('#title1').val(data[0].title);
                    $('#startdate').val(data[0].startdate);
                    $('#enddate').val(data[0].enddate);
                    $('#starttime').val(data[0].starttime);
                    $('#endtime').val(data[0].endtime);
                    $('#venue').val(data[0].venue);
                    $('#trainertype').val(data[0].trainertype);
                    $('#totalparty').val(data[0].totalman);
                    $('#partyconc').val(data[0].totalcontract);
                    $('#partycount').val(data[0].totaluser);
                    $('#btn_action').val(action1);
                    $('#clerkid').val(clerkid);
                    $('#id').val(id);

                    if (data[0].trainertype == 'EXTERNAL') {
                        $('#external').show();
                        $('#internal').hide();
                        $('#externalname').val(data[0].trainername);
                    }else if (data[0].trainertype == 'INTERNAL') {
                        $('#external').hide();
                        $('#internal').show();
                        edittrainer = data[0].trainername;
                    }

                    var peopletotal = parseInt(data[0].totaluser) + parseInt(data[0].totalcontract);

                    if (data[0].totaluser != 0) {
                        var i;
                        var userstaffname = [];
                        
                        for (i=0;i<peopletotal;i++) {
                            if (data[i].designation != 'CONTRACT') {
                                $(".inc").append('<div class="form-group row">\
                                    <div class="col-md-10">\
                                        <select name="participant'+i+'" id="participant'+i+'" class="form-control"></select>\
                                    </div>\
                                    <a href="#" class="remove_this btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                                </div>');

                                userstaffname.push(data[i].staffname);
                                counter++;
                                $('#partycount').val(counter);
                            }
                        }

                       var t;

                        $(function(){
                            $.post("fetch_training.php",{action:"load_party"},function(data){
                                for (t=0;t<=i;t++) {
                                    $('#participant'+t).html(data);
                                    $('#participant'+t).val(userstaffname[t]);
                                    attachStaffSearch('participant'+t);
                                }
                            });
                        });
                    }

                    var totalcont = data[0].totalcontract;

                    if (data[0].totalcontract != 0) {
                        var i;
                        var j = 0;
                        var contractstaffname = [];
                        
                        for (i=0;i<peopletotal;i++) {
                            if (data[i].designation == 'CONTRACT') {
                                $(".conc").append('<div class="form-group row">\
                                    <div class="col-md-10">\
                                        <select name="contractstaff'+j+'" id="contractstaff'+j+'" class="form-control"></select>\
                                    </div>\
                                    <a href="#" class="remove_con btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                                </div>');

                                contractstaffname.push(data[i].staffname);
                                counterconc++;
                                $('#partyconc').val(counterconc);
                                j++;
                            }
                        }

                       var t;

                        $(function(){
                            $.post("fetch_training.php",{action:"load_contract"},function(data){
                                for (t=0;t<totalcont;t++) {
                                    $('#contractstaff'+t).html(data);
                                    $('#contractstaff'+t).val(contractstaffname[t]);
                                    attachStaffSearch('contractstaff'+t);
                                }
                            });
                        });
                    }

                    var counter1 = counter - 1;

                    $("#append").click( function(e) {
                        counter1++;
                        counter++;
                        $('#partycount').val(counter);
                        e.preventDefault();
                        $(".inc").append('<div class="form-group row">\
                            <div class="col-md-10">\
                                <select name="participant'+counter1+'" id="participant'+counter1+'" class="form-control"></select>\
                            </div>\
                            <a href="#" class="remove_this btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                        </div>');
                        $(function(){
                            $.post("fetch_training.php",{action:"load_party"},function(data){
                                $('#participant'+counter1).html(data);
                                attachStaffSearch('participant'+counter1);
                            });
                        });
                        
                        return false;
                    });

                    $(document).on('click', '.remove_this', function() {
                        // counter--;
                        // $('#partycount').val(counter);
                        $(this).parent().remove();
                        return false;
                    });

                    var counterconc1 = counterconc - 1;

                    $("#appendcon").click( function(e) {
                        counterconc1++;
                        counterconc++;
                        $('#partyconc').val(counterconc);
                        e.preventDefault();
                        $(".conc").append('<div class="form-group row">\
                            <div class="col-md-10">\
                                <select name="contractstaff'+counterconc1+'" id="contractstaff'+counterconc1+'" class="form-control"></select>\
                            </div>\
                            <a href="#" class="remove_con btn btn-danger" style="margin-left:49px;"><i class="fa fa-trash"></i> Delete</a>\
                        </div>');
                        $(function(){
                            $.post("fetch_training.php",{action:"load_contract"},function(data){
                                $('#contractstaff'+counterconc1).html(data);
                                attachStaffSearch('contractstaff'+counterconc1);
                            });
                        });

                        return false;
                    });

                    $(document).on('click', '.remove_con', function() {
                        // counterconc--;
                        // $('#partyconc').val(counterconc);
                        $(this).parent().remove();
                        return false;
                    });
                }
            });
            
            $(function(){
                $.post("fetch_training.php",{action:"load_staff"},function(data){
                    $('#internalname').html(data);
                    $('#internalname').val(edittrainer);
                    attachStaffSearch('internalname');
                });
            });
        }

        $('#back_training').click(function(){
            window.location = "training_ojt.php";
            localStorage.setItem("setstarttr", starttrdate);
            localStorage.setItem("setendtr", endtrdate);
        });

        $(document).on('submit', '#training_form', function(event){
            event.preventDefault();
            $("#spinner-div").show();
            var form_data = $(this).serialize();
            $.ajax({
                url:"training_action.php",
                method:"POST",
                data:form_data,
                success:function(data)
                {
                    var response = JSON.parse(data)
                    if((response.message) == 'insert') {
                        swal(
                            'Added!',
                            'The OJT training has been added.',
                            'success'
                        ).then(function() {
                            $('#training_form')[0].reset();
        				})
                    }
                    else if((response.message) == 'update') {
                        swal(
                            'Edited!',
                            'The OJT training has been edited.',
                            'success'
                        ).then(function() {
                            window.location = "training_ojt.php";
        				})
                    }else if((response.message) == 'error') {
                        swal(
                            'Failed!',
                            'The operation cannot be done. Please refer to IT',
                            'error'
                        ).then(function() {
                            $('#training_form')[0].reset();
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
         header("Location: ../login.php");
         exit();
    }
?>