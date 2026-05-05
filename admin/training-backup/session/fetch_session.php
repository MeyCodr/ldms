<?php
    include "../../../dbconn.php";

    if($_POST["action"]=="load_staff"){
        echo loadStaff();
    }else if($_POST["action"] == "load_session"){
        $trainingid = $_POST['trainingid'];
        $output= array();
        $sql = "SELECT * from session where trainingid = '$trainingid';";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $output[]= array(
                'id' => $row['id'],
                'description' => $row['description'],
                'date' => $row['date'],
                'starttime' => date("g:i A", strtotime($row['starttime'])),
                'endtime' => date("g:i A", strtotime($row['endtime'])),
                'venue' => $row['venue'],
                'trainer' => $row['trainer'],
                'trainername' => $row['trainername'],
                'btnedit' => '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>',
            ); 
        }
        echo json_encode($output);
    }else if($_POST["action"] == "fetch_session"){
        $id = $_POST["id"];
        $sql = "SELECT `description`,`date`,starttime,endtime,`venue`,`trainer`,`trainername` from session where id = '$id';";
        $query = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }

    function loadStaff(){
		global $conn;
		$sql = "SELECT staffname FROM user order by staffname";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Trainer --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['staffname'].'">'.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}
?>