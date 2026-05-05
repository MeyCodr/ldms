<?php
    include "../../../../dbconn.php";

    if($_POST["action"] == "load_participant"){
		$trainingid = $_POST['trainingid'];
        $output= array();
        $sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$trainingid' and designation != 'CONTRACT';";
		$query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
			if ($row['attendance'] == '') {
				$status = '<span class="label label-pill label-warning">PENDING</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}else if ($row['attendance'] == 'COMPLETEDOJT') {
				$status = '<span class="label label-pill label-success">COMPLETED</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}else if ($row['attendance'] == 'ABSENT') {
				$status = '<span class="label label-pill label-danger">ABSENT</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}

            $output[]= array(
                'id' => $row['id'],
				'staffno' => $row['staffno'],
                'staffname' => $row['staffname'],
				'department' => $row['department'],
				'status' => $status,
                'btnedit' => $btnedit,
            ); 
        }
        echo json_encode($output);
    }else if($_POST["action"] == 'fetch_trainingtitle'){
		$trainingid = $_POST['trainingid'];
		$output= array();
		$sql = "select title from ojt where id = '$trainingid';";
		$query = mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query))
		{
			$title = $row['title'];
		}
	
		$output[]= array(
			'title' => $title,
		);
	
		echo json_encode($output);
	}else if($_POST["action"] == "load_contract"){
		$trainingid = $_POST['trainingid'];
        $output= array();
        $sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$trainingid' and designation = 'CONTRACT';";
		$query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
			if ($row['attendance'] == '') {
				$status = '<span class="label label-pill label-warning">PENDING</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}else if ($row['attendance'] == 'COMPLETEDOJT') {
				$status = '<span class="label label-pill label-success">COMPLETED</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}else if ($row['attendance'] == 'ABSENT') {
				$status = '<span class="label label-pill label-danger">ABSENT</span>';
				$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
			}

            $output[]= array(
                'id' => $row['id'],
				'staffno' => $row['staffno'],
                'staffname' => $row['staffname'],
				'department' => $row['department'],
				'status' => $status,
                'btnedit' => $btnedit,
            ); 
        }
        echo json_encode($output);
    }
?>