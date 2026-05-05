<?php
    include "../../dbconn.php";

    if($_POST["action"]=="load_trainer"){
        echo loadTrainer();
    }else if($_POST["action"]=="load_staff"){
        echo loadStaff();
    }else if($_POST["action"] == "load_training"){
        $output= array();
        $trainid = '';
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        if ($_POST["startdate"] != '') {
            $sql = "select *,totalday*totalhour as sumhour from (select *,((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training where startdate between '$startdate' and '$enddate')tablea;";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];
    
                $query5 = "select count(*) as totalpeople from participation where trainingid = '$trainid'";
                $result5 = mysqli_query($conn, $query5);
                while($row5 = mysqli_fetch_array($result5)) {
                    if ($row5['totalpeople'] != '') {
                        $totalpeople = $row5['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query3 = "select count(*) as totalpeople from participation where trainingid = '$trainid' and attendance = 'COMPLETED'";
                $result3 = mysqli_query($conn, $query3);
                while($row3 = mysqli_fetch_array($result3)) {
                    if ($row3['totalpeople'] != '') {
                        $totalcomplete = $row3['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }

                $percentage = ($totalcomplete/$totalpeople)*100;
                $percentageround = round($percentage,2);
    
                $output[]= array(
                    'id' => $trainid,
                    'trainingcode' => $row['trainingcode'],
                    'title' => $row['title'],
                    'program' => $row['program'],
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => $row['starttime'],
                    'endtime' => $row['endtime'],
                    'cost' => $row['cost'],
                    'hadc' => $row['hadc'],
                    'platform' => $row['platform'],
                    'function' => $row['function'],
                    'participant' => $totalpeople.'<br>('.$percentageround.' %)',
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['sumhour'],
                    'totalmanhour' => $row['totalday'] * $row['totalhour'] * $totalpeople,
                    'btnedit' => '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm viewparticipant" style="margin-left:5px;"><i class="fa fa-search"></i></button><button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i></button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i></button>',
                ); 
            }           
        }else {
            $percentage = '';
            $sql = "select *,totalday*totalhour as sumhour from (select *,((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training)tablea;";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];
    
                $query5 = "select count(*) as totalpeople from participation where trainingid = '$trainid'";
                $result5 = mysqli_query($conn, $query5);
                while($row5 = mysqli_fetch_array($result5)) {
                    if ($row5['totalpeople'] != '') {
                        $totalpeople = $row5['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query3 = "select count(*) as totalpeople from participation where trainingid = '$trainid' and attendance = 'COMPLETED'";
                $result3 = mysqli_query($conn, $query3);
                while($row3 = mysqli_fetch_array($result3)) {
                    if ($row3['totalpeople'] != '') {
                        $totalcomplete = $row3['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }
				
				if ($totalpeople == 0) {
					$percentage = 0;
				}else {
					$percentage = ($totalcomplete/$totalpeople)*100;
				}

                $percentageround = round($percentage,2);
    
                $output[]= array(
                    'id' => $trainid,
                    'trainingcode' => $row['trainingcode'],
                    'title' => $row['title'],
                    'program' => $row['program'],
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => $row['starttime'],
                    'endtime' => $row['endtime'],
                    'cost' => $row['cost'],
                    'hadc' => $row['hadc'],
                    'platform' => $row['platform'],
                    'function' => $row['function'],
                    'participant' => $totalpeople.'<br>('.$percentageround.' %)',
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['sumhour'],
                    'totalmanhour' => $row['totalday'] * $row['totalhour'] * $totalpeople,
                    'btnedit' => '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm viewparticipant" style="margin-left:5px;"><i class="fa fa-search"></i></button><button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i></button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i></button>',
                ); 
            }
        }
        
        echo json_encode($output);
    }else if($_POST["action"] == "fetch_training"){
        $id = $_POST["id"];
        $sql = "SELECT * from training where id = '$id';";
        $query = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }else if($_POST["action"] == "load_ojt"){
        $output= array();
        $trainid = '';
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        if ($_POST["startdate"] != '') {
            $sql = "select *,((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from ojt where startdate between '$startdate' and '$enddate';";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];

                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                while($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance = 'COMPLETED'";
                $result2 = mysqli_query($conn, $query2);
                while($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }

                $percentage = ($totalcomplete/$totalpeople)*100;
                $percentageround = round($percentage,2);

                $output[]= array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => $row['starttime'],
                    'endtime' => $row['endtime'],
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalpeople.'<br>('.$percentageround.' %)',
                    'totalmanhour' => $row['totalday'] * $row['totalhour'] * $row['totalman'],
                    // 'btnmodify' => '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button> <button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>'
                ); 
            }
        }else {
            $sql = "select *,((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from ojt;";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];

                $query1 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result1 = mysqli_query($conn, $query1);
                while($row1 = mysqli_fetch_array($result1)) {
                    if ($row1['totalpeople'] != '') {
                        $totalpeople = $row1['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance = 'COMPLETED'";
                $result2 = mysqli_query($conn, $query2);
                while($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }

                $percentage = ($totalcomplete/$totalpeople)*100;
                $percentageround = round($percentage,2);

                $output[]= array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'startdate' => $row['startdate'],
                    'enddate' => $row['enddate'],
                    'starttime' => $row['starttime'],
                    'endtime' => $row['endtime'],
                    'totalday' => $row['totalday'],
                    'totalhour' => $row['totalhour'],
                    'totalman' => $totalpeople.' ('.$percentageround.' %)',
                    'totalmanhour' => $row['totalday'] * $row['totalhour'] * $row['totalman'],
                    // 'btnmodify' => '<button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> Edit</button> <button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> Delete</button>'
                ); 
            }
        }
        
        echo json_encode($output);
    }else if($_POST["action"] == "fetch_ojt"){
        $id = $_POST["id"];
        $sql = "SELECT * from ojt where id = '$id';";
        $query = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }else if($_POST["action"] == "load_totalsummary"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];

        if ($_POST["startdate"] == '') {
            $sql = "select sum(cost) as totalcost from training;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalcost'] != null) {
					$totalcost = $row['totalcost'];
				}else if ($row['totalcost'] == null) {
					$totalcost = 0;
				}
			}

            $sql = "select sum(totalday) as totaldays from (select ((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday from training)tablea;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totaldays'] != null) {
					$totaldays = $row['totaldays'];
				}else if ($row['totaldays'] == null) {
					$totaldays = 0;
				}
			}

            $sql = "select sum(totalall) as totalhours from (select totalday*totalhour as totalall from (select ((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training)tablea)tableb;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalhours'] != null) {
					$totalhours = $row['totalhours'];
				}else if ($row['totalhours'] == null) {
					$totalhours = 0;
				}
			}

            $sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid group by trainingid)tablea;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalmans'] != null) {
					$totalmans = $row['totalmans'];
				}else if ($row['totalmans'] == null) {
					$totalmans = 0;
				}
			}
        }else {
            $sql = "select sum(cost) as totalcost from training where startdate between '$startdate' and '$enddate';";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalcost'] != null) {
					$totalcost = $row['totalcost'];
				}else if ($row['totalcost'] == null) {
					$totalcost = 0;
				}
			}

            $sql = "select sum(totalday) as totaldays from (select ((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday from training where startdate between '$startdate' and '$enddate')tablea;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totaldays'] != null) {
					$totaldays = $row['totaldays'];
				}else if ($row['totaldays'] == null) {
					$totaldays = 0;
				}
			}

            $sql = "select sum(totalall) as totalhours from (select totalday*totalhour as totalall from (select ((DATEDIFF(enddate, startdate)) - ((WEEK(enddate) - WEEK(startdate)) * 2) - (case when weekday(enddate) = 6 then 1 else 0 end) - (case when weekday(startdate) = 5 then 1 else 0 end) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training where startdate between '$startdate' and '$enddate')tablea)tableb;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalhours'] != null) {
					$totalhours = $row['totalhours'];
				}else if ($row['totalhours'] == null) {
					$totalhours = 0;
				}
			}

            $sql = "select sum(totalman) as totalmans from (select count(*) as totalman from training join participation on training.id = trainingid where startdate between '$startdate' and '$enddate' group by trainingid)tablea;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalmans'] != null) {
					$totalmans = $row['totalmans'];
				}else if ($row['totalmans'] == null) {
					$totalmans = 0;
				}
			}
        }

        $output = ([
            'totalcost' => $totalcost,
            'totaldays' => $totaldays,
            'totalhours' => $totalhours,
            'totalmans' => $totalmans,
        ]);
        echo json_encode($output);
    }else if($_POST["action"] == "load_ojtsummary"){
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];

        if ($_POST["startdate"] == '') {
            $sql = "select sum(totalman) as totalmans from ojt;";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalmans'] != null) {
					$totalmans = $row['totalmans'];
				}else if ($row['totalmans'] == null) {
					$totalmans = 0;
				}
			}
        }else {
            $sql = "select sum(totalman) as totalmans from ojt where startdate between '$startdate' and '$enddate';";
            $query = mysqli_query($conn,$sql);
			while($row = mysqli_fetch_assoc($query))
			{
				if ($row['totalmans'] != null) {
					$totalmans = $row['totalmans'];
				}else if ($row['totalmans'] == null) {
					$totalmans = 0;
				}
			}
        }

        $output = ([
            'totalmans' => $totalmans,
        ]);
        echo json_encode($output);
    }

    function loadTrainer(){
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

    function loadStaff(){
		global $conn;
		$sql = "SELECT staffname FROM user order by staffname";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Staff --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['id'].'">'.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}
?>