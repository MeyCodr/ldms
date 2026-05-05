<?php 
    include "../../dbconn.php";

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addtni') {
            $userid = $_POST['userid'];
            $mandatory = $_POST['mandatory'];

            $sql = "select department from user where id = '$userid'";
            $query = mysqli_query($conn,$sql);
            while($row = mysqli_fetch_assoc($query))
            {
                $department = $row['department'];
            }

            $sql = "delete from tni where department = '$department' and year = year(curdate());";
            mysqli_query($conn, $sql);

            for ($i=1;$i<=$mandatory;$i++) {
                $task = strtoupper($_POST['task'.$i]);
                $targetsk = $_POST['targetsk'.$i];
                $currentsk = $_POST['currentsk'.$i];
                $gap = $_POST['gap'.$i];
                $cause = strtoupper($_POST['cause'.$i]);
                $ask = strtoupper($_POST['ask'.$i]);
                $trtype = strtoupper($_POST['trtype'.$i]);
                $evaluate = strtoupper($_POST['evaluate'.$i]);

                $sql = "insert into tni (training,expected,actual,gap,cause,ask,method,evaluation,department,year) values ('$task','$targetsk','$currentsk','$gap','$cause','$ask','$trtype','$evaluate','$department',year(curdate()))";
                mysqli_query($conn, $sql);
            }
            echo json_encode(['message' => 'insert']);
        }
    }
?>