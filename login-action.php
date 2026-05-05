<?php
    include "dbconn.php";
    session_start();

    if (isset($_POST['username']) && isset($_POST['password'])) {
    	$username = $_POST['username'];
    	$password = md5($_POST['password']);
    	
        $sql = "SELECT * FROM user WHERE staffno='$username' AND password='$password'";
        $result = mysqli_query($conn, $sql);
    	if (mysqli_num_rows($result) == 1) {
    		$row = mysqli_fetch_assoc($result);
            if ($row['roletype'] == 'ADMIN') {
                $_SESSION['id'] = $row['id'];
                $_SESSION['fullname'] = $row['staffname'];
                $_SESSION['role'] = $row['roletype'];
                $_SESSION['usertype'] = $row['usertype'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['designation'] = $row['designation'];
                $_SESSION['hodid'] = $row['hodid'];
            }else if ($row['roletype'] == 'CLERK') {
                $_SESSION['id'] = $row['id'];
                $_SESSION['fullname'] = $row['staffname'];
                $_SESSION['role'] = $row['roletype'];
                $_SESSION['usertype'] = $row['usertype'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['designation'] = $row['designation'];
                $_SESSION['hodid'] = $row['hodid'];
            }else if ($row['roletype'] == '') {
                $_SESSION['id'] = $row['id'];
                $_SESSION['fullname'] = $row['staffname'];
                $_SESSION['role'] = $row['roletype'];
				$_SESSION['designation'] = $row['designation'];
                $_SESSION['usertype'] = $row['usertype'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['hodid'] = $row['hodid'];
            }
            echo json_encode(['message' => 'login']);
        }else {
            echo json_encode(['message' => 'notlogin']);
        }
    }
?>
