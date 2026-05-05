<?php
    include "../../../dbconn.php";

    if($_POST["action"] == "load_department"){
        echo loadDepartment();
    }else if($_POST["action"] == "load_section"){
        $department = $_POST['department'];
        echo loadSection($department);
    }else if($_POST["action"] == "load_dept"){
        $output= array();
        $trainid = '';

        if (isset($_POST["department"])) {
            $department = $_POST["department"];
        }else {
            $department = '';
        }

        if (isset($_POST["startdate"])) {
            $startdate = $_POST["startdate"];
        }else {
            $startdate = '';
        }

        if (isset($_POST["enddate"])) {
            $enddate = $_POST["enddate"];
        }else {
            $enddate = '';
        }

        if ($department != '' && $startdate == '') {
            if ($department != 'ALL') {
				$sql = "select training.id,user.department,title,startdate,enddate,starttime,endtime,venue,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,'PUBLIC' as trtype from training join participation on training.id = trainingid join user on userid = user.id where department = '$department' group by training.id union select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,'OJT' as trtype from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where user.department = '$department' group by ojt.id;";
				$query = mysqli_query($conn,$sql);
				while ($row = mysqli_fetch_assoc($query))
				{
					$trainid = $row['id'];

					if ($row['trtype'] == 'PUBLIC') {
						$query1 = "select count(*) as totalpeople from participation join user on userid = user.id where trainingid = '$trainid' and department = '$department'";
						$result1 = mysqli_query($conn, $query1);
						while($row1 = mysqli_fetch_array($result1)) {
							if ($row1['totalpeople'] != '') {
								$totalpeople = $row1['totalpeople'];
							}else {
								$totalpeople = 0;
							}
						}

						$query2 = "select count(*) as totalpeople from participation join user on userid = user.id where trainingid = '$trainid' and department = '$department' and attendance = 'COMPLETED'";
						$result2 = mysqli_query($conn, $query2);
						while($row2 = mysqli_fetch_array($result2)) {
							if ($row2['totalpeople'] != '') {
								$totalcomplete = $row2['totalpeople'];
							}else {
								$totalcomplete = 0;
							}
						}
					}else if ($row['trtype'] == 'OJT') {
						$query1 = "select sum(totalman) as totalpeople from participateojt join user on userid = user.id where ojtid = '$trainid' and user.department = '$department'";
						$result1 = mysqli_query($conn, $query1);
						while($row1 = mysqli_fetch_array($result1)) {
							if ($row1['totalpeople'] != '') {
								$totalpeople = $row1['totalpeople'];
							}else {
								$totalpeople = 0;
							}
						}

						$query2 = "select sum(totalman) as totalpeople from participateojt join user on userid = user.id where ojtid = '$trainid' and user.department = '$department' and attendance = 'COMPLETEDOJT'";
						$result2 = mysqli_query($conn, $query2);
						while($row2 = mysqli_fetch_array($result2)) {
							if ($row2['totalpeople'] != '') {
								$totalcomplete = $row2['totalpeople'];
							}else {
								$totalcomplete = 0;
							}
						}
					}

					if ($totalpeople != '0') {
						$percentage = ($totalcomplete/$totalpeople)*100;
					}else {
						$percentage = 0;
					}
					$percentageround = round($percentage,2);

					$idtitle = $row['id'].'|'.$row['title'];

					$output[]= array(
						'id' => $row['id'],
						'department' => $row['department'],
						'trtype' => $row['trtype'],
						'title' => '<a class="linkparty" id="'.$idtitle.'">'.$row['title'].'</a>',
						'startdate' => $row['startdate'],
						'enddate' => $row['enddate'],
						'starttime' => date("H:i", strtotime($row['starttime'])),
						'endtime' => date("H:i", strtotime($row['endtime'])),
						'venue' => $row['venue'],
						'totalday' => $row['totalday'],
						'totalhour' => $row['totalhour'],
						'totalman' => $totalcomplete.' / '.$totalpeople.'<br>('.$percentageround.' %)',
						'totalmanhour' => $row['totalhour'] * $totalcomplete
					); 
				}
			}
        }else if ($department != '' && $startdate != '') {
            if ($department != 'ALL') {
				$sql = "select training.id,user.department,title,startdate,enddate,starttime,endtime,venue,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,'PUBLIC' as trtype from training join participation on training.id = trainingid join user on userid = user.id where department = '$department' and startdate between '$startdate' and '$enddate' group by training.id union select ojt.id,user.department,title,startdate,enddate,starttime,endtime,venue,((DATEDIFF(enddate, startdate)) + 1) as totalday,((DATEDIFF(enddate, startdate)) + 1) * ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,'OJT' as trtype from ojt join participateojt on ojt.id = ojtid join user on userid = user.id where user.department = '$department' and startdate between '$startdate' and '$enddate' group by ojt.id;";
				$query = mysqli_query($conn,$sql);
				while ($row = mysqli_fetch_assoc($query))
				{
					$trainid = $row['id'];

					if ($row['trtype'] == 'PUBLIC') {
						$query1 = "select count(*) as totalpeople from participation join user on userid = user.id where trainingid = '$trainid' and department = '$department'";
						$result1 = mysqli_query($conn, $query1);
						while($row1 = mysqli_fetch_array($result1)) {
							if ($row1['totalpeople'] != '') {
								$totalpeople = $row1['totalpeople'];
							}else {
								$totalpeople = 0;
							}
						}

						$query2 = "select count(*) as totalpeople from participation join user on userid = user.id where trainingid = '$trainid' and department = '$department' and attendance in ('COMPLETED')";
						$result2 = mysqli_query($conn, $query2);
						while($row2 = mysqli_fetch_array($result2)) {
							if ($row2['totalpeople'] != '') {
								$totalcomplete = $row2['totalpeople'];
							}else {
								$totalcomplete = 0;
							}
						}
					}else if ($row['trtype'] == 'OJT') {
						$query1 = "select sum(totalman) as totalpeople from participateojt join user on userid = user.id where ojtid = '$trainid' and user.department = '$department'";
						$result1 = mysqli_query($conn, $query1);
						while($row1 = mysqli_fetch_array($result1)) {
							if ($row1['totalpeople'] != '') {
								$totalpeople = $row1['totalpeople'];
							}else {
								$totalpeople = 0;
							}
						}

						$query2 = "select sum(totalman) as totalpeople from participateojt join user on userid = user.id where ojtid = '$trainid' and user.department = '$department' and attendance in ('COMPLETEDOJT')";
						$result2 = mysqli_query($conn, $query2);
						while($row2 = mysqli_fetch_array($result2)) {
							if ($row2['totalpeople'] != '') {
								$totalcomplete = $row2['totalpeople'];
							}else {
								$totalcomplete = 0;
							}
						}
					}

					if ($totalpeople != '0') {
						$percentage = ($totalcomplete/$totalpeople)*100;
					}else {
						$percentage = 0;
					}
					$percentageround = round($percentage,2);
					
					$idtitle = $row['id'].'|'.$row['title'];

					$output[]= array(
						'id' => $row['id'],
						'department' => $row['department'],
						'trtype' => $row['trtype'],
						'title' => '<a class="linkparty" id="'.$idtitle.'">'.$row['title'].'</a>',
						'startdate' => $row['startdate'],
						'enddate' => $row['enddate'],
						'starttime' => date("H:i", strtotime($row['starttime'])),
						'endtime' => date("H:i", strtotime($row['endtime'])),
						'venue' => $row['venue'],
						'totalday' => $row['totalday'],
						'totalhour' => $row['totalhour'],
						'totalman' => $totalcomplete.' / '.$totalpeople.'<br>('.$percentageround.' %)',
						'totalmanhour' => $row['totalhour'] * $totalcomplete
					);  
				}
			}
        }
        
        echo json_encode($output);
    }else if($_POST["action"] == "load_deptsummary"){
        if (isset($_POST["department"])) {
            $department = $_POST["department"];
        }else {
            $department = '';
        }

        if (isset($_POST["startdate"])) {
            $startdate = $_POST["startdate"];
        }else {
            $startdate = '';
        }

        if (isset($_POST["enddate"])) {
            $enddate = $_POST["enddate"];
        }else {
            $enddate = '';
        }

        if ($department != '' && $startdate == '') {
            if ($department != 'ALL') {
				$sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid join user on userid = user.id where department = '$department' and attendance = 'COMPLETED')tablea;";
				$query = mysqli_query($conn,$sql);
				while($row = mysqli_fetch_assoc($query))
				{
					if ($row['totalmans'] != null) {
						$totalpub = $row['totalmans'];
					}else if ($row['totalmans'] == null) {
						$totalpub = 0;
					}
				}

				$sql1 = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where department = '$department';";
				$query1 = mysqli_query($conn,$sql1);
				while($row1 = mysqli_fetch_assoc($query1))
				{
					if ($row1['totalmans'] != null) {
						$totalojt = $row1['totalmans'];
					}else if ($row1['totalmans'] == null) {
						$totalojt = 0;
					}
				}
			}
        }else if ($department != '' && $startdate != '') {
           if ($department != 'ALL') {
				$sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid join user on userid = user.id where department = '$department' and attendance = 'COMPLETED')tablea;";
				$query = mysqli_query($conn,$sql);
				while($row = mysqli_fetch_assoc($query))
				{
					if ($row['totalmans'] != null) {
						$totalpub = $row['totalmans'];
					}else if ($row['totalmans'] == null) {
						$totalpub = 0;
					}
				}

				$sql1 = "select sum(totalman) as totalmans from ojt join (select ojtid,department from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where department = '$department';";
				$query1 = mysqli_query($conn,$sql1);
				while($row1 = mysqli_fetch_assoc($query1))
				{
					if ($row1['totalmans'] != null) {
						$totalojt = $row1['totalmans'];
					}else if ($row1['totalmans'] == null) {
						$totalojt = 0;
					}
				}
		    }
        }

        $output = ([
            'totalmans' => $totalpub+$totalojt,
        ]);
        echo json_encode($output);
    }else if($_POST["action"] == "load_participant"){
		$trainingid = $_POST['trainingid'];
		$department = $_POST['department'];
        $output= array();

		$statustr = explode('|', $trainingid);
		$newid = $statustr[0];
		$newtitle = $statustr[1];

		$sql = "select trtype from (select id,title,'PUBLIC' as trtype from training union select id,title,'OJT' as trtype from ojt)tablea where id = '$newid' and title = '$newtitle';";
		$query = mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query))
		{
			$typetr = $row['trtype'];
		}

		if ($typetr == 'PUBLIC') {
			$sql = "SELECT participation.id, participation.attendance, user.staffname,user.department,staffno FROM participation JOIN user ON participation.userid = user.id WHERE trainingid = '$newid' and designation != 'CONTRACT' and department = '$department';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['attendance'] == '') {
					$status = '<span class="label label-pill label-warning">PENDING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
				}else if ($row['attendance'] == 'COMPLETED') {
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
		}else if ($typetr == 'OJT') {
			$sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$newid' and designation != 'CONTRACT' and user.department = '$department';";
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
		$department = $_POST['department'];
        $output= array();

		$statustr = explode('|', $trainingid);
		$newid = $statustr[0];
		$newtitle = $statustr[1];

		$sql = "select trtype from (select id,title,'PUBLIC' as trtype from training union select id,title,'OJT' as trtype from ojt)tablea where id = '$newid' and title = '$newtitle';";
		$query = mysqli_query($conn,$sql);
		while($row = mysqli_fetch_assoc($query))
		{
			$typetr = $row['trtype'];
		}

		if ($typetr == 'PUBLIC') {
			$sql = "SELECT participation.id, participation.attendance, user.staffname,user.department,staffno FROM participation JOIN user ON participation.userid = user.id WHERE trainingid = '$newid' and designation = 'CONTRACT' and department = '$department';";
			$query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['attendance'] == '') {
					$status = '<span class="label label-pill label-warning">PENDING</span>';
					$btnedit = '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm absent" style="margin-left:5px;"><i class="far fa-calendar-times"></i> ABSENT?</button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> DELETE</button>';
				}else if ($row['attendance'] == 'COMPLETED') {
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
		}else if ($typetr == 'OJT') {
			$sql = "SELECT participateojt.id, participateojt.attendance, user.staffname,user.department,staffno FROM participateojt JOIN user ON participateojt.userid = user.id WHERE ojtid = '$newid' and designation = 'CONTRACT' and user.department = '$department';";
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
		}
        echo json_encode($output);
    }

    function loadDepartment(){
		global $conn;
		$sql = "SELECT DISTINCT(`department`) AS department FROM `user` ORDER BY department";
		$query = mysqli_query($conn,$sql);
		$options='<option value="" hidden>-- Select Department --</option><option value="ALL">All Department</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['department'].'">'.$row['department'].'</option>';
		  }
		}
		return $options;
	}
	
	function loadSection($department){
		global $conn;
		$sql = "SELECT DISTINCT(if (section = '','-',section)) as section FROM user where department = '$department' order by section";
		$query = mysqli_query($conn,$sql);
		$options='<option value="">-- Select Section --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['section'].'">'.$row['section'].'</option>';
		  }
		}
		return $options;
	}
?>