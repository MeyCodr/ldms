<?php
    session_start();

    $canViewSkillMatrix = !empty($_SESSION['is_sm_user']) || (
        isset($_SESSION['designation'], $_SESSION['hodid'], $_SESSION['role'], $_SESSION['usertype'])
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && (int) $_SESSION['hodid'] != 0
        && $_SESSION['role'] == 'CLERK'
        && $_SESSION['usertype'] == 'MAIN'
    );

    if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'CLERK') && ($_SESSION['usertype'] == 'MAIN')) {

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Learning and Development Management System</title>
    <script src="../../../asset/js/jquery-1.10.2.min.js"></script>
    <link rel="stylesheet" href="../../../asset/css/bootstrap.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.css" />
    <script src="../../../asset/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
    <link rel="stylesheet" href="../../../asset/css/datepicker.css">
    <script src="../../../asset/js/bootstrap-datepicker1.js"></script>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.css" />
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-flash-1.5.4/b-html5-1.5.4/b-print-1.5.4/datatables.min.js">
    </script>
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
                <img src="../../../asset/image/lndlogo.gif" height="50" width="290">
            </div>
            <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

            </div>
        </div>
        <nav class="navbar navbar-inverse">
            <div class="container-fluid ">
                <ul class="nav navbar-nav">
                    <li><a href="../dashboard.php">HOME</a></li>
                    <li><a href="../attendance/training.php">MY TRAINING</a></li>
                    <li><a href="staff_list.php">TNA</a></li>
                    <?php if ($canViewSkillMatrix) { ?><li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li><?php } ?>
                    <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname']?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="../../../logout.php">LOGOUT</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <div id="spinner-div">
            <img src="../../../asset/image/loading.gif" id="ajaxSpinnerImage" title="working..."
                style="margin-top: 350px;" />
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-2" align="left">
                                <button type="button" name="back_training" id="back_training"
                                    class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> Back to
                                    Staff List</button>
                            </div>
                            <div class="col-md-8" style="margin-top:10px" align="center">
                                <strong id="title">Training Need Analysis (TNA) Form - (FY 2026) - <span
                                        id="staffname"></span></strong>
                            </div>
                            <div class="col-md-2" style="margin-top:10px" align="right">
                                <span id="statustna"></span>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body" align="center">
                        <form method="post" id="tna_form">
                            <div class="row">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-10" align="right">
                                    <h5 id="yeartraining"></h5>
                                    <h5 id="ojthours"></h5>
                                    <h5 id="publichours"></h5>
                                </div>
                                <div class="col-sm-1"></div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-10" align="left">
                                    In this TNA employee needs to complete it in consultation with your IMMEDIATE SUPERIOR. Employee is encourage to reflect on what skills you will need to succeed in the current job scope and what skills you still lack and wish to achieve or improve.
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
                                    <!-- ESG -->
                                    <table id="tnaesglist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-10" align="left" style="margin-top:5px;">
                                                            a. ESG (Environment-Social-Governance)
                                                        </div>
                                                        <div class="col-sm-2" align="right">
                                                            <button type="button" name="add_esg" id="add_esg"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <!-- ESG -->
                                    <table id="tnaselflist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-10" align="left" style="margin-top:5px;">
                                                            a. Soft Skill (Based on individual development competencies
                                                            i.e, language)
                                                        </div>
                                                        <div class="col-sm-2" align="right">
                                                            <button type="button" name="add_self" id="add_self"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <table id="tnaleadlist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-11" align="left" style="margin-top:5px;">
                                                            b. Leadership Awareness (This area will inculcate the
                                                            nurturing aspect of the talent hence inspiring others
                                                            towards achieving excellence hence inspiring others towards
                                                            achieving excellence)
                                                        </div>
                                                        <div class="col-sm-1" align="right">
                                                            <button type="button" name="add_lead" id="add_lead"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <!-- DATA DRIVEN -->
                                    <table id="tnadrivenlist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-11" align="left" style="margin-top:5px;">
                                                            d. Data Driven
                                                        </div>
                                                        <div class="col-sm-1" align="right">
                                                            <button type="button" name="add_driven" id="add_driven"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <!-- DATA DRIVEN -->
                                    <table id="tnafunclist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-11" align="left" style="margin-top:5px;">
                                                            e. Functional Awareness (Based on job requirement in terms
                                                            of knowledge and skills required to perform the duties;
                                                            technical skills or knowledge) - Critical Priorities &
                                                            Future Growth
                                                        </div>
                                                        <div class="col-sm-1" align="right">
                                                            <button type="button" name="add_func" id="add_func"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <table id="tnabusilist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-11" align="left" style="margin-top:5px;">
                                                            d. Digital Transformation & Innovation (Based on company
                                                            digital objective and expansion requirement for future
                                                            growth)
                                                        </div>
                                                        <div class="col-sm-1" align="right">
                                                            <button type="button" name="add_busi" id="add_busi"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <table id="tnaspeclist" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">
                                                    <div class="row">
                                                        <div class="col-sm-11" align="left" style="margin-top:5px;">
                                                            e. Special Project (Short term project either functional or
                                                            cross - functional)
                                                        </div>
                                                        <div class="col-sm-1" align="right">
                                                            <button type="button" name="add_spec" id="add_spec"
                                                                class="btn btn-info btn-sm">Add Task <i
                                                                    class="fa fa plus"></i> </button>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" width="10px">No.</th>
                                                <th rowspan="2" width="420px">Problem Statement</th>
                                                <th rowspan="2" width="420px">Training Required</th>
                                                <th colspan="2">Skills</th>
                                                <th rowspan="2" width="80px">Gap</th>
                                                <th rowspan="2">How will this be achieved?</th>
                                                <th rowspan="2" width="100px">When</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th width="80px">Target</th>
                                                <th width="80px">Current</th>
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
                                        <input type="hidden" name="esgaware" id="esgaware" />
                                        <input type="hidden" name="selfaware" id="selfaware" />
                                        <input type="hidden" name="leadaware" id="leadaware" />
                                        <input type="hidden" name="dataaware" id="dataaware" />
                                        <input type="hidden" name="functional" id="functional" />
                                        <input type="hidden" name="busiaware" id="busiaware" />
                                        <input type="hidden" name="special" id="special" />
                                        <button type="submit" name="action" id="action" class="btn btn-success btn-xm"
                                            style="width: 150px;"><i class="fa fa-save"></i> Save & Print PDF</button>
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
                    </br>All Rights Reserved. | Web design by PHN IT Department
                </h6>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
</footer>
<script>
function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    // add a zero in front of numbers<10
    h = checkTime(h);
    m = checkTime(m);
    s = checkTime(s);
    document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
    t = setTimeout(function() {
        startTime()
    }, 500);
}

function checkTime(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

$('#back_training').click(function() {
    window.location = "tna_list.php";
});

var userid = localStorage.getItem("setid");
var action = 'gettna';
var tna = 0;
var countesg = 0;
var countself = 0;
var countlead = 0;
var countdriven = 0;
var countfunc = 0;
var countbusi = 0;
var countspec = 0;
var curyear = new Date().getFullYear();
console.log("user id: ", userid);
$.ajax({
    url: "fetch_tna.php",
    method: "POST",
    data: {
        userid: userid,
        action: action
    },
    dataType: "json",
    success: function(data) {
        console.log("data: ", data);
        tna = parseInt(data[0].tna);
        console.log("tna: ", tna);
        $('#yeartraining').text('Training History ' + curyear);
        $('#ojthours').text('Current Training Hours (OJT) : ' + data[0].ojthour);
        $('#publichours').text('Current Training Hours (Public / In-house) : ' + data[0].publichour);
        $('#staffname').text(data[0].staffname);

        if (tna == 0) {
            $('#btn_action').val('addtna');
            $('#userid').val(userid);
        } else if (tna > 0) {
            if (data[0].status == "") {
                $('#statustna').fadeIn().html(
                    '<span class="label label-pill label-warning">WAITING FOR APPROVAL</span>');
            } else if (data[0].status == "APPROVE") {
                $('#statustna').fadeIn().html(
                    '<span class="label label-pill label-success">TNA APPROVED</span>');
            }

            $('#btn_action').val('addtna');
            $('#userid').val(userid);
            var action1 = 'getlisttna';
            var targetskes = [];
            var currentskes = [];
            var trtypees = [];
            var datetres = [];
            var traininges = [];

            var targetskse = [];
            var currentskse = [];
            var trtypese = [];
            var datetrse = [];
            var trainingse = [];

            var targetskle = [];
            var currentskle = [];
            var trtypele = [];
            var datetrle = [];
            var trainingle = [];

            var targetskda = [];
            var currentskda = [];
            var trtypeda = [];
            var datetrda = [];
            var trainingda = [];

            var targetskfu = [];
            var currentskfu = [];
            var trtypefu = [];
            var datetrfu = [];
            var trainingfu = [];

            var targetskbu = [];
            var currentskbu = [];
            var trtypebu = [];
            var datetrbu = [];
            var trainingbu = [];

            var targetsksp = [];
            var currentsksp = [];
            var trtypesp = [];
            var datetrsp = [];
            var trainingsp = [];
            $.ajax({
                url: "fetch_tna.php",
                method: "POST",
                data: {
                    userid: userid,
                    action: action1
                },
                dataType: "json",
                success: function(data) {
                    var trHTMLes = '';
                    var trHTMLse = '';
                    var trHTMLle = '';
                    var trHTMLda = '';
                    var trHTMLfu = '';
                    var trHTMLbu = '';
                    var trHTMLsp = '';
                    var noides = 1;
                    var noidse = 1;
                    var noidle = 1;
                    var noidda = 1;
                    var noidfu = 1;
                    var noidbu = 1;
                    var noidsp = 1;
                    $.each(data, function(i, data) {
                        if (data.section == 'esgaware') {
                            trHTMLes += `
                                    <tr>
                                        <td style="text-align:center;">${noides}</td>
                                        <td>
                                            <textarea class="form-control" name="taskes${noides}" id="taskes${noides}" rows="2">${data.task}</textarea>
                                        </td>
                                        <td>
                                            <select name="traininges${noides}" id="traininges${noides}" class="form-control">
                                                <option selected disabled>-- Select Training --</option>
                                                <option value="CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE">CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE</option>
                                                <option value="EU BATTERY REGULATION AWARENESS">EU BATTERY REGULATION AWARENESS</option>
                                                <option value="ESG REPORTING STANDARDS (BURSA, SC)">ESG REPORTING STANDARDS (BURSA, SC)</option>
                                                <option value="LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE">LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE</option>
                                                <option value="WASTE REDUCTION & CIRCULAR ECONOMY">WASTE REDUCTION & CIRCULAR ECONOMY</option>
                                                <option value="ESG AWARENESS AND STRATEGIC IMPLEMENTATION">ESG AWARENESS AND STRATEGIC IMPLEMENTATION</option>
                                                <option value="ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE">ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE</option>
                                                <option value="GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING">GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING</option>
                                                <option value="GREEN BOOK">GREEN BOOK</option>
                                                <option value="OTHERS">OTHERS</option>
                                            </select>
                                            <br>
                                            <input type="text" name="otres${noides}" id="otres${noides}" class="form-control" value="${data.othertr}" />
                                        </td>
                                        <td>
                                            <select name="targetskes${noides}" id="targetskes${noides}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="currentskes${noides}" id="currentskes${noides}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="text" name="gapes${noides}" id="gapes${noides}" class="form-control" value="${data.gap}" readonly />
                                        </td>
                                        <td>
                                            <select name="trtypees${noides}" id="trtypees${noides}" class="form-control">
                                                <option selected disabled>-- Select Training Type --</option>
                                                <option value="1">1 - On Job Training</option>
                                                <option value="2">2 - Coaching</option>
                                                <option value="3">3 - External / In-house</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="datetres${noides}" id="datetres${noides}" class="form-control">
                                                <option value="Jan">Jan</option>
                                                <option value="Feb">Feb</option>
                                                <option value="Mar">Mar</option>
                                                <option value="Apr">Apr</option>
                                                <option value="May">May</option>
                                                <option value="Jun">Jun</option>
                                                <option value="Jul">Jul</option>
                                                <option value="Aug">Aug</option>
                                                <option value="Sep">Sep</option>
                                                <option value="Oct">Oct</option>
                                                <option value="Nov">Nov</option>
                                                <option value="Dec">Dec</option>
                                            </select>
                                        </td>
                                        <td>
                                            <a href="#" class="remove_esg btn btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    `;
                            targetskes.push(data.targetskill);
                            currentskes.push(data.currentskill);
                            trtypees.push(data.trainingtype);
                            datetres.push(data.monthapply);
                            traininges.push(data.training);
                            noides++;
                        } else if (data.section == 'selfaware') {
                            trHTMLse += `
                                    <tr>
                                        <td style="text-align:center;">${noidse}</td>
                                        <td>
                                            <textarea class="form-control" name="taskse${noidse}" id="taskse${noidse}" rows="2">${data.task}</textarea>
                                        </td>
                                        <td>
                                            <select name="trainingse${noidse}" id="trainingse${noidse}" class="form-control">
                                            <option selected disabled value="">-- Select Training --</option>
                                            <option value="TRAIN THE TRAINER (TTT)">TRAIN THE TRAINER (TTT)</option>
                                            <option value="EFFECTIVE BUSINESS COMMUNICATION">EFFECTIVE BUSINESS COMMUNICATION</option>
                                            <option value="ENGLISH FOR PROFESSIONAL PURPOSES">ENGLISH FOR PROFESSIONAL PURPOSES</option>
                                            <option value="INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING">INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING</option>
                                            <option value="PRESENTATION AND PUBLIC SPEAKING SKILLS">PRESENTATION AND PUBLIC SPEAKING SKILLS</option>
                                            <option value="EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS">EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS</option>
                                            <option value="CONFLICT RESOLUTION AND NEGOTIATION SKILLS">CONFLICT RESOLUTION AND NEGOTIATION SKILLS</option>
                                            <option value="TIME MANAGEMENT AND PRODUCTIVITY SKILLS">TIME MANAGEMENT AND PRODUCTIVITY SKILLS</option>
                                            <option value="CROSS-CULTURAL COMMUNICATION">CROSS-CULTURAL COMMUNICATION</option>
                                            <option value="CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE">CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE</option>
                                            <option value="CREATIVE THINKING AND PROBLEM-SOLVING">CREATIVE THINKING AND PROBLEM-SOLVING</option>
                                            <option value="MICROSOFT TEAMS AND COLLABORATION TOOLS">MICROSOFT TEAMS AND COLLABORATION TOOLS</option>
                                            <option value="TIME MANAGEMENT USING MICROSOFT 365 TOOLS">TIME MANAGEMENT USING MICROSOFT 365 TOOLS</option>
                                            <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                                            <option value="ANTI BRRIBERY & ANTI CORRUPTION">ANTI BRRIBERY & ANTI CORRUPTION</option>
                                            <option value="OTHERS">OTHERS</option>
                                            </select>
                                            <br>
                                            <input type="text" name="otrse${noidse}" id="otrse${noidse}" class="form-control" value="${data.othertr}" />
                                        </td>
                                        <td>
                                            <select name="targetskse${noidse}" id="targetskse${noidse}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="currentskse${noidse}" id="currentskse${noidse}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="text" name="gapse${noidse}" id="gapse${noidse}" class="form-control" value="${data.gap}" readonly />
                                        </td>
                                        <td>
                                            <select name="trtypese${noidse}" id="trtypese${noidse}" class="form-control">
                                                <option selected disabled>-- Select Training Type --</option>
                                                <option value="1">1 - On Job Training</option>
                                                <option value="2">2 - Coaching</option>
                                                <option value="3">3 - External / In-house</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="datetrse${noidse}" id="datetrse${noidse}" class="form-control">
                                                <option value="Jan">Jan</option>
                                                <option value="Feb">Feb</option>
                                                <option value="Mar">Mar</option>
                                                <option value="Apr">Apr</option>
                                                <option value="May">May</option>
                                                <option value="Jun">Jun</option>
                                                <option value="Jul">Jul</option>
                                                <option value="Aug">Aug</option>
                                                <option value="Sep">Sep</option>
                                                <option value="Oct">Oct</option>
                                                <option value="Nov">Nov</option>
                                                <option value="Dec">Dec</option>
                                            </select>
                                        </td>
                                        <td>
                                            <a href="#" class="remove_self btn btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    `;
                            targetskse.push(data.targetskill);
                            currentskse.push(data.currentskill);
                            trtypese.push(data.trainingtype);
                            datetrse.push(data.monthapply);
                            trainingse.push(data.training);
                            noidse++;
                        } else if (data.section == 'leadaware') {
                            trHTMLle += `
                                    <tr>
                                        <td style="text-align:center;">${noidle}</td>
                                        <td>
                                            <textarea class="form-control" name="taskle${noidle}" id="taskle${noidle}" rows="2">${data.task}</textarea>
                                        </td>
                                        <td>
                                            <select name="trainingle${noidle}" id="trainingle${noidle}" class="form-control">
                                            <option selected disabled>-- Select Training --</option>
                                                <option value="ETHICAL LEADERSHIP & GOVERNANCE">ETHICAL LEADERSHIP & GOVERNANCE</option>
                                                <option value="RESILIENCE & STRESS MANAGEMENT">RESILIENCE & STRESS MANAGEMENT</option>
                                                <option value="INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS">INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS</option>
                                                <option value="HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS">HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS</option>
                                                <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                                                <option value="LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING">LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING</option>
                                                <option value="STRATEGIC LEADERSHIP AND DECISION-MAKING">STRATEGIC LEADERSHIP AND DECISION-MAKING</option>
                                                <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                                <option value="CUSTOMER-CENTRIC LEADERSHIP">CUSTOMER-CENTRIC LEADERSHIP</option>
                                                <option value="STRATEGIC SUPPLY CHAIN LEADERSHIP">STRATEGIC SUPPLY CHAIN LEADERSHIP</option>
                                                <option value="CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE">CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE</option>
                                                <option value="OTHERS">OTHERS</option>
                                            </select>
                                            <br>
                                            <input type="text" name="otrle${noidle}" id="otrle${noidle}" class="form-control" value="${data.othertr}" />
                                        </td>
                                        <td>
                                            <select name="targetskle${noidle}" id="targetskle${noidle}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="currentskle${noidle}" id="currentskle${noidle}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="text" name="gaple${noidle}" id="gaple${noidle}" class="form-control" value="${data.gap}" readonly />
                                        </td>
                                        <td>
                                            <select name="trtypele${noidle}" id="trtypele${noidle}" class="form-control">
                                                <option selected disabled>-- Select Training Type --</option>
                                                <option value="1">1 - On Job Training</option>
                                                <option value="2">2 - Coaching</option>
                                                <option value="3">3 - External / In-house</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="datetrle${noidle}" id="datetrle${noidle}" class="form-control">
                                                <option value="Jan">Jan</option>
                                                <option value="Feb">Feb</option>
                                                <option value="Mar">Mar</option>
                                                <option value="Apr">Apr</option>
                                                <option value="May">May</option>
                                                <option value="Jun">Jun</option>
                                                <option value="Jul">Jul</option>
                                                <option value="Aug">Aug</option>
                                                <option value="Sep">Sep</option>
                                                <option value="Oct">Oct</option>
                                                <option value="Nov">Nov</option>
                                                <option value="Dec">Dec</option>
                                            </select>
                                        </td>
                                        <td>
                                            <a href="#" class="remove_lead btn btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            targetskle.push(data.targetskill);
                            currentskle.push(data.currentskill);
                            trtypele.push(data.trainingtype);
                            datetrle.push(data.monthapply);
                            trainingle.push(data.training);
                            noidle++;
                        } else if (data.section == 'dataaware') {
                            trHTMLda += `
                                    <tr>
                                        <td style="text-align:center;">${noidda}</td>
                                        <td>
                                            <textarea class="form-control" name="taskda${noidda}" id="taskda${noidda}" rows="2">${data.task}</textarea>
                                        </td>
                                        <td>
                                            <select name="trainingda${noidda}" id="trainingda${noidda}" class="form-control">
                                                <option selected disabled>-- Select Training --</option>
                                                <option value="DATA PRIVACY & PDPA 2010 IN MANUFACTURING">DATA PRIVACY & PDPA 2010 IN MANUFACTURING</option>
                                                <option value="AI & ROBOTICS IN AUTOMOTIVE">AI & ROBOTICS IN AUTOMOTIVE</option>
                                                <option value="EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)">EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)</option>
                                                <option value="MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION">MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION</option>
                                                <option value="MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS">MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS</option>
                                                <option value="MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION">MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION</option>
                                                <option value="MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION">MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION</option>
                                                <option value="OTHERS">OTHERS</option>
                                            </select>
                                            <br>
                                            <input type="text" name="otrda${noidda}" id="otrda${noidda}" class="form-control" value="${data.othertr}" />
                                        </td>
                                        <td>
                                            <select name="targetskda${noidda}" id="targetskda${noidda}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="currentskda${noidda}" id="currentskda${noidda}" class="form-control">
                                                <option value="1">1 - Fundamental Awareness</option>
                                                <option value="2">2 - Novice</option>
                                                <option value="3">3 - Intermediate</option>
                                                <option value="4">4 - Proficient</option>
                                                <option value="5">5 - Expert</option>
                                            </select>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="text" name="gapda${noidda}" id="gapda${noidda}" class="form-control" value="${data.gap}" readonly />
                                        </td>
                                        <td>
                                            <select name="trtypeda${noidda}" id="trtypeda${noidda}" class="form-control">
                                                <option selected disabled>-- Select Training Type --</option>
                                                <option value="1">1 - On Job Training</option>
                                                <option value="2">2 - Coaching</option>
                                                <option value="3">3 - External / In-house</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="datetrda${noidda}" id="datetrda${noidda}" class="form-control">
                                                <option value="Jan">Jan</option>
                                                <option value="Feb">Feb</option>
                                                <option value="Mar">Mar</option>
                                                <option value="Apr">Apr</option>
                                                <option value="May">May</option>
                                                <option value="Jun">Jun</option>
                                                <option value="Jul">Jul</option>
                                                <option value="Aug">Aug</option>
                                                <option value="Sep">Sep</option>
                                                <option value="Oct">Oct</option>
                                                <option value="Nov">Nov</option>
                                                <option value="Dec">Dec</option>
                                            </select>
                                        </td>
                                        <td>
                                            <a href="#" class="remove_driven btn btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            targetskda.push(data.targetskill);
                            currentskda.push(data.currentskill);
                            trtypeda.push(data.trainingtype);
                            datetrda.push(data.monthapply);
                            trainingda.push(data.training);
                            noidda++;
                        } else if (data.section == 'functional') {
                            trHTMLfu += `
                                                <tr>
                                                    <td style="text-align:center;">${noidfu}</td>
                                                    <td>
                                                    <textarea class="form-control" name="taskfu${noidfu}" id="taskfu${noidfu}" rows="2">
                                                        ${data.task}
                                                    </textarea>
                                                    </td>
                                                    <td>
                                                    <select name="trainingfu${noidfu}" id="trainingfu${noidfu}" class="form-control">
                                                    <option selected disabled>-- Select Training --</option>
                                                    <optgroup label="IT, TECHNICAL & MAINTENANCE">
                                                        <option value="PREDICTIVE MAINTENANCE USING IOT">PREDICTIVE MAINTENANCE USING IOT</option>
                                                        <option value="EV COMPONENT SAFETY STANDARDS">EV COMPONENT SAFETY STANDARDS</option>
                                                        <option value="COST REDUCTION TECHNIQUES FOR MAINTENANCE">COST REDUCTION TECHNIQUES FOR MAINTENANCE</option>
                                                        <option value="IP & PATENT">IP & PATENT</option>
                                                        <option value="KARAKURI">KARAKURI</option>
                                                        <option value="CATIA">CATIA</option>
                                                        <option value="CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION">CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION</option>
                                                        <option value="MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING">MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING</option>
                                                        <option value="CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING">CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING</option>
                                                        <option value="ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE">ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE</option>
                                                        <option value="EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE">EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE</option>
                                                        <option value="PLC PROGRAMMING AND AUTOMATION CONTROL">PLC PROGRAMMING AND AUTOMATION CONTROL</option>
                                                        <option value="IT INFRASTRUCTURE AND NETWORK MANAGEMENT">IT INFRASTRUCTURE AND NETWORK MANAGEMENT</option>
                                                        <option value="CYBERSECURITY AND DATA PROTECTION">CYBERSECURITY AND DATA PROTECTION</option>
                                                        <option value="TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES">TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES</option>
                                                        <option value="INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS">INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS</option>
                                                        <option value="GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS">GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS</option>
                                                        <option value="MACHINE LEARNING FOR PROCESS OPTIMISATION">MACHINE LEARNING FOR PROCESS OPTIMISATION</option>
                                                        <option value="INDUSTRIAL ROBOT SAFETY AND COMPLIANCE">INDUSTRIAL ROBOT SAFETY AND COMPLIANCE</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="MANUFACTURING / OPERATION">
                                                        <option value="MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)">MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)</option>
                                                        <option value="ERGONOMICS & MANUAL HANDLING IN PRODUCTION">ERGONOMICS & MANUAL HANDLING IN PRODUCTION</option>
                                                        <option value="STAMPING PRESS OPERATION & MAINTENANCE">STAMPING PRESS OPERATION & MAINTENANCE</option>
                                                        <option value="DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING">DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING</option>
                                                        <option value="ROLL FORMING TECHNOLOGY & DEFECT PREVENTION">ROLL FORMING TECHNOLOGY & DEFECT PREVENTION</option>
                                                        <option value="WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS">WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS</option>
                                                        <option value="ROBOTIC WELDING & AUTOMATION IN ASSEMBLY">ROBOTIC WELDING & AUTOMATION IN ASSEMBLY</option>
                                                        <option value="STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES">STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES</option>
                                                        <option value="ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION">ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION</option>
                                                        <option value="FUNDAMENTALS OF ELECTRODEPOSITION">FUNDAMENTALS OF ELECTRODEPOSITION</option>
                                                        <option value="MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS">MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS</option>
                                                        <option value="ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS">ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS</option>
                                                        <option value="5S TRAINING">5S TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="QUALITY SYSTEMS & PRODUCTIVITY IMPROVEMENT">
                                                        <option value="ISO 50001:2018 ENERGY MANAGEMENT SYSTEM">ISO 50001:2018 ENERGY MANAGEMENT SYSTEM</option>
                                                        <option value="IATF 16949:2024 TRANSITION TRAINING">IATF 16949:2024 TRANSITION TRAINING</option>
                                                        <option value="ISO 37301 COMPLIANCE MANAGEMENT">ISO 37301 COMPLIANCE MANAGEMENT</option>
                                                        <option value="INTERNAL AUDIT AND CONTROLS">INTERNAL AUDIT AND CONTROLS</option>
                                                        <option value="BUSINESS CONTINUITY AND CRISIS MANAGEMENT">BUSINESS CONTINUITY AND CRISIS MANAGEMENT</option>
                                                        <option value="RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES">RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES</option>
                                                        <option value="KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT">KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT</option>
                                                        <option value="LEAN PRODUCTION SYSTEM TRAINING">LEAN PRODUCTION SYSTEM TRAINING</option>
                                                        <option value="POKA YOKE - WHAT YOU NEED TO KNOW">POKA YOKE - WHAT YOU NEED TO KNOW</option>
                                                        <option value="SIX SIGMA TOOLS FOR IMPROVEMENT">SIX SIGMA TOOLS FOR IMPROVEMENT</option>
                                                        <option value="8D PROBLEM SOLVING">8D PROBLEM SOLVING</option>
                                                        <option value="TESTING & LABORATORY MANAGEMENT">TESTING & LABORATORY MANAGEMENT</option>
                                                        <option value="STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT">STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT</option>
                                                        <option value="PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE">PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE</option>
                                                        <option value="SUPPLIER QUALITY MANAGEMENT AND AUDIT">SUPPLIER QUALITY MANAGEMENT AND AUDIT</option>
                                                        <option value="THE 7 NEW QC MANAGEMENT TOOLS">THE 7 NEW QC MANAGEMENT TOOLS</option>
                                                        <option value="ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING">ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING</option>
                                                        <option value="ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING">ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING</option>
                                                        <option value="LEAD AUDITOR IATF">LEAD AUDITOR IATF</option>
                                                        <option value="ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING">ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="INDUSTRIAL SAFETY">
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS">OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS</option>
                                                        <option value="INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES">INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES</option>
                                                        <option value="HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)">HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)</option>
                                                        <option value="FIRE SAFETY AND EMERGENCY RESPONSE">FIRE SAFETY AND EMERGENCY RESPONSE</option>
                                                        <option value="ERGONOMICS AND MANUAL HANDLING SAFETY">ERGONOMICS AND MANUAL HANDLING SAFETY</option>
                                                        <option value="HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE">HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE</option>
                                                        <option value="CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING">CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING</option>
                                                        <option value="SAFETY LEADERSHIP AND CULTURE BUILDING">SAFETY LEADERSHIP AND CULTURE BUILDING</option>
                                                        <option value="INCIDENT INVESTIGATION AND REPORTING">INCIDENT INVESTIGATION AND REPORTING</option>
                                                        <option value="EV CHARGER AND ELECTRICAL SAFETY">EV CHARGER AND ELECTRICAL SAFETY</option>
                                                        <option value="OVERHEAD CRANE">OVERHEAD CRANE</option>
                                                        <option value="FORKLIFT">FORKLIFT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="WORKING AT HEIGHT">WORKING AT HEIGHT</option>
                                                        <option value="SAFETY AWARENESS">SAFETY AWARENESS</option>
                                                        <option value="SCHEDULED WASTE MANAGEMENT">SCHEDULED WASTE MANAGEMENT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="HUMAN CAPITAL">
                                                        <option value="SUCCESSION PLANNING">SUCCESSION PLANNING</option>
                                                        <option value="ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)">ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)</option>
                                                        <option value="HRD CORP 2026 CLAIMABLE TRAINING RULES">HRD CORP 2026 CLAIMABLE TRAINING RULES</option>
                                                        <option value="TALENT ACQUISITION AND RECRUITMENT STRATEGIES">TALENT ACQUISITION AND RECRUITMENT STRATEGIES</option>
                                                        <option value="PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING">PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING</option>
                                                        <option value="EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES">EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES</option>
                                                        <option value="LEARNING & DEVELOPMENT PLANNING">LEARNING & DEVELOPMENT PLANNING</option>
                                                        <option value="COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT">COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT</option>
                                                        <option value="LABOUR LAW AND EMPLOYMENT COMPLIANCE">LABOUR LAW AND EMPLOYMENT COMPLIANCE</option>
                                                        <option value="HR ANALYTICS AND PEOPLE DATA MANAGEMENT">HR ANALYTICS AND PEOPLE DATA MANAGEMENT</option>
                                                        <option value="COACHING AND MENTORING SKILLS FOR MANAGERS">COACHING AND MENTORING SKILLS FOR MANAGERS</option>
                                                        <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                                        <option value="DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING">DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING</option>
                                                        <option value="EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS">EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LEGAL & GOVERNANCE">
                                                        <option value="CONTRACT AND LEGAL COMPLIANCE MANAGEMENT">CONTRACT AND LEGAL COMPLIANCE MANAGEMENT</option>
                                                        <option value="CORPORATE AND COMMERCIAL LAW AWARENESS">CORPORATE AND COMMERCIAL LAW AWARENESS</option>
                                                        <option value="REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY">REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY</option>
                                                        <option value="DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT">DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT</option>
                                                        <option value="INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT">INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="SALES, MARKETING & CUSTOMER SERVICE">
                                                        <option value="B2B SALES STRATEGIES FOR MANUFACTURING">B2B SALES STRATEGIES FOR MANUFACTURING</option>
                                                        <option value="INDUSTRIAL MARKETING AND BRAND POSITIONING">INDUSTRIAL MARKETING AND BRAND POSITIONING</option>
                                                        <option value="CUSTOMER RELATIONSHIP MANAGEMENT (CRM)">CUSTOMER RELATIONSHIP MANAGEMENT (CRM)</option>
                                                        <option value="TECHNICAL PRODUCT PRESENTATION SKILLS">TECHNICAL PRODUCT PRESENTATION SKILLS</option>
                                                        <option value="NEGOTIATION AND CLOSING TECHNIQUES">NEGOTIATION AND CLOSING TECHNIQUES</option>
                                                        <option value="MARKET AND COMPETITOR ANALYSIS">MARKET AND COMPETITOR ANALYSIS</option>
                                                        <option value="SALES FORECASTING AND BUSINESS METRICS">SALES FORECASTING AND BUSINESS METRICS</option>
                                                        <option value="CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS">CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="BUSINESS & MANAGEMENT">
                                                        <option value="BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS">BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS</option>
                                                        <option value="AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS">AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS</option>
                                                        <option value="FINANCIAL LITERACY FOR NON-FINANCE MANAGERS">FINANCIAL LITERACY FOR NON-FINANCE MANAGERS</option>
                                                        <option value="STRATEGIC THINKING AND BUSINESS DECISION-MAKING">STRATEGIC THINKING AND BUSINESS DECISION-MAKING</option>
                                                        <option value="CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS">CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS</option>
                                                        <option value="PROJECT ROI AND BUSINESS IMPACT ANALYSIS">PROJECT ROI AND BUSINESS IMPACT ANALYSIS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="FINANCIAL MANAGEMENT">
                                                        <option value="ESG-LINKED FINANCE & GREEN TAX INCENTIVES">ESG-LINKED FINANCE & GREEN TAX INCENTIVES</option>
                                                        <option value="TRANSFER PRICING & TAXATION UPDATES 2026">TRANSFER PRICING & TAXATION UPDATES 2026</option>
                                                        <option value="STRATEGIC COST MANAGEMENT IN AUTOMOTIVE">STRATEGIC COST MANAGEMENT IN AUTOMOTIVE</option>
                                                        <option value="REGULATORY REPORTING AND AUDIT READINESS">REGULATORY REPORTING AND AUDIT READINESS</option>
                                                        <option value="E-INVOICING">E-INVOICING</option>
                                                        <option value="FINANCIAL PLANNING AND BUDGETING">FINANCIAL PLANNING AND BUDGETING</option>
                                                        <option value="FINANCIAL REGULATORY COMPLIANCE AND REPORTING">FINANCIAL REGULATORY COMPLIANCE AND REPORTING</option>
                                                        <option value="CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION">CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION</option>
                                                        <option value="RISK MANAGEMENT AND INTERNAL CONTROLS">RISK MANAGEMENT AND INTERNAL CONTROLS</option>
                                                        <option value="FINANCIAL ANALYSIS AND DECISION-MAKING">FINANCIAL ANALYSIS AND DECISION-MAKING</option>
                                                        <option value="TAXATION AND GST/SST COMPLIANCE">TAXATION AND GST/SST COMPLIANCE</option>
                                                        <option value="PROCUREMENT AND FINANCE COLLABORATION">PROCUREMENT AND FINANCE COLLABORATION</option>
                                                        <option value="FINANCIAL SYSTEMS AND ERP UTILISATION">FINANCIAL SYSTEMS AND ERP UTILISATION</option>
                                                        <option value="INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT">INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT</option>
                                                        <option value="AI INNOVATIONS FOR FINANCIAL PROFESSIONALS">AI INNOVATIONS FOR FINANCIAL PROFESSIONALS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LOGISTICS / SCM / WAREHOUSE / INVENTORY">
                                                        <option value="SUPPLIER ESG COMPLIANCE AUDITS">SUPPLIER ESG COMPLIANCE AUDITS</option>
                                                        <option value="DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS">DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS</option>
                                                        <option value="MITI IMPORT/EXPORT REGULATORY UPDATES">MITI IMPORT/EXPORT REGULATORY UPDATES</option>
                                                        <option value="SUPPLY CHAIN MANAGEMENT FUNDAMENTALS">SUPPLY CHAIN MANAGEMENT FUNDAMENTALS</option>
                                                        <option value="WAREHOUSE MANAGEMENT AND INVENTORY CONTROL">WAREHOUSE MANAGEMENT AND INVENTORY CONTROL</option>
                                                        <option value="LOGISTICS AND TRANSPORTATION MANAGEMENT">LOGISTICS AND TRANSPORTATION MANAGEMENT</option>
                                                        <option value="DEMAND FORECASTING AND INVENTORY OPTIMISATION">DEMAND FORECASTING AND INVENTORY OPTIMISATION</option>
                                                        <option value="SUSTAINABLE SUPPLY CHAIN PRACTICES">SUSTAINABLE SUPPLY CHAIN PRACTICES</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="PROCUREMENT & VENDOR DEVELOPMENT">
                                                        <option value="AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS">AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS</option>
                                                        <option value="STRATEGIC SOURCING AND SUPPLIER SELECTION">STRATEGIC SOURCING AND SUPPLIER SELECTION</option>
                                                        <option value="SUPPLIER PERFORMANCE MANAGEMENT (SPM)">SUPPLIER PERFORMANCE MANAGEMENT (SPM)</option>
                                                        <option value="CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT">CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT</option>
                                                        <option value="COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)">COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)</option>
                                                        <option value="VENDOR DEVELOPMENT AND COLLABORATION">VENDOR DEVELOPMENT AND COLLABORATION</option>
                                                        <option value="DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION">DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION</option>
                                                        <option value="SUSTAINABLE PROCUREMENT AND ESG PRACTICES">SUSTAINABLE PROCUREMENT AND ESG PRACTICES</option>
                                                        <option value="SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION">SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION</option>
                                                        <option value="ADVANCED PROCUREMENT">ADVANCED PROCUREMENT</option>
                                                        <option value="DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S">DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="GOVERNANCE RISK AND COMPLIANCE">
                                                        <option value="COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY">COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY</option>
                                                        <option value="ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE">ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE</option>
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="REGISTERED ELECTRICAL ENERGY MANAGER (REM)">REGISTERED ELECTRICAL ENERGY MANAGER (REM)</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>
                                                    <br>
                                                    <input type="text" name="otrfu${noidfu}" id="otrfu${noidfu}" class="form-control" value="${data.othertr}" />
                                                    </td>
                                                    <td>
                                                    <select name="targetskfu${noidfu}" id="targetskfu${noidfu}" class="form-control">
                                                        <option value="1">1 - Fundamental Awareness</option>
                                                        <option value="2">2 - Novice</option>
                                                        <option value="3">3 - Intermediate</option>
                                                        <option value="4">4 - Proficient</option>
                                                        <option value="5">5 - Expert</option>
                                                    </select>
                                                    </td>
                                                    <td>
                                                    <select name="currentskfu${noidfu}" id="currentskfu${noidfu}" class="form-control">
                                                        <option value="1">1 - Fundamental Awareness</option>
                                                        <option value="2">2 - Novice</option>
                                                        <option value="3">3 - Intermediate</option>
                                                        <option value="4">4 - Proficient</option>
                                                        <option value="5">5 - Expert</option>
                                                    </select>
                                                    </td>
                                                    <td style="text-align:center;">
                                                    <input type="text" name="gapfu${noidfu}" id="gapfu${noidfu}" class="form-control" value="${data.gap}" readonly />
                                                    </td>
                                                    <td>
                                                    <select name="trtypefu${noidfu}" id="trtypefu${noidfu}" class="form-control">
                                                        <option selected disabled>-- Select Training Type --</option>
                                                        <option value="1">1 - On Job Training</option>
                                                        <option value="2">2 - Coaching</option>
                                                        <option value="3">3 - External / In-house</option>
                                                    </select>
                                                    </td>
                                                    <td>
                                                    <select name="datetrfu${noidfu}" id="datetrfu${noidfu}" class="form-control">
                                                        <option value="Jan">Jan</option>
                                                        <option value="Feb">Feb</option>
                                                        <option value="Mar">Mar</option>
                                                        <option value="Apr">Apr</option>
                                                        <option value="May">May</option>
                                                        <option value="Jun">Jun</option>
                                                        <option value="Jul">Jul</option>
                                                        <option value="Aug">Aug</option>
                                                        <option value="Sep">Sep</option>
                                                        <option value="Oct">Oct</option>
                                                        <option value="Nov">Nov</option>
                                                        <option value="Dec">Dec</option>
                                                    </select>
                                                    </td>
                                                    <td>
                                                    <a href="#" class="remove_func btn btn-danger">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </a>
                                                    </td>
                                                </tr>
                                                `;
                            targetskfu.push(data.targetskill);
                            currentskfu.push(data.currentskill);
                            trtypefu.push(data.trainingtype);
                            datetrfu.push(data.monthapply);
                            trainingfu.push(data.training);
                            noidfu++;
                        } else if (data.section == 'busiaware') {
                            trHTMLbu += `
                                            <tr>
                                                <td style="text-align:center;">${noidbu}</td>
                                                <td>
                                                    <textarea class="form-control" name="taskbu${noidbu}" id="taskbu${noidbu}" rows="2">${data.task}</textarea>
                                                </td>
                                                <td>
                                                    <select name="trainingbu${noidbu}" id="trainingbu${noidbu}" class="form-control">
                                                        <option selected disabled>-- Select Training --</option>
                                                            <optgroup label="DIGITAL TRANSFORMATION & INNOVATION">
                                                                <option value="INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES">INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES</option>
                                                                <option value="DIGITAL SUPPLY CHAIN OPTIMISATION">DIGITAL SUPPLY CHAIN OPTIMISATION</option>
                                                                <option value="DIGITAL TWINS IN MANUFACTURING">DIGITAL TWINS IN MANUFACTURING</option>
                                                                <option value="ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES">ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES</option>
                                                                <option value="ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING">ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING</option>
                                                                <option value="DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY">DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY</option>
                                                                <option value="INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION">INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION</option>
                                                                <option value="AGILE AND LEAN DIGITAL PROJECT MANAGEMENT">AGILE AND LEAN DIGITAL PROJECT MANAGEMENT</option>
                                                                <option value="PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING">PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING</option>
                                                                <option value="CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS">CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS</option>
                                                                <option value="THE MODERN WORKPLACE">THE MODERN WORKPLACE</option>
                                                                <option value="OTHERS">OTHERS</option>
                                                            </optgroup>
                                                        </optgroup>
                                                    </select>
                                                    <br>
                                                    <input type="text" name="otrbu${noidbu}" id="otrbu${noidbu}" class="form-control" value="${data.othertr}" />
                                                </td>
                                                <td>
                                                    <select name="targetskbu${noidbu}" id="targetskbu${noidbu}" class="form-control">
                                                        <option value="1">1 - Fundamental Awareness</option>
                                                        <option value="2">2 - Novice</option>
                                                        <option value="3">3 - Intermediate</option>
                                                        <option value="4">4 - Proficient</option>
                                                        <option value="5">5 - Expert</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="currentskbu${noidbu}" id="currentskbu${noidbu}" class="form-control">
                                                        <option value="1">1 - Fundamental Awareness</option>
                                                        <option value="2">2 - Novice</option>
                                                        <option value="3">3 - Intermediate</option>
                                                        <option value="4">4 - Proficient</option>
                                                        <option value="5">5 - Expert</option>
                                                    </select>
                                                </td>
                                                <td style="text-align:center;">
                                                    <input type="text" name="gapbu${noidbu}" id="gapbu${noidbu}" class="form-control" value="${data.gap}" readonly />
                                                </td>
                                                <td>
                                                    <select name="trtypebu${noidbu}" id="trtypebu${noidbu}" class="form-control">
                                                        <option selected disabled>-- Select Training Type --</option>
                                                        <option value="1">1 - On Job Training</option>
                                                        <option value="2">2 - Coaching</option>
                                                        <option value="3">3 - External / In-house</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="datetrbu${noidbu}" id="datetrbu${noidbu}" class="form-control">
                                                        <option value="Jan">Jan</option>
                                                        <option value="Feb">Feb</option>
                                                        <option value="Mar">Mar</option>
                                                        <option value="Apr">Apr</option>
                                                        <option value="May">May</option>
                                                        <option value="Jun">Jun</option>
                                                        <option value="Jul">Jul</option>
                                                        <option value="Aug">Aug</option>
                                                        <option value="Sep">Sep</option>
                                                        <option value="Oct">Oct</option>
                                                        <option value="Nov">Nov</option>
                                                        <option value="Dec">Dec</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <a href="#" class="remove_busi btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                                </td>
                                            </tr>`;
                            targetskbu.push(data.targetskill);
                            currentskbu.push(data.currentskill);
                            trtypebu.push(data.trainingtype);
                            datetrbu.push(data.monthapply);
                            trainingbu.push(data.training);
                            noidbu++;
                        } else if (data.section == 'special') {
                            trHTMLsp += '<tr><td style="text-align:center;">' + noidsp +
                                '</td><td><textarea class="form-control" name="tasksp' +
                                noidsp + '" id="tasksp' + noidsp + '" rows="2">' + data
                                .task + '</textarea></td><td><select name="trainingsp' +
                                noidsp + '" id="trainingsp' + noidsp +
                                '" class="form-control"><option selected disabled="disabled">-- Select Training --</option><option value="OTHERS">OTHERS</option></select><br><input type="text" name="otrsp' +
                                noidsp + '" id="otrsp' + noidsp +
                                '" class="form-control" value="' + data.othertr +
                                '"/></td><td><select name="targetsksp' + noidsp +
                                '" id="targetsksp' + noidsp +
                                '" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsksp' +
                                noidsp + '" id="currentsksp' + noidsp +
                                '" class="form-control"><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gapsp' +
                                noidsp + '" id="gapsp' + noidsp +
                                '" class="form-control" value="' + data.gap +
                                '" readonly /></td><td><select name="trtypesp' +
                                noidsp + '" id="trtypesp' + noidsp +
                                '" class="form-control"><option selected disabled="disabled">-- Select Training Type --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="3">3 - External / In-house</option></select></td><td><select name="datetrsp' +
                                noidsp + '" id="datetrsp' + noidsp +
                                '" class="form-control"><option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option></select></td><td><a href="#" class="remove_spec btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td></tr>';
                            targetsksp.push(data.targetskill);
                            currentsksp.push(data.currentskill);
                            trtypesp.push(data.trainingtype);
                            datetrsp.push(data.monthapply);
                            trainingsp.push(data.training);
                            noidsp++;
                        }
                    });
                    $('#tnaesglist').append(trHTMLes);
                    $('#tnaselflist').append(trHTMLse);
                    $('#tnaleadlist').append(trHTMLle);
                    $('#tnadrivenlist').append(trHTMLda);
                    $('#tnafunclist').append(trHTMLfu);
                    $('#tnabusilist').append(trHTMLbu);
                    $('#tnaspeclist').append(trHTMLsp);

                    // UPDATE ESG NEW

                    for (t = 1; t < noides; t++) {
                        $('#targetskes' + t).val(targetskes[t - 1]);
                        $('#currentskes' + t).val(currentskes[t - 1]);
                        $('#trtypees' + t).val(trtypees[t - 1]);
                        $('#datetres' + t).val(datetres[t - 1]);
                        $('#traininges' + t).val(traininges[t - 1]);

                        if ($('#traininges' + t).val() != 'OTHERS') {
                            $('#otres' + t).hide();
                        }

                        $('#traininges' + t).on('change', function() {
                            var ides = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otres' + ides).show();
                            } else {
                                $('#otres' + ides).hide();
                            }
                        });

                        var targetes = parseInt($('#targetskes' + t).val());
                        var currentes = parseInt($('#currentskes' + t).val());

                        $('#targetskes' + t).on('change', function() {
                            var ides = $(this).attr("id").slice(-1);
                            var targetes = parseInt($('#targetskes' + idse).val());
                            var currentes = parseInt($('#currentskes' + idse).val());

                            var gapes = targetes - currentes;
                            $('#gapes' + ides).val(gapes);
                        });

                        $('#currentskes' + t).on('change', function() {
                            var ides = $(this).attr("id").slice(-1);
                            var targetes = parseInt($('#targetskes' + ides).val());
                            var currentes = parseInt($('#currentskes' + ides).val());

                            var gapes = targetes - currentes;
                            $('#gapes' + ides).val(gapes);
                        });
                    }

                    countesg = noides - 1;

                    $(document).on('click', '.remove_esg', function() {
                        countesg--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#esgaware').val(countesg);

                    // UPDATE ESG NEW

                    for (t = 1; t < noidse; t++) {
                        $('#targetskse' + t).val(targetskse[t - 1]);
                        $('#currentskse' + t).val(currentskse[t - 1]);
                        $('#trtypese' + t).val(trtypese[t - 1]);
                        $('#datetrse' + t).val(datetrse[t - 1]);
                        $('#trainingse' + t).val(trainingse[t - 1]);

                        if ($('#trainingse' + t).val() != 'OTHERS') {
                            $('#otrse' + t).hide();
                        }

                        $('#trainingse' + t).on('change', function() {
                            var idse = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrse' + idse).show();
                            } else {
                                $('#otrse' + idse).hide();
                            }
                        });

                        var targetse = parseInt($('#targetskse' + t).val());
                        var currentse = parseInt($('#currentskse' + t).val());

                        $('#targetskse' + t).on('change', function() {
                            var idse = $(this).attr("id").slice(-1);
                            var targetse = parseInt($('#targetskse' + idse).val());
                            var currentse = parseInt($('#currentskse' + idse).val());

                            var gapse = targetse - currentse;
                            $('#gapse' + idse).val(gapse);
                        });

                        $('#currentskse' + t).on('change', function() {
                            var idse = $(this).attr("id").slice(-1);
                            var targetse = parseInt($('#targetskse' + idse).val());
                            var currentse = parseInt($('#currentskse' + idse).val());

                            var gapse = targetse - currentse;
                            $('#gapse' + idse).val(gapse);
                        });
                    }

                    countself = noidse - 1;

                    $(document).on('click', '.remove_self', function() {
                        countself--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#selfaware').val(countself);

                    for (t = 1; t < noidle; t++) {
                        $('#targetskle' + t).val(targetskle[t - 1]);
                        $('#currentskle' + t).val(currentskle[t - 1]);
                        $('#trtypele' + t).val(trtypele[t - 1]);
                        $('#datetrle' + t).val(datetrle[t - 1]);
                        $('#trainingle' + t).val(trainingle[t - 1]);

                        if ($('#trainingle' + t).val() != 'OTHERS') {
                            $('#otrle' + t).hide();
                        }

                        $('#trainingle' + t).on('change', function() {
                            var idle = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrle' + idle).show();
                            } else {
                                $('#otrle' + idle).hide();
                            }
                        });

                        var targetle = parseInt($('#targetskle' + t).val());
                        var currentle = parseInt($('#currentskle' + t).val());

                        $('#targetskle' + t).on('change', function() {
                            var idle = $(this).attr("id").slice(-1);
                            var targetle = parseInt($('#targetskle' + idle).val());
                            var currentle = parseInt($('#currentskle' + idle).val());

                            var gaple = targetle - currentle;
                            $('#gaple' + idle).val(gaple);
                        });

                        $('#currentskle' + t).on('change', function() {
                            var idle = $(this).attr("id").slice(-1);
                            var targetle = parseInt($('#targetskle' + idle).val());
                            var currentle = parseInt($('#currentskle' + idle).val());

                            var gaple = targetle - currentle;
                            $('#gaple' + idle).val(gaple);
                        });
                    }

                    countlead = noidle - 1;

                    $(document).on('click', '.remove_lead', function() {
                        countlead--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#leadaware').val(countlead);

                     // DATA DRIVEN UPDATE

                     for (t = 1; t < noidda; t++) {
                        $('#targetskda' + t).val(targetskda[t - 1]);
                        $('#currentskda' + t).val(currentskda[t - 1]);
                        $('#trtypeda' + t).val(trtypeda[t - 1]);
                        $('#datetrda' + t).val(datetrda[t - 1]);
                        $('#trainingda' + t).val(trainingda[t - 1]);

                        if ($('#trainingda' + t).val() != 'OTHERS') {
                            $('#otrda' + t).hide();
                        }

                        $('#trainingda' + t).on('change', function() {
                            var idda = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrda' + idda).show();
                            } else {
                                $('#otrda' + idda).hide();
                            }
                        });

                        var targetda = parseInt($('#targetskda' + t).val());
                        var currentda = parseInt($('#currentskda' + t).val());

                        $('#targetskda' + t).on('change', function() {
                            var idda = $(this).attr("id").slice(-1);
                            var targetda = parseInt($('#targetskda' + idda).val());
                            var currentda = parseInt($('#currentskda' + idda).val());

                            var gapda = targetda - currentda;
                            $('#gapda' + idda).val(gapda);
                        });

                        $('#currentskda' + t).on('change', function() {
                            var idda = $(this).attr("id").slice(-1);
                            var targetda = parseInt($('#targetskda' + idda).val());
                            var currentda = parseInt($('#currentskda' + idda).val());

                            var gapda = targetda - currentda;
                            $('#gapda' + idda).val(gapda);
                        });
                    }

                    countdriven = noidda - 1;

                    $(document).on('click', '.remove_driven', function() {
                        countdriven--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#dataaware').val(countdriven);

                    // DATA DRIVEN UPDATE


                    for (t = 1; t < noidfu; t++) {
                        $('#targetskfu' + t).val(targetskfu[t - 1]);
                        $('#currentskfu' + t).val(currentskfu[t - 1]);
                        $('#trtypefu' + t).val(trtypefu[t - 1]);
                        $('#datetrfu' + t).val(datetrfu[t - 1]);
                        $('#trainingfu' + t).val(trainingfu[t - 1]);

                        if ($('#trainingfu' + t).val() != 'OTHERS') {
                            $('#otrfu' + t).hide();
                        }

                        $('#trainingfu' + t).on('change', function() {
                            var idfu = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrfu' + idfu).show();
                            } else {
                                $('#otrfu' + idfu).hide();
                            }
                        });

                        var targetfu = parseInt($('#targetskfu' + t).val());
                        var currentfu = parseInt($('#currentskfu' + t).val());

                        $('#targetskfu' + t).on('change', function() {
                            var idfu = $(this).attr("id").slice(-1);
                            var targetfu = parseInt($('#targetskfu' + idfu).val());
                            var currentfu = parseInt($('#currentskfu' + idfu).val());

                            var gapfu = targetfu - currentfu;
                            $('#gapfu' + idfu).val(gapfu);
                        });

                        $('#currentskfu' + t).on('change', function() {
                            var idfu = $(this).attr("id").slice(-1);
                            var targetfu = parseInt($('#targetskfu' + idfu).val());
                            var currentfu = parseInt($('#currentskfu' + idfu).val());

                            var gapfu = targetfu - currentfu;
                            $('#gapfu' + idfu).val(gapfu);
                        });
                    }

                    countfunc = noidfu - 1;

                    $(document).on('click', '.remove_func', function() {
                        countfunc--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#functional').val(countfunc);

                    for (t = 1; t < noidbu; t++) {
                        $('#targetskbu' + t).val(targetskbu[t - 1]);
                        $('#currentskbu' + t).val(currentskbu[t - 1]);
                        $('#trtypebu' + t).val(trtypebu[t - 1]);
                        $('#datetrbu' + t).val(datetrbu[t - 1]);
                        $('#trainingbu' + t).val(trainingbu[t - 1]);

                        if ($('#trainingbu' + t).val() != 'OTHERS') {
                            $('#otrbu' + t).hide();
                        }

                        $('#trainingbu' + t).on('change', function() {
                            var idbu = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrbu' + idbu).show();
                            } else {
                                $('#otrbu' + idbu).hide();
                            }
                        });

                        var targetbu = parseInt($('#targetskbu' + t).val());
                        var currentbu = parseInt($('#currentskbu' + t).val());

                        $('#targetskbu' + t).on('change', function() {
                            var idbu = $(this).attr("id").slice(-1);
                            var targetbu = parseInt($('#targetskbu' + idbu).val());
                            var currentbu = parseInt($('#currentskbu' + idbu).val());

                            var gapbu = targetbu - currentbu;
                            $('#gapbu' + idbu).val(gapbu);
                        });

                        $('#currentskbu' + t).on('change', function() {
                            var idbu = $(this).attr("id").slice(-1);
                            var targetbu = parseInt($('#targetskbu' + idbu).val());
                            var currentbu = parseInt($('#currentskbu' + idbu).val());

                            var gapbu = targetbu - currentbu;
                            $('#gapbu' + idbu).val(gapbu);
                        });
                    }

                    countbusi = noidbu - 1;

                    $(document).on('click', '.remove_busi', function() {
                        countbusi--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#busiaware').val(countbusi);

                    for (t = 1; t < noidsp; t++) {
                        $('#targetsksp' + t).val(targetsksp[t - 1]);
                        $('#currentsksp' + t).val(currentsksp[t - 1]);
                        $('#trtypesp' + t).val(trtypesp[t - 1]);
                        $('#datetrsp' + t).val(datetrsp[t - 1]);
                        $('#trainingsp' + t).val(trainingsp[t - 1]);

                        if ($('#trainingsp' + t).val() != 'OTHERS') {
                            $('#otrsp' + t).hide();
                        }

                        $('#trainingsp' + t).on('change', function() {
                            var idsp = $(this).attr("id").slice(-1);
                            if ($(this).val() == 'OTHERS') {
                                $('#otrsp' + idsp).show();
                            } else {
                                $('#otrsp' + idsp).hide();
                            }
                        });

                        var targetsp = parseInt($('#targetsksp' + t).val());
                        var currentsp = parseInt($('#currentsksp' + t).val());

                        $('#targetsksp' + t).on('change', function() {
                            var idsp = $(this).attr("id").slice(-1);
                            var targetsp = parseInt($('#targetsksp' + idsp).val());
                            var currentsp = parseInt($('#currentsksp' + idsp).val());

                            var gapsp = targetsp - currentsp;
                            $('#gapsp' + idsp).val(gapsp);
                        });

                        $('#currentsksp' + t).on('change', function() {
                            var idsp = $(this).attr("id").slice(-1);
                            var targetsp = parseInt($('#targetsksp' + idsp).val());
                            var currentsp = parseInt($('#currentsksp' + idsp).val());

                            var gapsp = targetsp - currentsp;
                            $('#gapsp' + idsp).val(gapsp);
                        });
                    }

                    countspec = noidsp - 1;

                    $(document).on('click', '.remove_spec', function() {
                        countspec--;
                        $(this).closest('tr').remove();
                        return false;
                    });

                    $('#special').val(countspec);
                }
            });
        }
    }
});

$('#add_esg').click(function() {
    countesg++;
    if (countesg == 1) {
        $("#tnaesglist").append(`
                <tr>
                    <td style="text-align:center;">${countesg}</td>
                    <td>
                        <textarea class="form-control" name="taskes${countesg}" id="taskes${countesg}" rows="2" placeholder="Insert your problem statement" autocomplete="off" required></textarea>
                    </td>
                    <td>
                        <select name="traininges${countesg}" id="traininges${countesg}" class="form-control" required>
                            <option selected disabled value="">-- Select Training --</option>
                                                <option value="CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE">CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE</option>
                                                <option value="EU BATTERY REGULATION AWARENESS">EU BATTERY REGULATION AWARENESS</option>
                                                <option value="ESG REPORTING STANDARDS (BURSA, SC)">ESG REPORTING STANDARDS (BURSA, SC)</option>
                                                <option value="LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE">LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE</option>
                                                <option value="WASTE REDUCTION & CIRCULAR ECONOMY">WASTE REDUCTION & CIRCULAR ECONOMY</option>
                                                <option value="ESG AWARENESS AND STRATEGIC IMPLEMENTATION">ESG AWARENESS AND STRATEGIC IMPLEMENTATION</option>
                                                <option value="ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE">ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE</option>
                                                <option value="GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING">GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING</option>
                                                <option value="GREEN BOOK">GREEN BOOK</option>
                                                <option value="OTHERS">OTHERS</option>
                        </select>
                        <br>
                        <input type="text" name="otres${countesg}" id="otres${countesg}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskes${countesg}" id="targetskes${countesg}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskes${countesg}" id="currentskes${countesg}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapes${countesg}" id="gapes${countesg}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypees${countesg}" id="trtypees${countesg}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetres${countesg}" id="datetres${countesg}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_esg btn btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                `);
    } else if ($('#taskes' + (countesg - 1)).val() != '' && $('#traininges' + (countesg - 1)).val() != '' &&
        $('#trtypees' + (countesg - 1)).val() != null) {
        $("#tnaesglist").append(`
                <tr>
                    <td style="text-align:center;">${countesg}</td>
                    <td>
                        <textarea class="form-control" name="taskes${countesg}" id="taskes${countesg}" rows="2" placeholder="Insert your problem statement" autocomplete="off" required></textarea>
                    </td>
                    <td>
                        <select name="traininges${countesg}" id="traininges${countesg}" class="form-control" required>
                            <option selected disabled value="">-- Select Training --</option>
                                                <option value="CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE">CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE</option>
                                                <option value="EU BATTERY REGULATION AWARENESS">EU BATTERY REGULATION AWARENESS</option>
                                                <option value="ESG REPORTING STANDARDS (BURSA, SC)">ESG REPORTING STANDARDS (BURSA, SC)</option>
                                                <option value="LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE">LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE</option>
                                                <option value="WASTE REDUCTION & CIRCULAR ECONOMY">WASTE REDUCTION & CIRCULAR ECONOMY</option>
                                                <option value="ESG AWARENESS AND STRATEGIC IMPLEMENTATION">ESG AWARENESS AND STRATEGIC IMPLEMENTATION</option>
                                                <option value="ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE">ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE</option>
                                                <option value="GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING">GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING</option>
                                                <option value="GREEN BOOK">GREEN BOOK</option>
                                                <option value="OTHERS">OTHERS</option>
                        </select>
                        <br>
                        <input type="text" name="otres${countesg}" id="otres${countesg}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskes${countesg}" id="targetskes${countesg}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskes${countesg}" id="currentskes${countesg}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapes${countesg}" id="gapes${countesg}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypees${countesg}" id="trtypees${countesg}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetres${countesg}" id="datetres${countesg}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_esg btn btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                `);
    } else if (countesg > 3) {
        alert('You can add up to 3 task only');
        countesg--;
    } else {
        alert('Please complete previous task details!');
        countesg--;
    }

    $('#gapes' + countesg).val(0);
    $('#otres' + countesg).hide();

    $('#traininges' + countesg).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otres' + countesg).show();
        } else {
            $('#otres' + countesg).hide();
        }
    });

    $('#targetskes' + countesg).on('change', function() {
        var targetes = parseInt($('#targetskes' + countesg).val());
        var currentes = parseInt($('#currentskes' + countesg).val());

        var gapes = targetes - currentes;
        $('#gapes' + countesg).val(gapes);
    });

    $('#currentskes' + countesg).on('change', function() {
        var targetes = parseInt($('#targetskes' + countesg).val());
        var currentes = parseInt($('#currentskes' + countesg).val());

        var gapes = targetes - currentes;
        $('#gapes' + countesg).val(gapes);
    });

    $('#esgaware').val(countesg);
});

$(document).on('click', '.remove_esg', function() {
    countesg--;
    $(this).closest('tr').remove();
    return false;
});

$('#esgaware').val(countesg);


$('#add_self').click(function() {
    countself++;
    if (countself == 1) {
        $("#tnaselflist").append(`
                <tr>
                    <td style="text-align:center;">${countself}</td>
                    <td>
                        <textarea class="form-control" name="taskse${countself}" id="taskse${countself}" rows="2" placeholder="Insert your problem statement" autocomplete="off" required></textarea>
                    </td>
                    <td>
                        <select name="trainingse${countself}" id="trainingse${countself}" class="form-control" required>
                        <option selected disabled value="">-- Select Training --</option>
                            <option value="TRAIN THE TRAINER (TTT)">TRAIN THE TRAINER (TTT)</option>
                            <option value="EFFECTIVE BUSINESS COMMUNICATION">EFFECTIVE BUSINESS COMMUNICATION</option>
                            <option value="ENGLISH FOR PROFESSIONAL PURPOSES">ENGLISH FOR PROFESSIONAL PURPOSES</option>
                            <option value="INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING">INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING</option>
                            <option value="PRESENTATION AND PUBLIC SPEAKING SKILLS">PRESENTATION AND PUBLIC SPEAKING SKILLS</option>
                            <option value="EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS">EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS</option>
                            <option value="CONFLICT RESOLUTION AND NEGOTIATION SKILLS">CONFLICT RESOLUTION AND NEGOTIATION SKILLS</option>
                            <option value="TIME MANAGEMENT AND PRODUCTIVITY SKILLS">TIME MANAGEMENT AND PRODUCTIVITY SKILLS</option>
                            <option value="CROSS-CULTURAL COMMUNICATION">CROSS-CULTURAL COMMUNICATION</option>
                            <option value="CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE">CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE</option>
                            <option value="CREATIVE THINKING AND PROBLEM-SOLVING">CREATIVE THINKING AND PROBLEM-SOLVING</option>
                            <option value="MICROSOFT TEAMS AND COLLABORATION TOOLS">MICROSOFT TEAMS AND COLLABORATION TOOLS</option>
                            <option value="TIME MANAGEMENT USING MICROSOFT 365 TOOLS">TIME MANAGEMENT USING MICROSOFT 365 TOOLS</option>
                            <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                            <option value="ANTI BRRIBERY & ANTI CORRUPTION">ANTI BRRIBERY & ANTI CORRUPTION</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                        <br>
                        <input type="text" name="otrse${countself}" id="otrse${countself}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskse${countself}" id="targetskse${countself}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskse${countself}" id="currentskse${countself}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapse${countself}" id="gapse${countself}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypese${countself}" id="trtypese${countself}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetrse${countself}" id="datetrse${countself}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_self btn btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                `);
    } else if ($('#taskse' + (countself - 1)).val() != '' && $('#trainingse' + (countself - 1)).val() != '' &&
        $('#trtypese' + (countself - 1)).val() != null) {
        $("#tnaselflist").append(`
                <tr>
                    <td style="text-align:center;">${countself}</td>
                    <td>
                        <textarea class="form-control" name="taskse${countself}" id="taskse${countself}" rows="2" placeholder="Insert your problem statement" autocomplete="off" required></textarea>
                    </td>
                    <td>
                        <select name="trainingse${countself}" id="trainingse${countself}" class="form-control" required>
                        <option selected disabled value="">-- Select Training --</option>
                            <option value="TRAIN THE TRAINER (TTT)">TRAIN THE TRAINER (TTT)</option>
                            <option value="EFFECTIVE BUSINESS COMMUNICATION">EFFECTIVE BUSINESS COMMUNICATION</option>
                            <option value="ENGLISH FOR PROFESSIONAL PURPOSES">ENGLISH FOR PROFESSIONAL PURPOSES</option>
                            <option value="INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING">INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING</option>
                            <option value="PRESENTATION AND PUBLIC SPEAKING SKILLS">PRESENTATION AND PUBLIC SPEAKING SKILLS</option>
                            <option value="EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS">EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS</option>
                            <option value="CONFLICT RESOLUTION AND NEGOTIATION SKILLS">CONFLICT RESOLUTION AND NEGOTIATION SKILLS</option>
                            <option value="TIME MANAGEMENT AND PRODUCTIVITY SKILLS">TIME MANAGEMENT AND PRODUCTIVITY SKILLS</option>
                            <option value="CROSS-CULTURAL COMMUNICATION">CROSS-CULTURAL COMMUNICATION</option>
                            <option value="CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE">CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE</option>
                            <option value="CREATIVE THINKING AND PROBLEM-SOLVING">CREATIVE THINKING AND PROBLEM-SOLVING</option>
                            <option value="MICROSOFT TEAMS AND COLLABORATION TOOLS">MICROSOFT TEAMS AND COLLABORATION TOOLS</option>
                            <option value="TIME MANAGEMENT USING MICROSOFT 365 TOOLS">TIME MANAGEMENT USING MICROSOFT 365 TOOLS</option>
                            <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                            <option value="ANTI BRRIBERY & ANTI CORRUPTION">ANTI BRRIBERY & ANTI CORRUPTION</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                        <br>
                        <input type="text" name="otrse${countself}" id="otrse${countself}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskse${countself}" id="targetskse${countself}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskse${countself}" id="currentskse${countself}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapse${countself}" id="gapse${countself}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypese${countself}" id="trtypese${countself}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetrse${countself}" id="datetrse${countself}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_self btn btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                `);
    } else if (countself > 3) {
        alert('You can add up to 3 task only');
        countself--;
    } else {
        alert('Please complete previous task details!');
        countself--;
    }

    $('#gapse' + countself).val(0);
    $('#otrse' + countself).hide();

    $('#trainingse' + countself).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrse' + countself).show();
        } else {
            $('#otrse' + countself).hide();
        }
    });

    $('#targetskse' + countself).on('change', function() {
        var targetse = parseInt($('#targetskse' + countself).val());
        var currentse = parseInt($('#currentskse' + countself).val());

        var gapse = targetse - currentse;
        $('#gapse' + countself).val(gapse);
    });

    $('#currentskse' + countself).on('change', function() {
        var targetse = parseInt($('#targetskse' + countself).val());
        var currentse = parseInt($('#currentskse' + countself).val());

        var gapse = targetse - currentse;
        $('#gapse' + countself).val(gapse);
    });

    $('#selfaware').val(countself);
});

$(document).on('click', '.remove_self', function() {
    countself--;
    $(this).closest('tr').remove();
    return false;
});

$('#selfaware').val(countself);


$('#add_lead').click(function() {
    countlead++;
    if (countlead == 1) {
        $("#tnaleadlist").append(`
                    <tr>
                        <td style="text-align:center;">${countlead}</td>
                        <td>
                            <textarea class="form-control" name="taskle${countlead}" id="taskle${countlead}" rows="2" placeholder="Insert Major Task" required></textarea>
                        </td>
                        <td>
                            <select name="trainingle${countlead}" id="trainingle${countlead}" class="form-control" required>
                            <option selected disabled>-- Select Training --</option>
                                <option value="ETHICAL LEADERSHIP & GOVERNANCE">ETHICAL LEADERSHIP & GOVERNANCE</option>
                                <option value="RESILIENCE & STRESS MANAGEMENT">RESILIENCE & STRESS MANAGEMENT</option>
                                <option value="INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS">INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS</option>
                                <option value="HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS">HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS</option>
                                <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                                <option value="LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING">LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING</option>
                                <option value="STRATEGIC LEADERSHIP AND DECISION-MAKING">STRATEGIC LEADERSHIP AND DECISION-MAKING</option>
                                <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                <option value="CUSTOMER-CENTRIC LEADERSHIP">CUSTOMER-CENTRIC LEADERSHIP</option>
                                <option value="STRATEGIC SUPPLY CHAIN LEADERSHIP">STRATEGIC SUPPLY CHAIN LEADERSHIP</option>
                                <option value="CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE">CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                            <br>
                            <input type="text" name="otrle${countlead}" id="otrle${countlead}" class="form-control" placeholder="Others Training" autocomplete="off" />
                        </td>
                        <td>
                            <select name="targetskle${countlead}" id="targetskle${countlead}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td>
                            <select name="currentskle${countlead}" id="currentskle${countlead}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <input type="text" name="gaple${countlead}" id="gaple${countlead}" class="form-control" readonly />
                        </td>
                        <td>
                            <select name="trtypele${countlead}" id="trtypele${countlead}" class="form-control" required>
                                <option selected disabled value="">-- Select Training Type --</option>
                                <option value="1">1 - On Job Training</option>
                                <option value="2">2 - Coaching</option>
                                <option value="3">3 - External / In-house</option>
                            </select>
                        </td>
                        <td>
                            <select name="datetrle${countlead}" id="datetrle${countlead}" class="form-control" required>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="Jun">Jun</option>
                                <option value="Jul">Jul</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </td>
                        <td>
                            <a href="#" class="remove_lead btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                `);
    } else if ($('#taskle' + (countlead - 1)).val() != '' && $('#trainingle' + (countlead - 1)).val() != '' &&
        $('#trtypele' + (countlead - 1)).val() != null) {
        $("#tnaleadlist").append(`
                    <tr>
                        <td style="text-align:center;">${countlead}</td>
                        <td>
                            <textarea class="form-control" name="taskle${countlead}" id="taskle${countlead}" rows="2" placeholder="Insert Major Task" required></textarea>
                        </td>
                        <td>
                            <select name="trainingle${countlead}" id="trainingle${countlead}" class="form-control" required>
                            <option selected disabled>-- Select Training --</option>
                                <option value="ETHICAL LEADERSHIP & GOVERNANCE">ETHICAL LEADERSHIP & GOVERNANCE</option>
                                <option value="RESILIENCE & STRESS MANAGEMENT">RESILIENCE & STRESS MANAGEMENT</option>
                                <option value="INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS">INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS</option>
                                <option value="HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS">HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS</option>
                                <option value="GREEN TECHNOLOGY AND ENERGY EFFICIENCY">GREEN TECHNOLOGY AND ENERGY EFFICIENCY</option>
                                <option value="LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING">LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING</option>
                                <option value="STRATEGIC LEADERSHIP AND DECISION-MAKING">STRATEGIC LEADERSHIP AND DECISION-MAKING</option>
                                <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                <option value="CUSTOMER-CENTRIC LEADERSHIP">CUSTOMER-CENTRIC LEADERSHIP</option>
                                <option value="STRATEGIC SUPPLY CHAIN LEADERSHIP">STRATEGIC SUPPLY CHAIN LEADERSHIP</option>
                                <option value="CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE">CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                            <br>
                            <input type="text" name="otrle${countlead}" id="otrle${countlead}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                        </td>
                        <td>
                            <select name="targetskle${countlead}" id="targetskle${countlead}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td>
                            <select name="currentskle${countlead}" id="currentskle${countlead}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <input type="text" name="gaple${countlead}" id="gaple${countlead}" class="form-control" readonly />
                        </td>
                        <td>
                            <select name="trtypele${countlead}" id="trtypele${countlead}" class="form-control" required>
                                <option selected disabled value="">-- Select Training Type --</option>
                                <option value="1">1 - On Job Training</option>
                                <option value="2">2 - Coaching</option>
                                <option value="3">3 - External / In-house</option>
                            </select>
                        </td>
                        <td>
                            <select name="datetrle${countlead}" id="datetrle${countlead}" class="form-control" required>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="Jun">Jun</option>
                                <option value="Jul">Jul</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </td>
                        <td>
                            <a href="#" class="remove_lead btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                `);
    } else if (countlead > 3) {
        alert('You can add up to 3 task only');
        countlead--;
    } else {
        alert('Please complete previous task details!');
        countlead--;
    }

    $('#gaple' + countlead).val(0);
    $('#otrle' + countlead).hide();

    $('#trainingle' + countlead).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrle' + countlead).show();
        } else {
            $('#otrle' + countlead).hide();
        }
    });

    $('#targetskle' + countlead).on('change', function() {
        var targetle = parseInt($('#targetskle' + countlead).val());
        var currentle = parseInt($('#currentskle' + countlead).val());

        var gaple = targetle - currentle;
        $('#gaple' + countlead).val(gaple);
    });

    $('#currentskle' + countlead).on('change', function() {
        var targetle = parseInt($('#targetskle' + countlead).val());
        var currentle = parseInt($('#currentskle' + countlead).val());

        var gaple = targetle - currentle;
        $('#gaple' + countlead).val(gaple);
    });

    $('#leadaware').val(countlead);
});

$(document).on('click', '.remove_lead', function() {
    countself--;
    $(this).closest('tr').remove();
    return false;
});

$('#leadaware').val(countlead);

$('#add_driven').click(function() {
    countdriven++;
    if (countdriven == 1) {
        $("#tnadrivenlist").append(`
                    <tr>
                        <td style="text-align:center;">${countdriven}</td>
                        <td>
                            <textarea class="form-control" name="taskda${countdriven}" id="taskda${countdriven}" rows="2" placeholder="Insert Major Task" required></textarea>
                        </td>
                        <td>
                            <select name="trainingda${countdriven}" id="trainingda${countdriven}" class="form-control" required>
                                                <option selected disabled>-- Select Training --</option>
                                                <option value="DATA PRIVACY & PDPA 2010 IN MANUFACTURING">DATA PRIVACY & PDPA 2010 IN MANUFACTURING</option>
                                                <option value="AI & ROBOTICS IN AUTOMOTIVE">AI & ROBOTICS IN AUTOMOTIVE</option>
                                                <option value="EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)">EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)</option>
                                                <option value="MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION">MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION</option>
                                                <option value="MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS">MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS</option>
                                                <option value="MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION">MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION</option>
                                                <option value="MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION">MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION</option>
                                                <option value="OTHERS">OTHERS</option>
                            </select>
                            <br>
                            <input type="text" name="otrda${countdriven}" id="otrda${countdriven}" class="form-control" placeholder="Others Training" autocomplete="off" />
                        </td>
                        <td>
                            <select name="targetskda${countdriven}" id="targetskda${countdriven}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td>
                            <select name="currentskda${countdriven}" id="currentskda${countdriven}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <input type="text" name="gapda${countdriven}" id="gapda${countdriven}" class="form-control" readonly />
                        </td>
                        <td>
                            <select name="trtypeda${countdriven}" id="trtypeda${countdriven}" class="form-control" required>
                                <option selected disabled value="">-- Select Training Type --</option>
                                <option value="1">1 - On Job Training</option>
                                <option value="2">2 - Coaching</option>
                                <option value="3">3 - External / In-house</option>
                            </select>
                        </td>
                        <td>
                            <select name="datetrda${countdriven}" id="datetrda${countdriven}" class="form-control" required>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="Jun">Jun</option>
                                <option value="Jul">Jul</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </td>
                        <td>
                            <a href="#" class="remove_driven btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                `);
    } else if ($('#taskda' + (countdriven - 1)).val() != '' && $('#trainingda' + (countdriven - 1)).val() !=
        '' &&
        $('#trtypeda' + (countdriven - 1)).val() != null) {
        $("#tnadrivenlist").append(`
                    <tr>
                        <td style="text-align:center;">${countdriven}</td>
                        <td>
                            <textarea class="form-control" name="taskda${countdriven}" id="taskda${countdriven}" rows="2" placeholder="Insert Major Task" required></textarea>
                        </td>
                        <td>
                            <select name="trainingda${countdriven}" id="trainingda${countdriven}" class="form-control" required>
                                                <option selected disabled>-- Select Training --</option>
                                                <option value="DATA PRIVACY & PDPA 2010 IN MANUFACTURING">DATA PRIVACY & PDPA 2010 IN MANUFACTURING</option>
                                                <option value="AI & ROBOTICS IN AUTOMOTIVE">AI & ROBOTICS IN AUTOMOTIVE</option>
                                                <option value="EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)">EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)</option>
                                                <option value="MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION">MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION</option>
                                                <option value="MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS">MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS</option>
                                                <option value="MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION">MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION</option>
                                                <option value="MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION">MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION</option>
                                                <option value="OTHERS">OTHERS</option>
                            </select>
                            <br>
                            <input type="text" name="otrda${countdriven}" id="otrda${countdriven}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                        </td>
                        <td>
                            <select name="targetskda${countdriven}" id="targetskda${countdriven}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td>
                            <select name="currentskda${countdriven}" id="currentskda${countdriven}" class="form-control" required>
                                <option value="1">1 - Fundamental Awareness</option>
                                <option value="2">2 - Novice</option>
                                <option value="3">3 - Intermediate</option>
                                <option value="4">4 - Proficient</option>
                                <option value="5">5 - Expert</option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <input type="text" name="gapda${countdriven}" id="gapda${countdriven}" class="form-control" readonly />
                        </td>
                        <td>
                            <select name="trtypeda${countdriven}" id="trtypeda${countdriven}" class="form-control" required>
                                <option selected disabled value="">-- Select Training Type --</option>
                                <option value="1">1 - On Job Training</option>
                                <option value="2">2 - Coaching</option>
                                <option value="3">3 - External / In-house</option>
                            </select>
                        </td>
                        <td>
                            <select name="datetrda${countdriven}" id="datetrda${countdriven}" class="form-control" required>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="Jun">Jun</option>
                                <option value="Jul">Jul</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </td>
                        <td>
                            <a href="#" class="remove_driven btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                `);
    } else if (countdriven > 3) {
        alert('You can add up to 3 task only');
        countdriven--;
    } else {
        alert('Please complete previous task details!');
        countdriven--;
    }

    $('#gapda' + countdriven).val(0);
    $('#otrda' + countdriven).hide();

    $('#trainingda' + countdriven).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrda' + countdriven).show();
        } else {
            $('#otrda' + countdriven).hide();
        }
    });

    $('#targetskda' + countdriven).on('change', function() {
        var targetda = parseInt($('#targetskda' + countdriven).val());
        var currentda = parseInt($('#currentskda' + countdriven).val());

        var gapda = targetda - currentda;
        $('#gapda' + countdriven).val(gapda);
    });

    $('#currentskda' + countdriven).on('change', function() {
        var targetda = parseInt($('#targetskda' + countdriven).val());
        var currentda = parseInt($('#currentskda' + countdriven).val());

        var gapda = targetda - currentda;
        $('#gapda' + countdriven).val(gapda);
    });

    $('#dataaware').val(countdriven);
});

$(document).on('click', '.remove_driven', function() {
    countdriven--;
    $(this).closest('tr').remove();
    return false;
});

$('#dataaware').val(countdriven);

$('#add_func').click(function() {
    countfunc++;
    if (countfunc == 1) {
        $("#tnafunclist").append(`
                <tr>
                    <td style="text-align:center;">${countfunc}</td>
                    <td>
                        <textarea class="form-control" name="taskfu${countfunc}" id="taskfu${countfunc}" rows="2" placeholder="Insert Major Task" required></textarea>
                    </td>
                    <td>
                        <select name="trainingfu${countfunc}" id="trainingfu${countfunc}" class="form-control" required>
                            <option selected disabled value="">-- Select Training --</option>
                                                    <optgroup label="IT, TECHNICAL & MAINTENANCE">
                                                        <option value="PREDICTIVE MAINTENANCE USING IOT">PREDICTIVE MAINTENANCE USING IOT</option>
                                                        <option value="EV COMPONENT SAFETY STANDARDS">EV COMPONENT SAFETY STANDARDS</option>
                                                        <option value="COST REDUCTION TECHNIQUES FOR MAINTENANCE">COST REDUCTION TECHNIQUES FOR MAINTENANCE</option>
                                                        <option value="IP & PATENT">IP & PATENT</option>
                                                        <option value="KARAKURI">KARAKURI</option>
                                                        <option value="CATIA">CATIA</option>
                                                        <option value="CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION">CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION</option>
                                                        <option value="MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING">MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING</option>
                                                        <option value="CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING">CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING</option>
                                                        <option value="ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE">ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE</option>
                                                        <option value="EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE">EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE</option>
                                                        <option value="PLC PROGRAMMING AND AUTOMATION CONTROL">PLC PROGRAMMING AND AUTOMATION CONTROL</option>
                                                        <option value="IT INFRASTRUCTURE AND NETWORK MANAGEMENT">IT INFRASTRUCTURE AND NETWORK MANAGEMENT</option>
                                                        <option value="CYBERSECURITY AND DATA PROTECTION">CYBERSECURITY AND DATA PROTECTION</option>
                                                        <option value="TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES">TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES</option>
                                                        <option value="INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS">INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS</option>
                                                        <option value="GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS">GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS</option>
                                                        <option value="MACHINE LEARNING FOR PROCESS OPTIMISATION">MACHINE LEARNING FOR PROCESS OPTIMISATION</option>
                                                        <option value="INDUSTRIAL ROBOT SAFETY AND COMPLIANCE">INDUSTRIAL ROBOT SAFETY AND COMPLIANCE</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="MANUFACTURING / OPERATION">
                                                        <option value="MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)">MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)</option>
                                                        <option value="ERGONOMICS & MANUAL HANDLING IN PRODUCTION">ERGONOMICS & MANUAL HANDLING IN PRODUCTION</option>
                                                        <option value="STAMPING PRESS OPERATION & MAINTENANCE">STAMPING PRESS OPERATION & MAINTENANCE</option>
                                                        <option value="DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING">DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING</option>
                                                        <option value="ROLL FORMING TECHNOLOGY & DEFECT PREVENTION">ROLL FORMING TECHNOLOGY & DEFECT PREVENTION</option>
                                                        <option value="WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS">WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS</option>
                                                        <option value="ROBOTIC WELDING & AUTOMATION IN ASSEMBLY">ROBOTIC WELDING & AUTOMATION IN ASSEMBLY</option>
                                                        <option value="STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES">STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES</option>
                                                        <option value="ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION">ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION</option>
                                                        <option value="FUNDAMENTALS OF ELECTRODEPOSITION">FUNDAMENTALS OF ELECTRODEPOSITION</option>
                                                        <option value="MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS">MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS</option>
                                                        <option value="ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS">ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS</option>
                                                        <option value="5S TRAINING">5S TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="QUALITY SYSTEMS & PRODUCTIVITY IMPROVEMENT">
                                                        <option value="ISO 50001:2018 ENERGY MANAGEMENT SYSTEM">ISO 50001:2018 ENERGY MANAGEMENT SYSTEM</option>
                                                        <option value="IATF 16949:2024 TRANSITION TRAINING">IATF 16949:2024 TRANSITION TRAINING</option>
                                                        <option value="ISO 37301 COMPLIANCE MANAGEMENT">ISO 37301 COMPLIANCE MANAGEMENT</option>
                                                        <option value="INTERNAL AUDIT AND CONTROLS">INTERNAL AUDIT AND CONTROLS</option>
                                                        <option value="BUSINESS CONTINUITY AND CRISIS MANAGEMENT">BUSINESS CONTINUITY AND CRISIS MANAGEMENT</option>
                                                        <option value="RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES">RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES</option>
                                                        <option value="KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT">KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT</option>
                                                        <option value="LEAN PRODUCTION SYSTEM TRAINING">LEAN PRODUCTION SYSTEM TRAINING</option>
                                                        <option value="POKA YOKE - WHAT YOU NEED TO KNOW">POKA YOKE - WHAT YOU NEED TO KNOW</option>
                                                        <option value="SIX SIGMA TOOLS FOR IMPROVEMENT">SIX SIGMA TOOLS FOR IMPROVEMENT</option>
                                                        <option value="8D PROBLEM SOLVING">8D PROBLEM SOLVING</option>
                                                        <option value="TESTING & LABORATORY MANAGEMENT">TESTING & LABORATORY MANAGEMENT</option>
                                                        <option value="STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT">STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT</option>
                                                        <option value="PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE">PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE</option>
                                                        <option value="SUPPLIER QUALITY MANAGEMENT AND AUDIT">SUPPLIER QUALITY MANAGEMENT AND AUDIT</option>
                                                        <option value="THE 7 NEW QC MANAGEMENT TOOLS">THE 7 NEW QC MANAGEMENT TOOLS</option>
                                                        <option value="ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING">ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING</option>
                                                        <option value="ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING">ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING</option>
                                                        <option value="LEAD AUDITOR IATF">LEAD AUDITOR IATF</option>
                                                        <option value="ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING">ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="INDUSTRIAL SAFETY">
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS">OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS</option>
                                                        <option value="INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES">INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES</option>
                                                        <option value="HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)">HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)</option>
                                                        <option value="FIRE SAFETY AND EMERGENCY RESPONSE">FIRE SAFETY AND EMERGENCY RESPONSE</option>
                                                        <option value="ERGONOMICS AND MANUAL HANDLING SAFETY">ERGONOMICS AND MANUAL HANDLING SAFETY</option>
                                                        <option value="HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE">HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE</option>
                                                        <option value="CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING">CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING</option>
                                                        <option value="SAFETY LEADERSHIP AND CULTURE BUILDING">SAFETY LEADERSHIP AND CULTURE BUILDING</option>
                                                        <option value="INCIDENT INVESTIGATION AND REPORTING">INCIDENT INVESTIGATION AND REPORTING</option>
                                                        <option value="EV CHARGER AND ELECTRICAL SAFETY">EV CHARGER AND ELECTRICAL SAFETY</option>
                                                        <option value="OVERHEAD CRANE">OVERHEAD CRANE</option>
                                                        <option value="FORKLIFT">FORKLIFT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="WORKING AT HEIGHT">WORKING AT HEIGHT</option>
                                                        <option value="SAFETY AWARENESS">SAFETY AWARENESS</option>
                                                        <option value="SCHEDULED WASTE MANAGEMENT">SCHEDULED WASTE MANAGEMENT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="HUMAN CAPITAL">
                                                        <option value="SUCCESSION PLANNING">SUCCESSION PLANNING</option>
                                                        <option value="ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)">ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)</option>
                                                        <option value="HRD CORP 2026 CLAIMABLE TRAINING RULES">HRD CORP 2026 CLAIMABLE TRAINING RULES</option>
                                                        <option value="TALENT ACQUISITION AND RECRUITMENT STRATEGIES">TALENT ACQUISITION AND RECRUITMENT STRATEGIES</option>
                                                        <option value="PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING">PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING</option>
                                                        <option value="EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES">EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES</option>
                                                        <option value="LEARNING & DEVELOPMENT PLANNING">LEARNING & DEVELOPMENT PLANNING</option>
                                                        <option value="COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT">COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT</option>
                                                        <option value="LABOUR LAW AND EMPLOYMENT COMPLIANCE">LABOUR LAW AND EMPLOYMENT COMPLIANCE</option>
                                                        <option value="HR ANALYTICS AND PEOPLE DATA MANAGEMENT">HR ANALYTICS AND PEOPLE DATA MANAGEMENT</option>
                                                        <option value="COACHING AND MENTORING SKILLS FOR MANAGERS">COACHING AND MENTORING SKILLS FOR MANAGERS</option>
                                                        <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                                        <option value="DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING">DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING</option>
                                                        <option value="EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS">EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LEGAL & GOVERNANCE">
                                                        <option value="CONTRACT AND LEGAL COMPLIANCE MANAGEMENT">CONTRACT AND LEGAL COMPLIANCE MANAGEMENT</option>
                                                        <option value="CORPORATE AND COMMERCIAL LAW AWARENESS">CORPORATE AND COMMERCIAL LAW AWARENESS</option>
                                                        <option value="REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY">REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY</option>
                                                        <option value="DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT">DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT</option>
                                                        <option value="INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT">INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="SALES, MARKETING & CUSTOMER SERVICE">
                                                        <option value="B2B SALES STRATEGIES FOR MANUFACTURING">B2B SALES STRATEGIES FOR MANUFACTURING</option>
                                                        <option value="INDUSTRIAL MARKETING AND BRAND POSITIONING">INDUSTRIAL MARKETING AND BRAND POSITIONING</option>
                                                        <option value="CUSTOMER RELATIONSHIP MANAGEMENT (CRM)">CUSTOMER RELATIONSHIP MANAGEMENT (CRM)</option>
                                                        <option value="TECHNICAL PRODUCT PRESENTATION SKILLS">TECHNICAL PRODUCT PRESENTATION SKILLS</option>
                                                        <option value="NEGOTIATION AND CLOSING TECHNIQUES">NEGOTIATION AND CLOSING TECHNIQUES</option>
                                                        <option value="MARKET AND COMPETITOR ANALYSIS">MARKET AND COMPETITOR ANALYSIS</option>
                                                        <option value="SALES FORECASTING AND BUSINESS METRICS">SALES FORECASTING AND BUSINESS METRICS</option>
                                                        <option value="CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS">CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="BUSINESS & MANAGEMENT">
                                                        <option value="BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS">BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS</option>
                                                        <option value="AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS">AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS</option>
                                                        <option value="FINANCIAL LITERACY FOR NON-FINANCE MANAGERS">FINANCIAL LITERACY FOR NON-FINANCE MANAGERS</option>
                                                        <option value="STRATEGIC THINKING AND BUSINESS DECISION-MAKING">STRATEGIC THINKING AND BUSINESS DECISION-MAKING</option>
                                                        <option value="CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS">CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS</option>
                                                        <option value="PROJECT ROI AND BUSINESS IMPACT ANALYSIS">PROJECT ROI AND BUSINESS IMPACT ANALYSIS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="FINANCIAL MANAGEMENT">
                                                        <option value="ESG-LINKED FINANCE & GREEN TAX INCENTIVES">ESG-LINKED FINANCE & GREEN TAX INCENTIVES</option>
                                                        <option value="TRANSFER PRICING & TAXATION UPDATES 2026">TRANSFER PRICING & TAXATION UPDATES 2026</option>
                                                        <option value="STRATEGIC COST MANAGEMENT IN AUTOMOTIVE">STRATEGIC COST MANAGEMENT IN AUTOMOTIVE</option>
                                                        <option value="REGULATORY REPORTING AND AUDIT READINESS">REGULATORY REPORTING AND AUDIT READINESS</option>
                                                        <option value="E-INVOICING">E-INVOICING</option>
                                                        <option value="FINANCIAL PLANNING AND BUDGETING">FINANCIAL PLANNING AND BUDGETING</option>
                                                        <option value="FINANCIAL REGULATORY COMPLIANCE AND REPORTING">FINANCIAL REGULATORY COMPLIANCE AND REPORTING</option>
                                                        <option value="CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION">CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION</option>
                                                        <option value="RISK MANAGEMENT AND INTERNAL CONTROLS">RISK MANAGEMENT AND INTERNAL CONTROLS</option>
                                                        <option value="FINANCIAL ANALYSIS AND DECISION-MAKING">FINANCIAL ANALYSIS AND DECISION-MAKING</option>
                                                        <option value="TAXATION AND GST/SST COMPLIANCE">TAXATION AND GST/SST COMPLIANCE</option>
                                                        <option value="PROCUREMENT AND FINANCE COLLABORATION">PROCUREMENT AND FINANCE COLLABORATION</option>
                                                        <option value="FINANCIAL SYSTEMS AND ERP UTILISATION">FINANCIAL SYSTEMS AND ERP UTILISATION</option>
                                                        <option value="INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT">INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT</option>
                                                        <option value="AI INNOVATIONS FOR FINANCIAL PROFESSIONALS">AI INNOVATIONS FOR FINANCIAL PROFESSIONALS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LOGISTICS / SCM / WAREHOUSE / INVENTORY">
                                                        <option value="SUPPLIER ESG COMPLIANCE AUDITS">SUPPLIER ESG COMPLIANCE AUDITS</option>
                                                        <option value="DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS">DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS</option>
                                                        <option value="MITI IMPORT/EXPORT REGULATORY UPDATES">MITI IMPORT/EXPORT REGULATORY UPDATES</option>
                                                        <option value="SUPPLY CHAIN MANAGEMENT FUNDAMENTALS">SUPPLY CHAIN MANAGEMENT FUNDAMENTALS</option>
                                                        <option value="WAREHOUSE MANAGEMENT AND INVENTORY CONTROL">WAREHOUSE MANAGEMENT AND INVENTORY CONTROL</option>
                                                        <option value="LOGISTICS AND TRANSPORTATION MANAGEMENT">LOGISTICS AND TRANSPORTATION MANAGEMENT</option>
                                                        <option value="DEMAND FORECASTING AND INVENTORY OPTIMISATION">DEMAND FORECASTING AND INVENTORY OPTIMISATION</option>
                                                        <option value="SUSTAINABLE SUPPLY CHAIN PRACTICES">SUSTAINABLE SUPPLY CHAIN PRACTICES</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="PROCUREMENT & VENDOR DEVELOPMENT">
                                                        <option value="AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS">AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS</option>
                                                        <option value="STRATEGIC SOURCING AND SUPPLIER SELECTION">STRATEGIC SOURCING AND SUPPLIER SELECTION</option>
                                                        <option value="SUPPLIER PERFORMANCE MANAGEMENT (SPM)">SUPPLIER PERFORMANCE MANAGEMENT (SPM)</option>
                                                        <option value="CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT">CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT</option>
                                                        <option value="COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)">COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)</option>
                                                        <option value="VENDOR DEVELOPMENT AND COLLABORATION">VENDOR DEVELOPMENT AND COLLABORATION</option>
                                                        <option value="DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION">DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION</option>
                                                        <option value="SUSTAINABLE PROCUREMENT AND ESG PRACTICES">SUSTAINABLE PROCUREMENT AND ESG PRACTICES</option>
                                                        <option value="SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION">SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION</option>
                                                        <option value="ADVANCED PROCUREMENT">ADVANCED PROCUREMENT</option>
                                                        <option value="DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S">DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="GOVERNANCE RISK AND COMPLIANCE">
                                                        <option value="COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY">COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY</option>
                                                        <option value="ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE">ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE</option>
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="REGISTERED ELECTRICAL ENERGY MANAGER (REM)">REGISTERED ELECTRICAL ENERGY MANAGER (REM)</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>
                            <!-- Add more <optgroup> here as necessary -->
                        </select>
                        <br>
                        <input type="text" name="otrfu${countfunc}" id="otrfu${countfunc}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskfu${countfunc}" id="targetskfu${countfunc}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskfu${countfunc}" id="currentskfu${countfunc}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapfu${countfunc}" id="gapfu${countfunc}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypefu${countfunc}" id="trtypefu${countfunc}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetrfu${countfunc}" id="datetrfu${countfunc}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_func btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                </tr>
                `);
    } else if ($('#taskfu' + (countfunc - 1)).val() != '' && $('#trainingfu' + (countfunc - 1)).val() != '' &&
        $('#trtypefu' + (countfunc - 1)).val() != null) {
        $("#tnafunclist").append(`
                <tr>
                    <td style="text-align:center;">${countfunc}</td>
                    <td>
                        <textarea class="form-control" name="taskfu${countfunc}" id="taskfu${countfunc}" rows="2" placeholder="Insert Major Task" required></textarea>
                    </td>
                    <td>
                        <select name="trainingfu${countfunc}" id="trainingfu${countfunc}" class="form-control" required>
                            <option selected disabled value="">-- Select Training --</option>
                                                    <optgroup label="IT, TECHNICAL & MAINTENANCE">
                                                        <option value="PREDICTIVE MAINTENANCE USING IOT">PREDICTIVE MAINTENANCE USING IOT</option>
                                                        <option value="EV COMPONENT SAFETY STANDARDS">EV COMPONENT SAFETY STANDARDS</option>
                                                        <option value="COST REDUCTION TECHNIQUES FOR MAINTENANCE">COST REDUCTION TECHNIQUES FOR MAINTENANCE</option>
                                                        <option value="IP & PATENT">IP & PATENT</option>
                                                        <option value="KARAKURI">KARAKURI</option>
                                                        <option value="CATIA">CATIA</option>
                                                        <option value="CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION">CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION</option>
                                                        <option value="MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING">MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING</option>
                                                        <option value="CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING">CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING</option>
                                                        <option value="ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE">ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE</option>
                                                        <option value="EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE">EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE</option>
                                                        <option value="PLC PROGRAMMING AND AUTOMATION CONTROL">PLC PROGRAMMING AND AUTOMATION CONTROL</option>
                                                        <option value="IT INFRASTRUCTURE AND NETWORK MANAGEMENT">IT INFRASTRUCTURE AND NETWORK MANAGEMENT</option>
                                                        <option value="CYBERSECURITY AND DATA PROTECTION">CYBERSECURITY AND DATA PROTECTION</option>
                                                        <option value="TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES">TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES</option>
                                                        <option value="INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS">INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS</option>
                                                        <option value="GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS">GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS</option>
                                                        <option value="MACHINE LEARNING FOR PROCESS OPTIMISATION">MACHINE LEARNING FOR PROCESS OPTIMISATION</option>
                                                        <option value="INDUSTRIAL ROBOT SAFETY AND COMPLIANCE">INDUSTRIAL ROBOT SAFETY AND COMPLIANCE</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="MANUFACTURING / OPERATION">
                                                        <option value="MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)">MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)</option>
                                                        <option value="ERGONOMICS & MANUAL HANDLING IN PRODUCTION">ERGONOMICS & MANUAL HANDLING IN PRODUCTION</option>
                                                        <option value="STAMPING PRESS OPERATION & MAINTENANCE">STAMPING PRESS OPERATION & MAINTENANCE</option>
                                                        <option value="DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING">DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING</option>
                                                        <option value="ROLL FORMING TECHNOLOGY & DEFECT PREVENTION">ROLL FORMING TECHNOLOGY & DEFECT PREVENTION</option>
                                                        <option value="WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS">WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS</option>
                                                        <option value="ROBOTIC WELDING & AUTOMATION IN ASSEMBLY">ROBOTIC WELDING & AUTOMATION IN ASSEMBLY</option>
                                                        <option value="STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES">STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES</option>
                                                        <option value="ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION">ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION</option>
                                                        <option value="FUNDAMENTALS OF ELECTRODEPOSITION">FUNDAMENTALS OF ELECTRODEPOSITION</option>
                                                        <option value="MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS">MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS</option>
                                                        <option value="ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS">ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS</option>
                                                        <option value="5S TRAINING">5S TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="QUALITY SYSTEMS & PRODUCTIVITY IMPROVEMENT">
                                                        <option value="ISO 50001:2018 ENERGY MANAGEMENT SYSTEM">ISO 50001:2018 ENERGY MANAGEMENT SYSTEM</option>
                                                        <option value="IATF 16949:2024 TRANSITION TRAINING">IATF 16949:2024 TRANSITION TRAINING</option>
                                                        <option value="ISO 37301 COMPLIANCE MANAGEMENT">ISO 37301 COMPLIANCE MANAGEMENT</option>
                                                        <option value="INTERNAL AUDIT AND CONTROLS">INTERNAL AUDIT AND CONTROLS</option>
                                                        <option value="BUSINESS CONTINUITY AND CRISIS MANAGEMENT">BUSINESS CONTINUITY AND CRISIS MANAGEMENT</option>
                                                        <option value="RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES">RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES</option>
                                                        <option value="KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT">KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT</option>
                                                        <option value="LEAN PRODUCTION SYSTEM TRAINING">LEAN PRODUCTION SYSTEM TRAINING</option>
                                                        <option value="POKA YOKE - WHAT YOU NEED TO KNOW">POKA YOKE - WHAT YOU NEED TO KNOW</option>
                                                        <option value="SIX SIGMA TOOLS FOR IMPROVEMENT">SIX SIGMA TOOLS FOR IMPROVEMENT</option>
                                                        <option value="8D PROBLEM SOLVING">8D PROBLEM SOLVING</option>
                                                        <option value="TESTING & LABORATORY MANAGEMENT">TESTING & LABORATORY MANAGEMENT</option>
                                                        <option value="STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT">STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT</option>
                                                        <option value="PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE">PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE</option>
                                                        <option value="SUPPLIER QUALITY MANAGEMENT AND AUDIT">SUPPLIER QUALITY MANAGEMENT AND AUDIT</option>
                                                        <option value="THE 7 NEW QC MANAGEMENT TOOLS">THE 7 NEW QC MANAGEMENT TOOLS</option>
                                                        <option value="ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING">ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING</option>
                                                        <option value="ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING">ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING</option>
                                                        <option value="LEAD AUDITOR IATF">LEAD AUDITOR IATF</option>
                                                        <option value="ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING">ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="INDUSTRIAL SAFETY">
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS">OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS</option>
                                                        <option value="INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES">INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES</option>
                                                        <option value="HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)">HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)</option>
                                                        <option value="FIRE SAFETY AND EMERGENCY RESPONSE">FIRE SAFETY AND EMERGENCY RESPONSE</option>
                                                        <option value="ERGONOMICS AND MANUAL HANDLING SAFETY">ERGONOMICS AND MANUAL HANDLING SAFETY</option>
                                                        <option value="HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE">HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE</option>
                                                        <option value="CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING">CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING</option>
                                                        <option value="SAFETY LEADERSHIP AND CULTURE BUILDING">SAFETY LEADERSHIP AND CULTURE BUILDING</option>
                                                        <option value="INCIDENT INVESTIGATION AND REPORTING">INCIDENT INVESTIGATION AND REPORTING</option>
                                                        <option value="EV CHARGER AND ELECTRICAL SAFETY">EV CHARGER AND ELECTRICAL SAFETY</option>
                                                        <option value="OVERHEAD CRANE">OVERHEAD CRANE</option>
                                                        <option value="FORKLIFT">FORKLIFT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="WORKING AT HEIGHT">WORKING AT HEIGHT</option>
                                                        <option value="SAFETY AWARENESS">SAFETY AWARENESS</option>
                                                        <option value="SCHEDULED WASTE MANAGEMENT">SCHEDULED WASTE MANAGEMENT</option>
                                                        <option value="OSH COORDINATOR TRAINING">OSH COORDINATOR TRAINING</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="HUMAN CAPITAL">
                                                        <option value="SUCCESSION PLANNING">SUCCESSION PLANNING</option>
                                                        <option value="ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)">ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)</option>
                                                        <option value="HRD CORP 2026 CLAIMABLE TRAINING RULES">HRD CORP 2026 CLAIMABLE TRAINING RULES</option>
                                                        <option value="TALENT ACQUISITION AND RECRUITMENT STRATEGIES">TALENT ACQUISITION AND RECRUITMENT STRATEGIES</option>
                                                        <option value="PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING">PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING</option>
                                                        <option value="EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES">EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES</option>
                                                        <option value="LEARNING & DEVELOPMENT PLANNING">LEARNING & DEVELOPMENT PLANNING</option>
                                                        <option value="COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT">COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT</option>
                                                        <option value="LABOUR LAW AND EMPLOYMENT COMPLIANCE">LABOUR LAW AND EMPLOYMENT COMPLIANCE</option>
                                                        <option value="HR ANALYTICS AND PEOPLE DATA MANAGEMENT">HR ANALYTICS AND PEOPLE DATA MANAGEMENT</option>
                                                        <option value="COACHING AND MENTORING SKILLS FOR MANAGERS">COACHING AND MENTORING SKILLS FOR MANAGERS</option>
                                                        <option value="CHANGE MANAGEMENT AND CULTURE TRANSFORMATION">CHANGE MANAGEMENT AND CULTURE TRANSFORMATION</option>
                                                        <option value="DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING">DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING</option>
                                                        <option value="EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS">EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LEGAL & GOVERNANCE">
                                                        <option value="CONTRACT AND LEGAL COMPLIANCE MANAGEMENT">CONTRACT AND LEGAL COMPLIANCE MANAGEMENT</option>
                                                        <option value="CORPORATE AND COMMERCIAL LAW AWARENESS">CORPORATE AND COMMERCIAL LAW AWARENESS</option>
                                                        <option value="REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY">REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY</option>
                                                        <option value="DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT">DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT</option>
                                                        <option value="INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT">INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="SALES, MARKETING & CUSTOMER SERVICE">
                                                        <option value="B2B SALES STRATEGIES FOR MANUFACTURING">B2B SALES STRATEGIES FOR MANUFACTURING</option>
                                                        <option value="INDUSTRIAL MARKETING AND BRAND POSITIONING">INDUSTRIAL MARKETING AND BRAND POSITIONING</option>
                                                        <option value="CUSTOMER RELATIONSHIP MANAGEMENT (CRM)">CUSTOMER RELATIONSHIP MANAGEMENT (CRM)</option>
                                                        <option value="TECHNICAL PRODUCT PRESENTATION SKILLS">TECHNICAL PRODUCT PRESENTATION SKILLS</option>
                                                        <option value="NEGOTIATION AND CLOSING TECHNIQUES">NEGOTIATION AND CLOSING TECHNIQUES</option>
                                                        <option value="MARKET AND COMPETITOR ANALYSIS">MARKET AND COMPETITOR ANALYSIS</option>
                                                        <option value="SALES FORECASTING AND BUSINESS METRICS">SALES FORECASTING AND BUSINESS METRICS</option>
                                                        <option value="CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS">CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="BUSINESS & MANAGEMENT">
                                                        <option value="BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS">BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS</option>
                                                        <option value="AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS">AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS</option>
                                                        <option value="FINANCIAL LITERACY FOR NON-FINANCE MANAGERS">FINANCIAL LITERACY FOR NON-FINANCE MANAGERS</option>
                                                        <option value="STRATEGIC THINKING AND BUSINESS DECISION-MAKING">STRATEGIC THINKING AND BUSINESS DECISION-MAKING</option>
                                                        <option value="CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS">CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS</option>
                                                        <option value="PROJECT ROI AND BUSINESS IMPACT ANALYSIS">PROJECT ROI AND BUSINESS IMPACT ANALYSIS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="FINANCIAL MANAGEMENT">
                                                        <option value="ESG-LINKED FINANCE & GREEN TAX INCENTIVES">ESG-LINKED FINANCE & GREEN TAX INCENTIVES</option>
                                                        <option value="TRANSFER PRICING & TAXATION UPDATES 2026">TRANSFER PRICING & TAXATION UPDATES 2026</option>
                                                        <option value="STRATEGIC COST MANAGEMENT IN AUTOMOTIVE">STRATEGIC COST MANAGEMENT IN AUTOMOTIVE</option>
                                                        <option value="REGULATORY REPORTING AND AUDIT READINESS">REGULATORY REPORTING AND AUDIT READINESS</option>
                                                        <option value="E-INVOICING">E-INVOICING</option>
                                                        <option value="FINANCIAL PLANNING AND BUDGETING">FINANCIAL PLANNING AND BUDGETING</option>
                                                        <option value="FINANCIAL REGULATORY COMPLIANCE AND REPORTING">FINANCIAL REGULATORY COMPLIANCE AND REPORTING</option>
                                                        <option value="CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION">CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION</option>
                                                        <option value="RISK MANAGEMENT AND INTERNAL CONTROLS">RISK MANAGEMENT AND INTERNAL CONTROLS</option>
                                                        <option value="FINANCIAL ANALYSIS AND DECISION-MAKING">FINANCIAL ANALYSIS AND DECISION-MAKING</option>
                                                        <option value="TAXATION AND GST/SST COMPLIANCE">TAXATION AND GST/SST COMPLIANCE</option>
                                                        <option value="PROCUREMENT AND FINANCE COLLABORATION">PROCUREMENT AND FINANCE COLLABORATION</option>
                                                        <option value="FINANCIAL SYSTEMS AND ERP UTILISATION">FINANCIAL SYSTEMS AND ERP UTILISATION</option>
                                                        <option value="INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT">INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT</option>
                                                        <option value="AI INNOVATIONS FOR FINANCIAL PROFESSIONALS">AI INNOVATIONS FOR FINANCIAL PROFESSIONALS</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="LOGISTICS / SCM / WAREHOUSE / INVENTORY">
                                                        <option value="SUPPLIER ESG COMPLIANCE AUDITS">SUPPLIER ESG COMPLIANCE AUDITS</option>
                                                        <option value="DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS">DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS</option>
                                                        <option value="MITI IMPORT/EXPORT REGULATORY UPDATES">MITI IMPORT/EXPORT REGULATORY UPDATES</option>
                                                        <option value="SUPPLY CHAIN MANAGEMENT FUNDAMENTALS">SUPPLY CHAIN MANAGEMENT FUNDAMENTALS</option>
                                                        <option value="WAREHOUSE MANAGEMENT AND INVENTORY CONTROL">WAREHOUSE MANAGEMENT AND INVENTORY CONTROL</option>
                                                        <option value="LOGISTICS AND TRANSPORTATION MANAGEMENT">LOGISTICS AND TRANSPORTATION MANAGEMENT</option>
                                                        <option value="DEMAND FORECASTING AND INVENTORY OPTIMISATION">DEMAND FORECASTING AND INVENTORY OPTIMISATION</option>
                                                        <option value="SUSTAINABLE SUPPLY CHAIN PRACTICES">SUSTAINABLE SUPPLY CHAIN PRACTICES</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="PROCUREMENT & VENDOR DEVELOPMENT">
                                                        <option value="AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS">AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS</option>
                                                        <option value="STRATEGIC SOURCING AND SUPPLIER SELECTION">STRATEGIC SOURCING AND SUPPLIER SELECTION</option>
                                                        <option value="SUPPLIER PERFORMANCE MANAGEMENT (SPM)">SUPPLIER PERFORMANCE MANAGEMENT (SPM)</option>
                                                        <option value="CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT">CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT</option>
                                                        <option value="COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)">COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)</option>
                                                        <option value="VENDOR DEVELOPMENT AND COLLABORATION">VENDOR DEVELOPMENT AND COLLABORATION</option>
                                                        <option value="DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION">DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION</option>
                                                        <option value="SUSTAINABLE PROCUREMENT AND ESG PRACTICES">SUSTAINABLE PROCUREMENT AND ESG PRACTICES</option>
                                                        <option value="SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION">SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION</option>
                                                        <option value="ADVANCED PROCUREMENT">ADVANCED PROCUREMENT</option>
                                                        <option value="DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S">DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA'S</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>

                                                    <optgroup label="GOVERNANCE RISK AND COMPLIANCE">
                                                        <option value="COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY">COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY</option>
                                                        <option value="ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE">ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE</option>
                                                        <option value="HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE">HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE</option>
                                                        <option value="REGISTERED ELECTRICAL ENERGY MANAGER (REM)">REGISTERED ELECTRICAL ENERGY MANAGER (REM)</option>
                                                        <option value="OTHERS">OTHERS</option>
                                                    </optgroup>
                        </select>
                        <br>
                        <input type="text" name="otrfu${countfunc}" id="otrfu${countfunc}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                    </td>
                    <td>
                        <select name="targetskfu${countfunc}" id="targetskfu${countfunc}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td>
                        <select name="currentskfu${countfunc}" id="currentskfu${countfunc}" class="form-control" required>
                            <option value="1">1 - Fundamental Awareness</option>
                            <option value="2">2 - Novice</option>
                            <option value="3">3 - Intermediate</option>
                            <option value="4">4 - Proficient</option>
                            <option value="5">5 - Expert</option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="gapfu${countfunc}" id="gapfu${countfunc}" class="form-control" readonly />
                    </td>
                    <td>
                        <select name="trtypefu${countfunc}" id="trtypefu${countfunc}" class="form-control" required>
                            <option selected disabled value="">-- Select Training Type --</option>
                            <option value="1">1 - On Job Training</option>
                            <option value="2">2 - Coaching</option>
                            <option value="3">3 - External / In-house</option>
                        </select>
                    </td>
                    <td>
                        <select name="datetrfu${countfunc}" id="datetrfu${countfunc}" class="form-control" required>
                            <option value="Jan">Jan</option>
                            <option value="Feb">Feb</option>
                            <option value="Mar">Mar</option>
                            <option value="Apr">Apr</option>
                            <option value="May">May</option>
                            <option value="Jun">Jun</option>
                            <option value="Jul">Jul</option>
                            <option value="Aug">Aug</option>
                            <option value="Sep">Sep</option>
                            <option value="Oct">Oct</option>
                            <option value="Nov">Nov</option>
                            <option value="Dec">Dec</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" class="remove_func btn btn-danger">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            `);
    } else if (countfunc > 3) {
        alert('You can add up to 3 task only');
        countfunc--;
    } else {
        alert('Please complete previous task details!');
        countfunc--;
    }

    $('#gapfu' + countfunc).val(0);
    $('#otrfu' + countfunc).hide();

    $('#trainingfu' + countfunc).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrfu' + countfunc).show();
        } else {
            $('#otrfu' + countfunc).hide();
        }
    });

    $('#targetskfu' + countfunc).on('change', function() {
        var targetfu = parseInt($('#targetskfu' + countfunc).val());
        var currentfu = parseInt($('#currentskfu' + countfunc).val());

        var gapfu = targetfu - currentfu;
        $('#gapfu' + countfunc).val(gapfu);
    });

    $('#currentskfu' + countfunc).on('change', function() {
        var targetfu = parseInt($('#targetskfu' + countfunc).val());
        var currentfu = parseInt($('#currentskfu' + countfunc).val());

        var gapfu = targetfu - currentfu;
        $('#gapfu' + countfunc).val(gapfu);
    });

    $('#functional').val(countfunc);
});

$(document).on('click', '.remove_func', function() {
    countself--;
    $(this).closest('tr').remove();
    return false;
});

$('#functional').val(countfunc);

$('#add_busi').click(function() {
    countbusi++;
    if (countbusi == 1) {
        $("#tnabusilist").append(`
                        <tr>
                            <td style="text-align:center;">${countbusi}</td>
                            <td>
                                <textarea class="form-control" name="taskbu${countbusi}" id="taskbu${countbusi}" rows="2" placeholder="Insert Major Task" required></textarea>
                            </td>
                            <td>
                                <select name="trainingbu${countbusi}" id="trainingbu${countbusi}" class="form-control" required>
                                    <option selected disabled value="">-- Select Training --</option>
                                                            <optgroup label="DIGITAL TRANSFORMATION & INNOVATION">
                                                                <option value="INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES">INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES</option>
                                                                <option value="DIGITAL SUPPLY CHAIN OPTIMISATION">DIGITAL SUPPLY CHAIN OPTIMISATION</option>
                                                                <option value="DIGITAL TWINS IN MANUFACTURING">DIGITAL TWINS IN MANUFACTURING</option>
                                                                <option value="ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES">ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES</option>
                                                                <option value="ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING">ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING</option>
                                                                <option value="DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY">DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY</option>
                                                                <option value="INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION">INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION</option>
                                                                <option value="AGILE AND LEAN DIGITAL PROJECT MANAGEMENT">AGILE AND LEAN DIGITAL PROJECT MANAGEMENT</option>
                                                                <option value="PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING">PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING</option>
                                                                <option value="CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS">CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS</option>
                                                                <option value="THE MODERN WORKPLACE">THE MODERN WORKPLACE</option>
                                                                <option value="OTHERS">OTHERS</option>
                                                            </optgroup>
                                    </optgroup>
                                </select>
                                <br>
                                <input type="text" name="otrbu${countbusi}" id="otrbu${countbusi}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                            </td>
                            <td>
                                <select name="targetskbu${countbusi}" id="targetskbu${countbusi}" class="form-control" required>
                                    <option value="1">1 - Fundamental Awareness</option>
                                    <option value="2">2 - Novice</option>
                                    <option value="3">3 - Intermediate</option>
                                    <option value="4">4 - Proficient</option>
                                    <option value="5">5 - Expert</option>
                                </select>
                            </td>
                            <td>
                                <select name="currentskbu${countbusi}" id="currentskbu${countbusi}" class="form-control" required>
                                    <option value="1">1 - Fundamental Awareness</option>
                                    <option value="2">2 - Novice</option>
                                    <option value="3">3 - Intermediate</option>
                                    <option value="4">4 - Proficient</option>
                                    <option value="5">5 - Expert</option>
                                </select>
                            </td>
                            <td style="text-align:center;">
                                <input type="text" name="gapbu${countbusi}" id="gapbu${countbusi}" class="form-control" readonly />
                            </td>
                            <td>
                                <select name="trtypebu${countbusi}" id="trtypebu${countbusi}" class="form-control" required>
                                    <option selected disabled value="">-- Select Training Type --</option>
                                    <option value="1">1 - On Job Training</option>
                                    <option value="2">2 - Coaching</option>
                                    <option value="3">3 - External / In-house</option>
                                </select>
                            </td>
                            <td>
                                <select name="datetrbu${countbusi}" id="datetrbu${countbusi}" class="form-control" required>
                                    <option value="Jan">Jan</option>
                                    <option value="Feb">Feb</option>
                                    <option value="Mar">Mar</option>
                                    <option value="Apr">Apr</option>
                                    <option value="May">May</option>
                                    <option value="Jun">Jun</option>
                                    <option value="Jul">Jul</option>
                                    <option value="Aug">Aug</option>
                                    <option value="Sep">Sep</option>
                                    <option value="Oct">Oct</option>
                                    <option value="Nov">Nov</option>
                                    <option value="Dec">Dec</option>
                                </select>
                            </td>
                            <td>
                                <a href="#" class="remove_busi btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        `);
    } else if ($('#taskbu' + (countbusi - 1)).val() != '' && $('#trainingbu' + (countbusi - 1)).val() != '' &&
        $('#trtypebu' + (countbusi - 1)).val() != null) {
        $("#tnabusilist").append(`
                                <tr>
                                    <td style="text-align:center;">${countbusi}</td>
                                    <td>
                                        <textarea class="form-control" name="taskbu${countbusi}" id="taskbu${countbusi}" rows="2" placeholder="Insert Major Task" required></textarea>
                                    </td>
                                    <td>
                                        <select name="trainingbu${countbusi}" id="trainingbu${countbusi}" class="form-control" required>
                                            <option selected disabled value="">-- Select Training --</option>
                                                            <optgroup label="DIGITAL TRANSFORMATION & INNOVATION">
                                                                <option value="INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES">INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES</option>
                                                                <option value="DIGITAL SUPPLY CHAIN OPTIMISATION">DIGITAL SUPPLY CHAIN OPTIMISATION</option>
                                                                <option value="DIGITAL TWINS IN MANUFACTURING">DIGITAL TWINS IN MANUFACTURING</option>
                                                                <option value="ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES">ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES</option>
                                                                <option value="ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING">ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING</option>
                                                                <option value="DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY">DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY</option>
                                                                <option value="INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION">INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION</option>
                                                                <option value="AGILE AND LEAN DIGITAL PROJECT MANAGEMENT">AGILE AND LEAN DIGITAL PROJECT MANAGEMENT</option>
                                                                <option value="PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING">PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING</option>
                                                                <option value="CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS">CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS</option>
                                                                <option value="THE MODERN WORKPLACE">THE MODERN WORKPLACE</option>
                                                                <option value="OTHERS">OTHERS</option>
                                                            </optgroup>
                                                            </optgroup>
                                            </optgroup>
                                        </select>
                                        <br>
                                        <input type="text" name="otrbu${countbusi}" id="otrbu${countbusi}" class="form-control" placeholder="Others Training" autocomplete="off"/>
                                    </td>
                                    <td>
                                        <select name="targetskbu${countbusi}" id="targetskbu${countbusi}" class="form-control" required>
                                            <option value="1">1 - Fundamental Awareness</option>
                                            <option value="2">2 - Novice</option>
                                            <option value="3">3 - Intermediate</option>
                                            <option value="4">4 - Proficient</option>
                                            <option value="5">5 - Expert</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="currentskbu${countbusi}" id="currentskbu${countbusi}" class="form-control" required>
                                            <option value="1">1 - Fundamental Awareness</option>
                                            <option value="2">2 - Novice</option>
                                            <option value="3">3 - Intermediate</option>
                                            <option value="4">4 - Proficient</option>
                                            <option value="5">5 - Expert</option>
                                        </select>
                                    </td>
                                    <td style="text-align:center;">
                                        <input type="text" name="gapbu${countbusi}" id="gapbu${countbusi}" class="form-control" readonly />
                                    </td>
                                    <td>
                                        <select name="trtypebu${countbusi}" id="trtypebu${countbusi}" class="form-control" required>
                                            <option selected disabled value="">-- Select Training Type --</option>
                                            <option value="1">1 - On Job Training</option>
                                            <option value="2">2 - Coaching</option>
                                            <option value="3">3 - External / In-house</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="datetrbu${countbusi}" id="datetrbu${countbusi}" class="form-control" required>
                                            <option value="Jan">Jan</option>
                                            <option value="Feb">Feb</option>
                                            <option value="Mar">Mar</option>
                                            <option value="Apr">Apr</option>
                                            <option value="May">May</option>
                                            <option value="Jun">Jun</option>
                                            <option value="Jul">Jul</option>
                                            <option value="Aug">Aug</option>
                                            <option value="Sep">Sep</option>
                                            <option value="Oct">Oct</option>
                                            <option value="Nov">Nov</option>
                                            <option value="Dec">Dec</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a href="#" class="remove_busi btn btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                                `);
    } else if (countbusi > 3) {
        alert('You can add up to 3 task only');
        countbusi--;
    } else {
        alert('Please complete previous task details!');
        countbusi--;
    }

    $('#gapbu' + countbusi).val(0);
    $('#otrbu' + countbusi).hide();

    $('#trainingbu' + countbusi).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrbu' + countbusi).show();
        } else {
            $('#otrbu' + countbusi).hide();
        }
    });

    $('#targetskbu' + countbusi).on('change', function() {
        var targetbu = parseInt($('#targetskbu' + countbusi).val());
        var currentbu = parseInt($('#currentskbu' + countbusi).val());

        var gapbu = targetbu - currentbu;
        $('#gapbu' + countbusi).val(gapbu);
    });

    $('#currentskbu' + countbusi).on('change', function() {
        var targetbu = parseInt($('#targetskbu' + countbusi).val());
        var currentbu = parseInt($('#currentskbu' + countbusi).val());

        var gapbu = targetbu - currentbu;
        $('#gapbu' + countbusi).val(gapbu);
    });

    $('#busiaware').val(countbusi);
});

$(document).on('click', '.remove_busi', function() {
    countself--;
    $(this).closest('tr').remove();
    return false;
});

$('#busiaware').val(countbusi);

$('#add_spec').click(function() {
    countspec++;
    if (countspec == 1) {
        $("#tnaspeclist").append('<tr><td style="text-align:center;">' + countspec +
            '</td><td><textarea class="form-control" name="tasksp' + countspec + '" id="tasksp' +
            countspec +
            '" rows="2" placeholder="Insert Major Task" required></textarea></td><td><select name="trainingsp' +
            countspec + '" id="trainingsp' + countspec +
            '" class="form-control" required><option selected disabled value="">-- Select Training --</option><option value="OTHERS">OTHERS</option></select><br><input type="text" name="otrsp' +
            countspec + '" id="otrsp' + countspec +
            '" class="form-control" placeholder="Others Training" autocomplete="off"/></td><td><select name="targetsksp' +
            countspec + '" id="targetsksp' + countspec +
            '" class="form-control" required><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsksp' +
            countspec + '" id="currentsksp' + countspec +
            '" class="form-control" required><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gapsp' +
            countspec + '" id="gapsp' + countspec +
            '" class="form-control" readonly /></td><td><select name="trtypesp' + countspec +
            '" id="trtypesp' + countspec +
            '" class="form-control" required><option selected disabled value="">-- Select Training Type --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="3">3 - External / In-house</option></select></td><td><select name="datetrsp' +
            countspec + '" id="datetrsp' + countspec +
            '" class="form-control" required><option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option></select></td><td><a href="#" class="remove_spec btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td></tr>'
        );
    } else if ($('#tasksp' + (countspec - 1)).val() != '' && $('#trainingsp' + (countspec - 1)).val() != '' &&
        $('#trtypesp' + (countspec - 1)).val() != null) {
        $("#tnaspeclist").append('<tr><td style="text-align:center;">' + countspec +
            '</td><td><textarea class="form-control" name="tasksp' + countspec + '" id="tasksp' +
            countspec +
            '" rows="2" placeholder="Insert Major Task" required></textarea></td><td><select name="trainingsp' +
            countspec + '" id="trainingsp' + countspec +
            '" class="form-control" required><option selected disabled value="">-- Select Training --</option><option value="OTHERS">OTHERS</option></select><br><input type="text" name="otrsp' +
            countspec + '" id="otrsp' + countspec +
            '" class="form-control" placeholder="Others Training" autocomplete="off"/></td><td><select name="targetsksp' +
            countspec + '" id="targetsksp' + countspec +
            '" class="form-control" required><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td><select name="currentsksp' +
            countspec + '" id="currentsksp' + countspec +
            '" class="form-control" required><option value="1">1 - Fundamental Awareness</option><option value="2">2 - Novice</option><option value="3">3 - Intermediate</option><option value="4">4 - Proficient</option><option value="5">5 - Expert</option></select></td><td style="text-align:center;"><input type="text" name="gapsp' +
            countspec + '" id="gapsp' + countspec +
            '" class="form-control" readonly /></td><td><select name="trtypesp' + countspec +
            '" id="trtypesp' + countspec +
            '" class="form-control" required><option selected disabled value="">-- Select Training Type --</option><option value="1">1 - On Job Training</option><option value="2">2 - Coaching</option><option value="3">3 - External / In-house</option></select></td><td><select name="datetrsp' +
            countspec + '" id="datetrsp' + countspec +
            '" class="form-control" required><option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option></select></td><td><a href="#" class="remove_spec btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td></tr>'
        );
    } else if (countspec > 3) {
        alert('You can add up to 3 task only');
        countspec--;
    } else {
        alert('Please complete previous task details!');
        countspec--;
    }

    $('#gapsp' + countspec).val(0);
    $('#otrsp' + countspec).hide();

    $('#trainingsp' + countspec).on('change', function() {
        if ($(this).val() == 'OTHERS') {
            $('#otrsp' + countspec).show();
        } else {
            $('#otrsp' + countspec).hide();
        }
    });

    $('#targetsksp' + countspec).on('change', function() {
        var targetsp = parseInt($('#targetsksp' + countspec).val());
        var currentsp = parseInt($('#currentsksp' + countspec).val());

        var gapsp = targetsp - currentsp;
        $('#gapsp' + countspec).val(gapsp);
    });

    $('#currentsksp' + countspec).on('change', function() {
        var targetsp = parseInt($('#targetsksp' + countspec).val());
        var currentsp = parseInt($('#currentsksp' + countspec).val());

        var gapbu = targetsp - currentsp;
        $('#gapsp' + countspec).val(gapsp);
    });

    $('#special').val(countspec);
});

$(document).on('click', '.remove_spec', function() {
    countspec--;
    $(this).closest('tr').remove();
    return false;
});

$('#special').val(countspec);

$(document).on('submit', '#tna_form', function(event) {
    event.preventDefault();
    $("#spinner-div").show();
    $("#userid").val(userid);
    $("#btn_action").val('addtna');
    var form_data = $(this).serialize();
    console.log("form data: ", form_data);
    $.ajax({
        url: "tna_action.php",
        method: "POST",
        data: form_data,
        success: function(data) {
            var response = JSON.parse(data);
            console.log("response: ", response);
            if ((response.message) == 'insert') {
                swal(
                    'Added!',
                    'The TNA has been recorded.',
                    'success'
                ).then(function() {
                    window.location = "manage_tna.php";
                })
            } else if ((response.message) == 'error') {
                swal(
                    'Failed!',
                    'The operation cannot be done. Please refer to IT',
                    'error'
                ).then(function() {
                    $('#tna_form')[0].reset();
                })
            }
        },
        complete: function() {
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
