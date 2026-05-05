<?php

include "../../../dbconn.php";

if (isset($_POST['action'])) {
	if ($_POST["action"] == "gettna") {
		$userid = $_POST["userid"];

		// $sql = "select count(*) as tnarecord,status from tna where userid = '$userid' and year = '2023';";
		$sql = "SELECT status, COUNT(*) AS tnarecord FROM tna WHERE userid = '$userid' AND year = '2023' GROUP BY status;";
		$query = mysqli_query($conn, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			$tna = $row['tnarecord'];
			$status = $row['status'];
		}

		$sql1 = "select ifnull(sum(totalday*totalhour),0) as sumhour from (select ((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training join participation on training.id = trainingid where userid = '$userid' and year(startdate) = '2023')tablea;";
		$query1 = mysqli_query($conn, $sql1);
		while ($row1 = mysqli_fetch_assoc($query1)) {
			$publichour = $row1['sumhour'];
		}

		$sql2 = "select ifnull(sum(totalday*totalhour),0) as sumhour from (select ((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from ojt join participateojt on ojt.id = ojtid where userid = '$userid' and year(startdate) = '2023')tablea;";
		$query2 = mysqli_query($conn, $sql2);
		while ($row2 = mysqli_fetch_assoc($query2)) {
			$ojthour = $row2['sumhour'];
		}

		$output[] = array(
			'tna' => $tna,
			'status' => $status,
			'publichour' => $publichour,
			'ojthour' => $ojthour,
			'totalhour' => $publichour + $ojthour,
		);

		echo json_encode($output);
	} else if ($_POST["action"] == "getlisttna") {
		$userid = $_POST["userid"];

		$sql = "select * from tna where userid = '$userid' and year = '2023';";
		$query = mysqli_query($conn, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			$output[] = array(
				'task' => $row['task'],
				'training' => $row['training'],
				'othertr' => $row['othertr'],
				'targetskill' => $row['targetskill'],
				'currentskill' => $row['currentskill'],
				'gap' => $row['gap'],
				'trainingtype' => $row['trainingtype'],
				'monthapply' => $row['monthapply'],
				'section' => $row['section'],
				'status' => $row['status'],
			);
		}

		echo json_encode($output);
	}
}