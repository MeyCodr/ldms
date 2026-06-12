<?php
session_start();

if (isset($_SESSION['fullname'])) {
    if ($_SESSION['role'] == 'ADMIN') {
        header("Location: admin/dashboard.php");
    }else if ($_SESSION['role'] == 'CLERK') {
        if ($_SESSION['usertype'] == 'MAIN'){
            header("Location: clerk/main/dashboard.php");
        }else if (!empty($_SESSION['is_sm_user'])) {
            header("Location: staff/office/dashboard.php");
        }else {
            header("Location: clerk/general/dashboard.php");
        }
    }else if ($_SESSION['role'] == '') {
        if ($_SESSION['designation'] == 'CONTRACT') {
            if (!empty($_SESSION['is_sm_user'])) {
                header("Location: staff/office/dashboard.php");
            } else {
                header("Location: staff/general/dashboard.php");
            }
        }else if ($_SESSION['designation'] == 'EXECUTIVE') {
            header("Location: staff/office/dashboard.php");
        }else if ($_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)' && $_SESSION['usertype'] != 'HOD') {
            header("Location: staff/office/dashboard.php");
        }else if ($_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)' && $_SESSION['usertype'] == 'HOD'){
           header("Location: staff/hod/dashboard.php");
        }else if ($_SESSION['designation'] == 'NON EXECUTIVE') {
            if ($_SESSION['usertype'] == 'OFFICE' || !empty($_SESSION['is_sm_user'])){
                header("Location: staff/office/dashboard.php");
            }else {
                header("Location: staff/general/dashboard.php");
            }
        }
    }
}else{
    header("Location: login.php");
    exit();
}
?>
