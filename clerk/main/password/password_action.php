<?php

    include "../../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == "Add") {
            $id = $_POST['id'];
            $password = md5($_POST['password']);

            $sql = "update `user` set password = '$password' where id = '$id'";
            if(mysqli_query($conn, $sql)){
                echo json_encode(['message' => 'insert']);
            }
        }
    }
?>
