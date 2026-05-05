<?php

include "../../dbconn.php";

if (isset($_POST['action'])) {
	if ($_POST["action"] == "load_staff") {
		echo loadStaff();
	} else if ($_POST["action"] == "load_training") {
		$userid = $_POST['userid'];
		$btnedit = '';
		$output = array();
		$sql = "SELECT 
				participation.id, 
				'PUBLIC/INHOUSE' AS type, 
				participation.attendance, 
				training.title, 
				training.startdate,
				training.enddate, -- added enddate
				'0' AS clerkid, 
				((DATEDIFF(training.enddate, training.startdate)) + 1) AS totalday, 
				ROUND((TIME_TO_SEC(TIMEDIFF(training.endtime, training.starttime)) / 60) / 60, 2) AS totalhour, 
				training.venue, 
				pme.status, 
				pme.designation
			FROM training 
			JOIN participation 
				ON training.id = participation.trainingid 
			LEFT JOIN pme 
				ON participation.userid = pme.userid 
				AND participation.id = pme.participationid 
			WHERE participation.userid = '$userid' 

			UNION 

			SELECT 
				ojt.id, 
				'OJT' AS type, 
				participateojt.attendance, 
				ojt.title, 
				ojt.startdate,
				ojt.enddate, -- added enddate
				participateojt.clerkid, 
				((DATEDIFF(ojt.enddate, ojt.startdate)) + 1) AS totalday, 
				ROUND((TIME_TO_SEC(TIMEDIFF(ojt.endtime, ojt.starttime)) / 60) / 60, 2) AS totalhour, 
				ojt.venue, 
				pme.status, 
				pme.designation
			FROM ojt 
			JOIN participateojt 
				ON ojt.id = participateojt.ojtid 
			LEFT JOIN pme 
				ON participateojt.userid = pme.userid 
				AND participateojt.id = pme.participationid 
			WHERE participateojt.userid = '$userid';";

		$query = mysqli_query($conn, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			if ($row['attendance'] == 'COMPLETED') {
				$status = '<span class="label label-pill label-success">' . $row['attendance'] . '</span>';
				$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
				$custom_sort = 3;
			} else if ($row['attendance'] == '' && $row['type'] == 'PUBLIC/INHOUSE') {
				$status = '<span class="label label-pill label-warning">WAITING</span>';
				$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm attendance" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill in Attendance</button>';
				$totalhour = 0;
				$custom_sort = 2;
			} else if ($row['attendance'] == '' && $row['type'] == 'OJT') {
				$status = '<span class="label label-pill label-warning">WAITING</span>';
				$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm attendance_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill In Attendance</button>';
				$totalhour = 0;
				$custom_sort = 1;
			} else if ($row['attendance'] == 'ABSENT') {
				$status = '<span class="label label-pill label-danger">' . $row['attendance'] . '</span>';
				$totalhour = 0;
				$custom_sort = 4;
			} else if ($row['attendance'] == 'COMPLETEDOJT') {
				if ($row['clerkid'] == '0') {
					$status = '<span class="label label-pill label-success">COMPLETED</span>';
					$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button>
									<button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm deleteownojt" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>';
					$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
					$custom_sort = 3;
				} else {
					$status = '<span class="label label-pill label-success">COMPLETED</span>';
					$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button>';
					$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
					$custom_sort = 3;
				}
			}

			// PME Status label & Button 
			if ($row['type'] == 'OJT' || $row['designation'] != 'EXECUTIVE' && $row['designation'] != 'MANAGER (AM/HOS & ABOVE)') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-success" style="width: 100px; font-size: 11px; border-radius: 5px;">
									COMPLETED
								</span>
							</div>';
			} else if ($row['status'] == 'pending') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-warning" style="width: 100px; font-size: 11px; border-radius: 5px;">
									PENDING
								</span>
								<br><small class="text-muted mt-5 d-block">Waiting for HOD evaluation</small>
							</div>';
			} else if ($row['status'] == 'approved') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-info p-2" style="width: 100px; font-size: 11px; border-radius: 5px;">
									APPROVED
								</span>
								<br>
								<a href="edit_pme.php?userid=' . $userid . '&participationid=' . $row['id'] . '" 
								class="btn btn-info btn-sm mt-3" style="margin-top: 5px;">
									<i class="fa fa-eye"></i> View PME
								</a>
							</div>';
			} else if (($row['status']) == 'completed' || ($row['status']) == 'verified') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-success" style="width: 90px; font-size: 11px; border-radius: 5px;">
									COMPLETED
								</span>
							</div>';
			}

			$output[] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'type' => $row['type'],
				'startdate' => $row['startdate'],
				'enddate' => $row['enddate'],
				'venue' => $row['venue'],
				'totalhour' => $totalhour,
				'status' => $status,
				'btnedit' => $btnedit,
				'pme' => $pme,
				'status_sort' => $row['status'] == 'approved' ? 1 : ($row['status'] == 'pending' ? 2 : 3),
				'custom_sort' => $custom_sort
			);
		}
		echo json_encode($output);
	} else if ($_POST["action"] == "filter_training") {
		$userid = $_POST['userid'];
		$startdate = $_POST['startdate'];
		$enddate = $_POST['enddate'];
		$btnedit = '';
		$output = array();
		$sql = "select participation.id,'PUBLIC/INHOUSE' as type,participation.attendance,training.title,training.startdate,'0' as clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,training.venue from training join participation on training.id = trainingid where userid = '$userid' and startdate between '$startdate' and '$enddate' union select ojt.id,'OJT' as type,attendance,title,startdate,clerkid,((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour,venue from ojt join participateojt on ojt.id = participateojt.ojtid where userid = '$userid' and startdate between '$startdate' and '$enddate';";
		$query = mysqli_query($conn, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			if ($row['attendance'] == 'COMPLETED') {
				$status = '<span class="label label-pill label-success">' . $row['attendance'] . '</span>';
				$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
				$custom_sort = 3;
			} else if ($row['attendance'] == '' && $row['type'] == 'PUBLIC/INHOUSE') {
				$status = '<span class="label label-pill label-warning">WAITING</span>';
				$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm attendance" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill in Attendance</button>';
				$totalhour = 0;
				$custom_sort = 2;
			} else if ($row['attendance'] == '' && $row['type'] == 'OJT') {
				$status = '<span class="label label-pill label-warning">WAITING</span>';
				$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-warning btn-sm attendance_ojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Fill In Attendance</button>';
				$totalhour = 0;
				$custom_sort = 1;
			} else if ($row['attendance'] == 'ABSENT') {
				$status = '<span class="label label-pill label-danger">' . $row['attendance'] . '</span>';
				$totalhour = 0;
				$custom_sort = 4;
			} else if ($row['attendance'] == 'COMPLETEDOJT') {
				if ($row['clerkid'] == '0') {
					$status = '<span class="label label-pill label-success">COMPLETED</span>';
					$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button><button type="submit" id="' . $row['id'] . '" class="btn btn-danger btn-sm deleteownojt" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>';
					$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
					$custom_sort = 3;
				} else {
					$status = '<span class="label label-pill label-success">COMPLETED</span>';
					$btnedit = '<button type="submit" id="' . $row['id'] . '" class="btn btn-info btn-sm editownojt" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button>';
					$totalhour = round(($row['totalday'] * $row['totalhour']), 2);
					$custom_sort = 3;
				}
			}

			// PME Status label & Button 
			if ($row['type'] == 'OJT') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-success" style="width: 100px; font-size: 11px; border-radius: 5px;">
									COMPLETED
								</span>
							</div>';
			} else if ($row['status'] == 'pending') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-warning" style="width: 100px; font-size: 11px; border-radius: 5px;">
									PENDING
								</span>
								 <br><small class="text-muted mt-5 d-block">Waiting for HOD evaluation</small>
							</div>';
			} else if ($row['status'] == 'approved') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-info p-2" style="width: 100px; font-size: 11px; border-radius: 5px;">
									APPROVED
								</span>
								<br>
								<a href="edit_pme.php?userid=' . $userid . '&participationid=' . $row['id'] . '" 
								class="btn btn-info btn-sm mt-3" style="margin-top: 5px;">
									<i class="fa fa-eye"></i> View PME
								</a>
							</div>';
			} else if (($row['status']) == 'completed' || ($row['status']) == 'verified') {
				$pme = '<div class="text-center">
								<span class="label label-pill label-success" style="width: 90px; font-size: 11px; border-radius: 5px;">
									COMPLETED
								</span>
							</div>';
			}


			$output[] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'type' => $row['type'],
				'startdate' => $row['startdate'],
				'enddate' => $row['enddate'],
				'venue' => $row['venue'],
				'totalhour' => $totalhour,
				'status' => $status,
				'btnedit' => $btnedit,
				'pme' => $pme,
				'status_sort' => $row['status'] == 'approved' ? 1 : ($row['status'] == 'pending' ? 2 : 3),
				'custom_sort' => $custom_sort
			);
		}
		echo json_encode($output);
	} else if ($_POST["action"] == "viewojtattendance") {
		$ojtid = $_POST["id"];
		$sql = "select * from ojt join participateojt on ojt.id = ojtid where ojtid = '$ojtid';";
		$query = mysqli_query($conn, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			$output[] = array(
				'title' => $row['title'],
				'startdate' => $row['startdate'],
				'enddate' => $row['enddate'],
				'starttime' => date('G:i', strtotime($row['starttime'])),
				'endtime' => date('G:i', strtotime($row['endtime'])),
				'venue' => $row['venue'],
				'trainertype' => $row['trainertype'],
				'trainername' => $row['trainername'],
				'q1' => $row['q1'],
				'q2' => $row['q2'],
				'q3' => $row['q3'],
			);
		}
		echo json_encode($output);
	}
}

function loadStaff()
{
	global $conn;
	$sql = "SELECT staffno,staffname FROM user order by staffno";
	$query = mysqli_query($conn, $sql);
	$options = '<option value="">-- Select Trainer --</option>';
	if (mysqli_num_rows($query) > 0) {
		// output data of each row

		while ($row = mysqli_fetch_assoc($query)) {
			$options .= '<option value="' . $row['staffname'] . '">' . $row['staffno'] . ' - ' . $row['staffname'] . '</option>';
		}
	}
	return $options;
}
?>