<?php

    include "../../../dbconn.php";

    if (isset($_POST['action'])) {
        if($_POST["action"]=="load_staff"){
            echo loadStaff();
        }else if($_POST["action"]=="load_training"){
			$userid = $_POST['userid'];
			$btnedit = '';
            $output= array();
			$sql = "select participation.id,'PUBLIC/INHOUSE' as type,participation.attendance,training.title,training.startdate,'0' as clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,training.venue from training join participation on training.id = trainingid where userid = '$userid' union select ojt.id,'OJT' as type,attendance,title,startdate,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,venue from ojt join participateojt on ojt.id = participateojt.ojtid where userid = '$userid';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['attendance'] == 'COMPLETED') {
					$status = '<span class="label label-pill label-success">'.$row['attendance'].'</span>';
					$totalhour = round(($row['totalday'] * $row['totalhour']),2);
				}else if ($row['attendance'] == '' && $row['type'] == 'PUBLIC/INHOUSE'){
					$status = '<span class="label label-pill label-warning">WAITING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm attendance" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill in Attendance</button>';
					$totalhour = 0;
				}else if ($row['attendance'] == '' && $row['type'] == 'OJT'){
					$status = '<span class="label label-pill label-warning">WAITING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm attendance_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill In Attendance</button>';
					$totalhour = 0;
				}else if ($row['attendance'] == 'ABSENT'){
					$status = '<span class="label label-pill label-danger">'.$row['attendance'].'</span>';
					$totalhour = 0;
				}else if ($row['attendance'] == 'COMPLETEDOJT') {
					if ($row['clerkid'] == '0') {
						$status = '<span class="label label-pill label-success">COMPLETED</span>';
						$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm deleteownojt" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>';
						$totalhour = round(($row['totalday'] * $row['totalhour']),2);
					}else {
						$status = '<span class="label label-pill label-success">COMPLETED</span>';
						$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button>';
						$totalhour = round(($row['totalday'] * $row['totalhour']),2);
					}
				}

				$output[]= array(
					'id' => $row['id'],
					'title' => $row['title'],
					'type' => $row['type'],
					'startdate' => $row['startdate'],
					'venue' => $row['venue'],
					'totalhour' => $totalhour,
					'status' => $status,
					'btnedit' => $btnedit,
				); 
			}
			echo json_encode($output);
		}else if($_POST["action"]=="filter_training"){
			$userid = $_POST['userid'];
			$startdate = $_POST['startdate'];
			$enddate = $_POST['enddate'];
			$btnedit = '';
            $output= array();
			$sql = "select participation.id,'PUBLIC/INHOUSE' as type,participation.attendance,training.title,training.startdate,'0' as clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,training.venue from training join participation on training.id = trainingid where userid = '$userid' and startdate between '$startdate' and '$enddate' union select ojt.id,'OJT' as type,attendance,title,startdate,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,venue from ojt join participateojt on ojt.id = participateojt.ojtid where userid = '$userid' and startdate between '$startdate' and '$enddate';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['attendance'] == 'COMPLETED') {
					$status = '<span class="label label-pill label-success">'.$row['attendance'].'</span>';
					$totalhour = round(($row['totalday'] * $row['totalhour']),2);
				}else if ($row['attendance'] == '' && $row['type'] == 'PUBLIC/INHOUSE'){
					$status = '<span class="label label-pill label-warning">WAITING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm attendance" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill in Attendance</button>';
					$totalhour = 0;
				}else if ($row['attendance'] == '' && $row['type'] == 'OJT'){
					$status = '<span class="label label-pill label-warning">WAITING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm attendance_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill In Attendance</button>';
					$totalhour = 0;
				}else if ($row['attendance'] == 'ABSENT'){
					$status = '<span class="label label-pill label-danger">'.$row['attendance'].'</span>';
					$totalhour = 0;
				}else if ($row['attendance'] == 'COMPLETEDOJT') {
					if ($row['clerkid'] == '0') {
						$status = '<span class="label label-pill label-success">COMPLETED</span>';
						$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm deleteownojt" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>';
						$totalhour = round(($row['totalday'] * $row['totalhour']),2);
					}else {
						$status = '<span class="label label-pill label-success">COMPLETED</span>';
						$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button>';
						$totalhour = round(($row['totalday'] * $row['totalhour']),2);
					}
				}

				$output[]= array(
					'id' => $row['id'],
					'title' => $row['title'],
					'type' => $row['type'],
					'startdate' => $row['startdate'],
					'venue' => $row['venue'],
					'totalhour' => $totalhour,
					'status' => $status,
					'btnedit' => $btnedit,
				); 
			}
			echo json_encode($output);
		}else if($_POST["action"] == "viewojtattendance"){
			$ojtid = $_POST["id"];
			$sql = "select * from ojt join participateojt on ojt.id = ojtid where ojtid = '$ojtid';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				$output[]= array(
					'title' => $row['title'],
					'startdate' => $row['startdate'],
					'enddate' => $row['enddate'],
					'starttime' => date('G:i',strtotime($row['starttime'])),
					'endtime' => date('G:i',strtotime($row['endtime'])),
					'venue' => $row['venue'],
					'trainertype' => $row['trainertype'],
					'trainername' => $row['trainername'],
					'q1' => $row['q1'],
					'q2' => $row['q2'],
					'q3' => $row['q3'],
				);
			}
			echo json_encode($output);
		}else if($_POST["action"] == "gettitle"){
			$ojtid = $_POST["id"];
			$sql = "select * from ojt where id = '$ojtid';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				$output[]= array(
					'title' => $row['title'],
				);
			}
			echo json_encode($output);
		}
    }

	function loadStaff(){
		global $conn;
		$sql = "SELECT staffno,staffname FROM user order by staffno";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Trainer --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['staffname'].'">'.$row['staffno'].' - '.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}
?>