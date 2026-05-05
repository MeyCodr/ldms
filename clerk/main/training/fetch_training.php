<?php
    include "../../../dbconn.php";

    if($_POST["action"]=="load_staff"){
        echo loadStaff();
    }else if($_POST["action"]=="load_contract"){
        echo loadContract();
    }else if($_POST["action"]=="load_party"){
        echo loadParty();
    }else if($_POST["action"] == "load_ojt"){
        $userid = $_POST["userid"];
        $output= array();
        $trainid = '';
        $startdate = $_POST["startdate"];
        $enddate = $_POST["enddate"];
        if ($_POST["startdate"] != '') {
            $sql = "select * from ojt where id in (select distinct(ojtid) as id from participateojt where clerkid = '$userid') and startdate between '$startdate' and '$enddate';";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];
    
                $query5 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result5 = mysqli_query($conn, $query5);
                while($row5 = mysqli_fetch_array($result5)) {
                    if ($row5['totalpeople'] != '') {
                        $totalpeople = $row5['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance = 'COMPLETEDOJT'";
                $result2 = mysqli_query($conn, $query2);
                while($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete/$totalpeople)*100;
                }else {
                    $percentage = 0;
                }
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
                    'trainername' => $row['trainername'],
                    'totalman' => $totalpeople.'<br>('.$percentageround.' %)',
                    'btnmodify' => '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm view" style="margin-left:5px;"><i class="fa fa-search"></i> </button><button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> </button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>',
                ); 
            }
        }else {
            $sql = "select * from ojt where id in (select distinct(ojtid) as id from participateojt where clerkid = '$userid');";
            $query = mysqli_query($conn,$sql);
            while ($row = mysqli_fetch_assoc($query))
            {
                $trainid = $row['id'];
    
                $query5 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid'";
                $result5 = mysqli_query($conn, $query5);
                while($row5 = mysqli_fetch_array($result5)) {
                    if ($row5['totalpeople'] != '') {
                        $totalpeople = $row5['totalpeople'];
                    }else {
                        $totalpeople = 0;
                    }
                }

                $query2 = "select sum(totalman) as totalpeople from participateojt where ojtid = '$trainid' and attendance = 'COMPLETEDOJT'";
                $result2 = mysqli_query($conn, $query2);
                while($row2 = mysqli_fetch_array($result2)) {
                    if ($row2['totalpeople'] != '') {
                        $totalcomplete = $row2['totalpeople'];
                    }else {
                        $totalcomplete = 0;
                    }
                }

                if ($totalpeople != '0') {
                    $percentage = ($totalcomplete/$totalpeople)*100;
                }else {
                    $percentage = 0;
                }
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
                    'trainername' => $row['trainername'],
                    'totalman' => $totalpeople.'<br>('.$percentageround.' %)',
                    'btnmodify' => '<button type="submit" id="'.$row['id'].'" class="btn btn-info btn-sm view" style="margin-left:5px;"><i class="fa fa-search"></i> </button><button type="submit" id="'.$row['id'].'" class="btn btn-warning btn-sm edit" style="margin-left:5px;"><i class="fa fa-edit"></i> </button><button type="submit" id="'.$row['id'].'" class="btn btn-danger btn-sm delete" style="margin-left:5px;"><i class="fa fa-trash"></i> </button>',
                ); 
            }
        }
        
        echo json_encode($output);
    }else if($_POST["action"] == "fetch_training"){
        $id = $_POST["id"];
        $output= array();
        $sql = "select id,title,startdate,enddate,starttime,endtime,venue,trainertype,trainername,staffname,designation,department from ojt join (select ojtid,staffname,designation,participateojt.department from participateojt join user on userid = user.id where ojtid = '$id')tableb on ojt.id = tableb.ojtid order by designation desc;";
        $query = mysqli_query($conn,$sql);
        while($row = mysqli_fetch_assoc($query))
        {
            $sql1 = "select ifnull(sum(totalman),0) as totalperm from participateojt join user on userid = user.id where ojtid = '$id' and designation != 'CONTRACT'";
            $query1 = mysqli_query($conn,$sql1);
            while($row1 = mysqli_fetch_assoc($query1))
            {
                $totalperm = $row1['totalperm'];
            }

            $sql2 = "select ifnull(sum(totalman),0) as totalcon from participateojt join user on userid = user.id where ojtid = '$id' and designation = 'CONTRACT'";
            $query2 = mysqli_query($conn,$sql2);
            while($row2 = mysqli_fetch_assoc($query2))
            {
                $totalcontract = $row2['totalcon'];
            }

            $output[]= array(
                'title' => $row['title'],
                'startdate' => $row['startdate'],
                'enddate' => $row['enddate'],
                'starttime' => $row['starttime'],
                'endtime' => $row['endtime'],
                'venue' => $row['venue'],
                'trainertype' => $row['trainertype'],
                'designation' => $row['designation'],
                'totaluser' => $totalperm,
                'totalcontract' => $totalcontract,
                'trainername' => $row['trainername'],
                'staffname' => $row['staffname'],
                'department' => $row['department'],
            );
        }
        echo json_encode($output);
    }else if($_POST["action"] == "load_ojtsummary"){
        $clerkid = $_POST["userid"];
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

        if ($startdate != '') {
            $sql = "select sum(totalman) as totalmans from ojt join (select ojtid,clerkid from participateojt group by ojtid)tablea on ojt.id = tablea.ojtid where clerkid = '$clerkid' and startdate between '$startdate' and '$enddate';";
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
            $sql = "select sum(totalman) as totalmans from participateojt where clerkid = '$clerkid';";
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

    function loadStaff(){
		global $conn;
		$sql = "SELECT staffno,staffname FROM user order by staffno";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Trainer --</option>';
		if (mysqli_num_rows($query) > 0) {
		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['staffname'].'">'.$row['staffno'].' - '.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}

    function loadParty(){
		global $conn;
		$sql = "SELECT staffno,staffname FROM user where designation != 'CONTRACT' order by staffno";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Permanent Staff --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['staffname'].'">'.$row['staffno'].' - '.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}

    function loadContract(){
		global $conn;
		$sql = "SELECT staffno,staffname FROM user where designation = 'CONTRACT' order by staffno";
		$query = mysqli_query($conn,$sql);
		$options = '<option value="">-- Select Contract staff --</option>';
		if (mysqli_num_rows($query) > 0) {
		  // output data of each row

		  while($row = mysqli_fetch_assoc($query)) {
			$options.= '<option value="'.$row['staffname'].'">'.$row['staffno'].' - '.$row['staffname'].'</option>';
		  }
		}
		return $options;
	}
?>
