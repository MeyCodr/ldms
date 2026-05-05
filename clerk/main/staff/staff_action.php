<?php 
    include "../../../dbconn.php";
    include_once __DIR__ . '/../../../division_department_section.php';

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'adduser') {
            $staffno = strtoupper($_POST['staffno']);
            $staffname = strtoupper($_POST['staffname']);
            $gender = $_POST['gender'];
            $department = $_POST['department'];
            $division = $_POST['division'];
			$section = $_POST['section'];
            $division_id = getDivisionIdByName($division);
            $department_id = getDepartmentIdByName($division_id, $department);
            $section_id = getSectionIdByName($department_id, $section);

            // HOD users should not have section_id and section should be blank
            if ($designation === 'HOD' || (isset($_POST['usertype']) && $_POST['usertype'] === 'HOD')) {
                $section = '';
                $section_id = null;
            }
            $sectionIdValue = is_null($section_id) ? 'NULL' : "'$section_id'";
            $password = md5('P@ss1234');
            $sql = "INSERT INTO `user` (`staffno`,`password`,`staffname`,`gender`,`designation`,`department`,`division`,`section`,`division_id`,`department_id`,`section_id`,`status`) values ('$staffno','$password', '$staffname', '$gender', 'CONTRACT', '$department', '$division', '$section', '$division_id', '$department_id', $sectionIdValue, '$status')";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'edituser') {
            $staffno = strtoupper($_POST['staffno']);
            $staffname = strtoupper($_POST['staffname']);
            $gender = $_POST['gender'];
            $department = $_POST['department'];
            $division = $_POST['division'];
			$section = $_POST['section'];
            $id = $_POST['id'];
			$status = $_POST['status'];
            $division_id = getDivisionIdByName($division);
            $department_id = getDepartmentIdByName($division_id, $department);
            $section_id = getSectionIdByName($department_id, $section);

            // HOD users should not have section_id and section should be blank
            if ($designation === 'HOD' || (isset($_POST['usertype']) && $_POST['usertype'] === 'HOD')) {
                $section = '';
                $section_id = null;
            }
            $sectionIdValue = is_null($section_id) ? 'NULL' : "'$section_id'";
            $sql = "UPDATE `user` SET `staffno` = '$staffno', `staffname` = '$staffname', `gender` = '$gender', `designation` = 'CONTRACT', `department` = '$department', `division` = '$division', `section` = '$section', `division_id` = '$division_id', `department_id` = '$department_id', `section_id` = $sectionIdValue, `status` = '$status' WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'update']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'deleteuser') {
            $id = $_POST['id'];
            $sql = "DELETE FROM `user` WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>