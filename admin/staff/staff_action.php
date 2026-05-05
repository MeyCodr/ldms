<?php 
    include "../../dbconn.php";
    include_once __DIR__ . '/../../division_department_section.php';

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'adduser') {
            $staffno = strtoupper($_POST['staffno']);
            $staffname = strtoupper($_POST['staffname']);
            $email = $_POST['email'];
            $gender = $_POST['gender'];
            $designation = $_POST['designation'];
            $department = $_POST['department'];
            $division = $_POST['division'];
			$section = $_POST['section'];
            $division_id = getDivisionIdByName($division);
            $department_id = getDepartmentIdByName($division_id, $department);
            $section_id = getSectionIdByName($department_id, $section);

            $status = $_POST['status'];

            // HOD users should not have section_id and section should be blank
            if ($designation === 'HOD' || (isset($_POST['usertype']) && $_POST['usertype'] === 'HOD')) {
                $section = '';
                $section_id = null;
            }

            // set hodid for department staff (if department has a HOD account)
            $hodid = 0;
            $sqlHod = "SELECT id FROM user WHERE department = '$department' AND usertype = 'HOD' LIMIT 1";
            $queryHod = mysqli_query($conn, $sqlHod);
            if ($queryHod && $rowHod = mysqli_fetch_assoc($queryHod)) {
                $hodid = mysqli_real_escape_string($conn, $rowHod['id']);
            }

            $password = md5('P@ss1234');
            $sectionIdValue = is_null($section_id) ? 'NULL' : "'$section_id'";
            $sql = "INSERT INTO `user` (`staffno`,`password`,`staffname`,`email`,`gender`,`designation`,`department`,`division`,`section`,`division_id`,`department_id`,`section_id`,`status`,`hodid`) values ('$staffno','$password', '$staffname','$email', '$gender', '$designation', '$department', '$division', '$section', '$division_id', '$department_id', $sectionIdValue, '$status', '$hodid')";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'edituser') {
            $staffno = strtoupper($_POST['staffno']);
            $staffname = strtoupper($_POST['staffname']);
            $email = $_POST['email'];
            $gender = $_POST['gender'];
            $designation = $_POST['designation'];
            $department = $_POST['department'];
            $division = $_POST['division'];
			$section = $_POST['section'];
            $id = $_POST['id'];
			$status = $_POST['status'];

            $sql1 = "select id from user where department = '$department' and usertype = 'HOD'";
            $query1 = mysqli_query($conn,$sql1);
            while($row1 = mysqli_fetch_assoc($query1)) {
                $hodnew = mysqli_real_escape_string($conn, $row1['id']);
            }

            $sql2 = "select hodid from user where id = '$id'";
            $query2 = mysqli_query($conn,$sql2);
            while($row2 = mysqli_fetch_assoc($query2)) {
                $hodprev = mysqli_real_escape_string($conn, $row2['hodid']);
            }

            $division_id = getDivisionIdByName($division);
            $department_id = getDepartmentIdByName($division_id, $department);
            $section_id = getSectionIdByName($department_id, $section);

            // HOD users should not have section_id and section should be blank
            if ($designation === 'HOD' || (isset($_POST['usertype']) && $_POST['usertype'] === 'HOD')) {
                $section = '';
                $section_id = null;
            }
            $sectionIdValue = is_null($section_id) ? 'NULL' : "'$section_id'";

            if ($hodprev != 0) {
                $sql = "UPDATE `user` SET `staffno` = '$staffno', `staffname` = '$staffname', `email` = '$email', `gender` = '$gender', `designation` = '$designation', `department` = '$department', `division` = '$division', `section` = '$section', `division_id` = '$division_id', `department_id` = '$department_id', `section_id` = $sectionIdValue, `status` = '$status', `hodid` = '$hodnew' WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                }
            }else {
                $sql = "UPDATE `user` SET `staffno` = '$staffno', `staffname` = '$staffname', `email` = '$email', `gender` = '$gender', `designation` = '$designation', `department` = '$department', `division` = '$division', `section` = '$section', `division_id` = '$division_id', `department_id` = '$department_id', `section_id` = $sectionIdValue, `status` = '$status' WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }
        }else if ($_POST['btn_action'] == 'deleteuser') {
            $id = $_POST['id'];
            $sql = "DELETE FROM `user` WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'delete']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }else if ($_POST['btn_action'] == 'resetpassword') {
            $id = $_POST['id'];
            $sql = "UPDATE `user` SET `password` = '9e9bbdcf1c723753f617a0f2be7c9bfb' WHERE `id` = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'reset']);
            }else {
                echo json_encode(['message' => 'error']);
            } 
        }
    }
?>