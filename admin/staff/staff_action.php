<?php 
    include "../../dbconn.php";
    include_once __DIR__ . '/../../division_department_section.php';
    include_once __DIR__ . '/delete_impact.php';

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
            $date_join = !empty($_POST['date_join']) ? $_POST['date_join'] : null;
            $plant = !empty($_POST['plant']) ? $_POST['plant'] : null;

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
            $dateJoinValue = is_null($date_join) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $date_join) . "'";
            $plantValue = is_null($plant) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $plant) . "'";
            $sql = "INSERT INTO `user` (`staffno`,`password`,`staffname`,`email`,`gender`,`designation`,`department`,`division`,`section`,`division_id`,`department_id`,`section_id`,`status`,`hodid`,`date_join`,`plant`) values ('$staffno','$password', '$staffname','$email', '$gender', '$designation', '$department', '$division', '$section', '$division_id', '$department_id', $sectionIdValue, '$status', '$hodid', $dateJoinValue, $plantValue)";
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
            $date_join = !empty($_POST['date_join']) ? $_POST['date_join'] : null;
            $plant = !empty($_POST['plant']) ? $_POST['plant'] : null;

            $hodnew = 0;
            $sql1 = "select id from user where department = '$department' and usertype = 'HOD'";
            $query1 = mysqli_query($conn,$sql1);
            while($row1 = mysqli_fetch_assoc($query1)) {
                $hodnew = (int) $row1['id'];
            }

            $hodprev = 0;
            $sql2 = "select hodid from user where id = '$id'";
            $query2 = mysqli_query($conn,$sql2);
            while($row2 = mysqli_fetch_assoc($query2)) {
                $hodprev = $row2['hodid'] !== null ? (int) $row2['hodid'] : 0;
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
            $dateJoinValue = is_null($date_join) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $date_join) . "'";
            $plantValue = is_null($plant) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $plant) . "'";

            if ($hodprev != 0) {
                $sql = "UPDATE `user` SET `staffno` = '$staffno', `staffname` = '$staffname', `email` = '$email', `gender` = '$gender', `designation` = '$designation', `department` = '$department', `division` = '$division', `section` = '$section', `division_id` = '$division_id', `department_id` = '$department_id', `section_id` = $sectionIdValue, `status` = '$status', `hodid` = '$hodnew', `date_join` = $dateJoinValue, `plant` = $plantValue WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                }
            }else {
                $sql = "UPDATE `user` SET `staffno` = '$staffno', `staffname` = '$staffname', `email` = '$email', `gender` = '$gender', `designation` = '$designation', `department` = '$department', `division` = '$division', `section` = '$section', `division_id` = '$division_id', `department_id` = '$department_id', `section_id` = $sectionIdValue, `status` = '$status', `date_join` = $dateJoinValue, `plant` = $plantValue WHERE `id` = '$id'";
                if(mysqli_query($conn, $sql)){
                    echo json_encode(['message' => 'update']);
                }else {
                    echo json_encode(['message' => 'error']);
                } 
            }
        }else if ($_POST['btn_action'] == 'delete_impact') {
            // Asked before the confirmation dialog, so the admin is told what
            // deleting actually destroys instead of a bare "Are you sure?".
            $impact = staffDeleteImpact($conn, $_POST['id']);
            if ($impact === null) {
                echo json_encode(['message' => 'error', 'detail' => 'Staff not found.']);
            } else {
                echo json_encode([
                    'message' => 'ok',
                    'staffno' => $impact['staffno'],
                    'staffname' => $impact['staffname'],
                    'warnings' => staffDeleteImpactWarnings($impact),
                    'blockers' => staffDeleteBlockers($impact),
                    'has_approved_matrix' => $impact['matrix_approved'] > 0,
                ]);
            }
        }else if ($_POST['btn_action'] == 'deleteuser') {
            // Refuses when the delete would orphan or destroy history, and
            // clears dangling reporting links when it does proceed.
            $result = staffDeleteSafely($conn, $_POST['id']);
            if ($result['ok']) {
                echo json_encode(['message' => 'delete', 'cleared' => $result['cleared']]);
            } else {
                echo json_encode([
                    'message' => $result['blocked'] ? 'blocked' : 'error',
                    'detail'  => $result['detail'],
                ]);
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