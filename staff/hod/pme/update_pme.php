<?php
include "../../../dbconn.php";

$id = $_POST['id'];
$staffname = $_POST['staffname'];
$staffno = $_POST['staffno'];
$department = $_POST['department'];
$training_title = $_POST['training_title'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$ojt = $_POST['ojt'];
$level_rating = $_POST['level_rating'];
$level_percent = $_POST['level_percent'];
$level_remark = $_POST['level_remark'];
$level_rating2 = $_POST['level_rating2'];
$level_percent2 = $_POST['level_percent2'];
$level_remark2 = $_POST['level_remark2'];
$behavioral_rating = $_POST['behavioral_rating'];
$behavioral_percent = $_POST['behavioral_percent'];
$behavioral_remark = $_POST['behavioral_remark'];
$result_rating = $_POST['result_rating'];
$result_percent = $_POST['result_percent'];
$result_remark = $_POST['result_remark'];

date_default_timezone_set('Asia/Kuala_Lumpur');
$pme_created = date('Y-m-d H:i:s'); 

$status = 'approved';

$sql = "UPDATE pme SET 
    staffname='$staffname', 
    staffno='$staffno', 
    department='$department', 
    training_title='$training_title', 
    from_date='$from_date', 
    to_date='$to_date', 
    ojt='$ojt',
    level_rating='$level_rating', 
    level_percent='$level_percent', 
    level_remark='$level_remark', 
    level_rating2='$level_rating2', 
    level_percent2='$level_percent2', 
    level_remark2='$level_remark2', 
    behavioral_rating='$behavioral_rating', 
    behavioral_percent='$behavioral_percent', 
    behavioral_remark='$behavioral_remark', 
    result_rating='$result_rating', 
    result_percent='$result_percent', 
    result_remark='$result_remark', 
    pme_created='$pme_created',
    status='$status'  
WHERE id='$id'";

$conn->query($sql);


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;


header("Location: ../dashboard.php?page=$page#pme-list");
exit();
?>
