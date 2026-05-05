<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../dbconn.php";

if (isset($_POST['btn_action'])) {
    if ($_POST['btn_action'] == 'addtna') {
        $userid = $_POST['userid'];
        $esgaware = $_POST['esgaware'];
        $selfaware = $_POST['selfaware'];
        $leadaware = $_POST['leadaware'];
        $dataaware = $_POST['dataaware'];
        $functional = $_POST['functional'];
        $busiaware = $_POST['busiaware'];
        $special = $_POST['special'];

        $sql = "delete from tna where userid = '$userid' and year = '2023';";
        mysqli_query($conn, $sql);

        for ($i = 1; $i <= $esgaware; $i++) {
            $taskes = '';
            $traininges = '';
            $otres = '';
            $targetskes = '';
            $currentskes = '';
            $gapes = '';
            $trtypees = '';
            $datetres = '';

            if (isset($_POST['taskes' . $i])) {
                $taskes = strtoupper($_POST['taskes' . $i]);
            }

            if (isset($_POST['traininges' . $i])) {
                $traininges = strtoupper($_POST['traininges' . $i]);
            }

            if (isset($_POST['otres' . $i])) {
                $otres = strtoupper(str_replace("'", "''", $_POST['otres' . $i]));
            }

            if (isset($_POST['targetskes' . $i])) {
                $targetskes = strtoupper($_POST['targetskes' . $i]);
            }

            if (isset($_POST['currentskes' . $i])) {
                $currentskes = strtoupper($_POST['currentskes' . $i]);
            }

            if (isset($_POST['gapes' . $i])) {
                $gapes = strtoupper($_POST['gapes' . $i]);
            }

            if (isset($_POST['trtypees' . $i])) {
                $trtypees = strtoupper($_POST['trtypees' . $i]);
            }

            if (isset($_POST['datetres' . $i])) {
                $datetres = $_POST['datetres' . $i];
            }

            if ($taskes != '' && $traininges != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskes','$traininges','$otres','$targetskes','$currentskes','$gapes','$trtypees','$datetres','1','esgaware','2023','$userid')";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $selfaware; $i++) {
            $taskse = '';
            $trainingse = '';
            $otrse = '';
            $targetskse = '';
            $currentskse = '';
            $gapse = '';
            $trtypese = '';
            $datetrse = '';

            if (isset($_POST['taskse' . $i])) {
                $taskse = strtoupper($_POST['taskse' . $i]);
            }

            if (isset($_POST['trainingse' . $i])) {
                $trainingse = strtoupper($_POST['trainingse' . $i]);
            }

            if (isset($_POST['otrse' . $i])) {
                $otrse = strtoupper($_POST['otrse' . $i]);
            }

            if (isset($_POST['targetskse' . $i])) {
                $targetskse = strtoupper($_POST['targetskse' . $i]);
            }

            if (isset($_POST['currentskse' . $i])) {
                $currentskse = strtoupper($_POST['currentskse' . $i]);
            }

            if (isset($_POST['gapse' . $i])) {
                $gapse = strtoupper($_POST['gapse' . $i]);
            }

            if (isset($_POST['trtypese' . $i])) {
                $trtypese = strtoupper($_POST['trtypese' . $i]);
            }

            if (isset($_POST['datetrse' . $i])) {
                $datetrse = $_POST['datetrse' . $i];
            }

            if ($taskse != '' && $trainingse != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskse','$trainingse','$otrse','$targetskse','$currentskse','$gapse','$trtypese','$datetrse','1','selfaware','2023','$userid')";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $leadaware; $i++) {
            $taskle = '';
            $trainingle = '';
            $otrle = '';
            $targetskle = '';
            $currentskle = '';
            $gaple = '';
            $trtypele = '';
            $datetrle = '';

            if (isset($_POST['taskle' . $i])) {
                $taskle = strtoupper($_POST['taskle' . $i]);
            }

            if (isset($_POST['trainingle' . $i])) {
                $trainingle = strtoupper($_POST['trainingle' . $i]);
            }

            if (isset($_POST['otrle' . $i])) {
                $otrle = strtoupper($_POST['otrle' . $i]);
            }

            if (isset($_POST['targetskle' . $i])) {
                $targetskle = strtoupper($_POST['targetskle' . $i]);
            }

            if (isset($_POST['currentskle' . $i])) {
                $currentskle = strtoupper($_POST['currentskle' . $i]);
            }

            if (isset($_POST['gaple' . $i])) {
                $gaple = strtoupper($_POST['gaple' . $i]);
            }

            if (isset($_POST['trtypele' . $i])) {
                $trtypele = strtoupper($_POST['trtypele' . $i]);
            }

            if (isset($_POST['datetrle' . $i])) {
                $datetrle = $_POST['datetrle' . $i];
            }

            if ($taskle != '' && $trainingle != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskle','$trainingle','$otrle','$targetskle','$currentskle','$gaple','$trtypele','$datetrle','1','leadaware','2023','$userid')";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $dataaware; $i++) {
            $taskda = '';
            $trainingda = '';
            $otrda = '';
            $targetskda = '';
            $currentskda = '';
            $gapda = '';
            $trtypeda = '';
            $datetrda = '';

            if (isset($_POST['taskda' . $i])) {
                $taskda = strtoupper($_POST['taskda' . $i]);
            }

            if (isset($_POST['trainingda' . $i])) {
                $trainingda = strtoupper($_POST['trainingda' . $i]);
            }

            if (isset($_POST['otrda' . $i])) {
                $otrda = strtoupper(str_replace("'", "''", $_POST['otrda' . $i]));
            }

            if (isset($_POST['targetskda' . $i])) {
                $targetskda = strtoupper($_POST['targetskda' . $i]);
            }

            if (isset($_POST['currentskda' . $i])) {
                $currentskda = strtoupper($_POST['currentskda' . $i]);
            }

            if (isset($_POST['gapda' . $i])) {
                $gapda = strtoupper($_POST['gapda' . $i]);
            }

            if (isset($_POST['trtypeda' . $i])) {
                $trtypeda = strtoupper($_POST['trtypeda' . $i]);
            }

            if (isset($_POST['datetrda' . $i])) {
                $datetrda = $_POST['datetrda' . $i];
            }

            if ($taskda != '' && $trainingda != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskda','$trainingda','$otrda','$targetskda','$currentskda','$gapda','$trtypeda','$datetrda','1','dataaware','2023','$userid')";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $functional; $i++) {
            $taskfu = '';
            $trainingfu = '';
            $otrfu = '';
            $targetskfu = '';
            $currentskfu = '';
            $gapfu = '';
            $trtypefu = '';
            $datetrfu = '';

            if (isset($_POST['taskfu' . $i])) {
                $taskfu = strtoupper($_POST['taskfu' . $i]);
            }

            if (isset($_POST['trainingfu' . $i])) {
                $trainingfu = strtoupper($_POST['trainingfu' . $i]);
            }

            if (isset($_POST['otrfu' . $i])) {
                $otrfu = strtoupper($_POST['otrfu' . $i]);
            }

            if (isset($_POST['targetskfu' . $i])) {
                $targetskfu = strtoupper($_POST['targetskfu' . $i]);
            }

            if (isset($_POST['currentskfu' . $i])) {
                $currentskfu = strtoupper($_POST['currentskfu' . $i]);
            }

            if (isset($_POST['gapfu' . $i])) {
                $gapfu = strtoupper($_POST['gapfu' . $i]);
            }

            if (isset($_POST['trtypefu' . $i])) {
                $trtypefu = strtoupper($_POST['trtypefu' . $i]);
            }

            if (isset($_POST['datetrfu' . $i])) {
                $datetrfu = $_POST['datetrfu' . $i];
            }

            if ($taskfu != '' && $trainingfu != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskfu','$trainingfu','$otrfu','$targetskfu','$currentskfu','$gapfu','$trtypefu','$datetrfu','1','functional','2023','$userid')";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $busiaware; $i++) {
            $taskbu = '';
            $trainingbu = '';
            $otrbu = '';
            $targetskbu = '';
            $currentskbu = '';
            $gapbu = '';
            $trtypebu = '';
            $datetrbu = '';

            if (isset($_POST['taskbu' . $i])) {
                $taskbu = strtoupper($_POST['taskbu' . $i]);
            }

            if (isset($_POST['trainingbu' . $i])) {
                $trainingbu = strtoupper($_POST['trainingbu' . $i]);
            }

            if (isset($_POST['otrbu' . $i])) {
                $otrbu = strtoupper($_POST['otrbu' . $i]);
            }

            if (isset($_POST['targetskbu' . $i])) {
                $targetskbu = strtoupper($_POST['targetskbu' . $i]);
            }

            if (isset($_POST['currentskbu' . $i])) {
                $currentskbu = strtoupper($_POST['currentskbu' . $i]);
            }

            if (isset($_POST['gapbu' . $i])) {
                $gapbu = strtoupper($_POST['gapbu' . $i]);
            }

            if (isset($_POST['trtypebu' . $i])) {
                $trtypebu = strtoupper($_POST['trtypebu' . $i]);
            }

            if (isset($_POST['datetrbu' . $i])) {
                $datetrbu = $_POST['datetrbu' . $i];
            }

            if ($taskbu != '' && $trainingbu != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$taskbu','$trainingbu','$otrbu','$targetskbu','$currentskbu','$gapbu','$trtypebu','$datetrbu','1','busiaware','2023','$userid')";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $special; $i++) {
            $tasksp = '';
            $trainingsp = '';
            $otrsp = '';
            $targetsksp = '';
            $currentskbu = '';
            $gapsp = '';
            $trtypesp = '';
            $datetrsp = '';

            if (isset($_POST['tasksp' . $i])) {
                $tasksp = strtoupper($_POST['tasksp' . $i]);
            }

            if (isset($_POST['trainingsp' . $i])) {
                $trainingsp = strtoupper($_POST['trainingsp' . $i]);
            }

            if (isset($_POST['otrsp' . $i])) {
                $otrsp = strtoupper($_POST['otrsp' . $i]);
            }

            if (isset($_POST['targetsksp' . $i])) {
                $targetsksp = strtoupper($_POST['targetsksp' . $i]);
            }

            if (isset($_POST['currentskbu' . $i])) {
                $currentskbu = strtoupper($_POST['currentskbu' . $i]);
            }

            if (isset($_POST['gapsp' . $i])) {
                $gapsp = strtoupper($_POST['gapsp' . $i]);
            }

            if (isset($_POST['trtypesp' . $i])) {
                $trtypesp = strtoupper($_POST['trtypesp' . $i]);
            }

            if (isset($_POST['datetrsp' . $i])) {
                $datetrsp = $_POST['datetrsp' . $i];
            }

            if ($tasksp != '' && $trainingsp != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,status,section,year,userid) values ('$tasksp','$trainingsp','$otrsp','$targetsksp','$currentskbu','$gapsp','$trtypesp','$datetrsp','1','special','2023','$userid')";
                mysqli_query($conn, $sql);
            }
        }

        // echo json_encode(['message' => 'insert']);
          echo json_encode(['message' => 'insert', 'userid' => $userid]);
    } else if ($_POST['btn_action'] == 'approvetna') {
        $userid = $_POST['userid'];
        $esgaware = $_POST['esgaware'];
        $selfaware = $_POST['selfaware'];
        $leadaware = $_POST['leadaware'];
        $functional = $_POST['functional'];
        $busiaware = $_POST['busiaware'];
        $special = $_POST['special'];

        $sql = "delete from tna where userid = '$userid' and year = '2023';";
        mysqli_query($conn, $sql);

        for ($i = 1; $i <= $esgaware; $i++) {
            $taskes = '';
            $traininges = '';
            $otres = '';
            $targetskes = '';
            $currentskes = '';
            $gapes = '';
            $trtypees = '';
            $datetres = '';

            if (isset($_POST['taskes' . $i])) {
                $taskes = strtoupper($_POST['taskes' . $i]);
            }

            if (isset($_POST['traininges' . $i])) {
                $traininges = strtoupper($_POST['traininges' . $i]);
            }

            if (isset($_POST['otres' . $i])) {
                $otres = strtoupper($_POST['otres' . $i]);
            }

            if (isset($_POST['targetskes' . $i])) {
                $targetskes = strtoupper($_POST['targetskes' . $i]);
            }

            if (isset($_POST['currentskes' . $i])) {
                $currentskes = strtoupper($_POST['currentskes' . $i]);
            }

            if (isset($_POST['gapes' . $i])) {
                $gapes = strtoupper($_POST['gapes' . $i]);
            }

            if (isset($_POST['trtypees' . $i])) {
                $trtypees = strtoupper($_POST['trtypees' . $i]);
            }

            if (isset($_POST['datetres' . $i])) {
                $datetres = $_POST['datetres' . $i];
            }

            if ($taskes != '' && $traininges != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$taskes','$traininges','$otres','$targetskes','$currentskes','$gapes','$trtypees','$datetres','esgaware','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $selfaware; $i++) {
            $taskse = '';
            $trainingse = '';
            $otrse = '';
            $targetskse = '';
            $currentskse = '';
            $gapse = '';
            $trtypese = '';
            $datetrse = '';

            if (isset($_POST['taskse' . $i])) {
                $taskse = strtoupper($_POST['taskse' . $i]);
            }

            if (isset($_POST['trainingse' . $i])) {
                $trainingse = strtoupper($_POST['trainingse' . $i]);
            }

            if (isset($_POST['otrse' . $i])) {
                $otrse = strtoupper($_POST['otrse' . $i]);
            }

            if (isset($_POST['targetskse' . $i])) {
                $targetskse = strtoupper($_POST['targetskse' . $i]);
            }

            if (isset($_POST['currentskse' . $i])) {
                $currentskse = strtoupper($_POST['currentskse' . $i]);
            }

            if (isset($_POST['gapse' . $i])) {
                $gapse = strtoupper($_POST['gapse' . $i]);
            }

            if (isset($_POST['trtypese' . $i])) {
                $trtypese = strtoupper($_POST['trtypese' . $i]);
            }

            if (isset($_POST['datetrse' . $i])) {
                $datetrse = $_POST['datetrse' . $i];
            }

            if ($taskse != '' && $trainingse != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$taskse','$trainingse','$otrse','$targetskse','$currentskse','$gapse','$trtypese','$datetrse','selfaware','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $leadaware; $i++) {
            $taskle = '';
            $trainingle = '';
            $otrle = '';
            $targetskle = '';
            $currentskle = '';
            $gaple = '';
            $trtypele = '';
            $datetrle = '';

            if (isset($_POST['taskle' . $i])) {
                $taskle = strtoupper($_POST['taskle' . $i]);
            }

            if (isset($_POST['trainingle' . $i])) {
                $trainingle = strtoupper($_POST['trainingle' . $i]);
            }

            if (isset($_POST['otrle' . $i])) {
                $otrle = strtoupper($_POST['otrle' . $i]);
            }

            if (isset($_POST['targetskle' . $i])) {
                $targetskle = strtoupper($_POST['targetskle' . $i]);
            }

            if (isset($_POST['currentskle' . $i])) {
                $currentskle = strtoupper($_POST['currentskle' . $i]);
            }

            if (isset($_POST['gaple' . $i])) {
                $gaple = strtoupper($_POST['gaple' . $i]);
            }

            if (isset($_POST['trtypele' . $i])) {
                $trtypele = strtoupper($_POST['trtypele' . $i]);
            }

            if (isset($_POST['datetrle' . $i])) {
                $datetrle = $_POST['datetrle' . $i];
            }

            if ($taskle != '' && $trainingle != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$taskle','$trainingle','$otrle','$targetskle','$currentskle','$gaple','$trtypele','$datetrle','leadaware','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $dataaware; $i++) {
            $taskda = '';
            $trainingda = '';
            $otrda = '';
            $targetskda = '';
            $currentskda = '';
            $gapda = '';
            $trtypeda = '';
            $datetrda = '';

            if (isset($_POST['taskda' . $i])) {
                $taskda = strtoupper($_POST['taskda' . $i]);
            }

            if (isset($_POST['trainingda' . $i])) {
                $trainingda = strtoupper($_POST['trainingda' . $i]);
            }

            if (isset($_POST['otrda' . $i])) {
                $otrda = strtoupper(str_replace("'", "''", $_POST['otrda' . $i]));
            }

            if (isset($_POST['targetskda' . $i])) {
                $targetskda = strtoupper($_POST['targetskda' . $i]);
            }

            if (isset($_POST['currentskda' . $i])) {
                $currentskda = strtoupper($_POST['currentskda' . $i]);
            }

            if (isset($_POST['gapda' . $i])) {
                $gapda = strtoupper($_POST['gapda' . $i]);
            }

            if (isset($_POST['trtypeda' . $i])) {
                $trtypeda = strtoupper($_POST['trtypeda' . $i]);
            }

            if (isset($_POST['datetrda' . $i])) {
                $datetrda = $_POST['datetrda' . $i];
            }

            if ($taskda != '' && $trainingda != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid) values ('$taskda','$trainingda','$otrda','$targetskda','$currentskda','$gapda','$trtypeda','$datetrda','dataaware','2023','$userid', 'APPROVE', curdate())";
                mysqli_query($conn, $sql);

            }
        }

        for ($i = 1; $i <= $functional; $i++) {
            $taskfu = '';
            $trainingfu = '';
            $otrfu = '';
            $targetskfu = '';
            $currentskfu = '';
            $gapfu = '';
            $trtypefu = '';
            $datetrfu = '';

            if (isset($_POST['taskfu' . $i])) {
                $taskfu = strtoupper($_POST['taskfu' . $i]);
            }

            if (isset($_POST['trainingfu' . $i])) {
                $trainingfu = strtoupper($_POST['trainingfu' . $i]);
            }

            if (isset($_POST['otrfu' . $i])) {
                $otrfu = strtoupper($_POST['otrfu' . $i]);
            }

            if (isset($_POST['targetskfu' . $i])) {
                $targetskfu = strtoupper($_POST['targetskfu' . $i]);
            }

            if (isset($_POST['currentskfu' . $i])) {
                $currentskfu = strtoupper($_POST['currentskfu' . $i]);
            }

            if (isset($_POST['gapfu' . $i])) {
                $gapfu = strtoupper($_POST['gapfu' . $i]);
            }

            if (isset($_POST['trtypefu' . $i])) {
                $trtypefu = strtoupper($_POST['trtypefu' . $i]);
            }

            if (isset($_POST['datetrfu' . $i])) {
                $datetrfu = $_POST['datetrfu' . $i];
            }

            if ($taskfu != '' && $trainingfu != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$taskfu','$trainingfu','$otrfu','$targetskfu','$currentskfu','$gapfu','$trtypefu','$datetrfu','functional','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $busiaware; $i++) {
            $taskbu = '';
            $trainingbu = '';
            $otrbu = '';
            $targetskbu = '';
            $currentskbu = '';
            $gapbu = '';
            $trtypebu = '';
            $datetrbu = '';

            if (isset($_POST['taskbu' . $i])) {
                $taskbu = strtoupper($_POST['taskbu' . $i]);
            }

            if (isset($_POST['trainingbu' . $i])) {
                $trainingbu = strtoupper($_POST['trainingbu' . $i]);
            }

            if (isset($_POST['otrbu' . $i])) {
                $otrbu = strtoupper($_POST['otrbu' . $i]);
            }

            if (isset($_POST['targetskbu' . $i])) {
                $targetskbu = strtoupper($_POST['targetskbu' . $i]);
            }

            if (isset($_POST['currentskbu' . $i])) {
                $currentskbu = strtoupper($_POST['currentskbu' . $i]);
            }

            if (isset($_POST['gapbu' . $i])) {
                $gapbu = strtoupper($_POST['gapbu' . $i]);
            }

            if (isset($_POST['trtypebu' . $i])) {
                $trtypebu = strtoupper($_POST['trtypebu' . $i]);
            }

            if (isset($_POST['datetrbu' . $i])) {
                $datetrbu = $_POST['datetrbu' . $i];
            }

            if ($taskbu != '' && $trainingbu != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$taskbu','$trainingbu','$otrbu','$targetskbu','$currentskbu','$gapbu','$trtypebu','$datetrbu','busiaware','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);
            }
        }

        for ($i = 1; $i <= $special; $i++) {
            $tasksp = '';
            $trainingsp = '';
            $otrsp = '';
            $targetsksp = '';
            $currentskbu = '';
            $gapsp = '';
            $trtypesp = '';
            $datetrsp = '';

            if (isset($_POST['tasksp' . $i])) {
                $tasksp = strtoupper($_POST['tasksp' . $i]);
            }

            if (isset($_POST['trainingsp' . $i])) {
                $trainingsp = strtoupper($_POST['trainingsp' . $i]);
            }

            if (isset($_POST['otrsp' . $i])) {
                $otrsp = strtoupper($_POST['otrsp' . $i]);
            }

            if (isset($_POST['targetsksp' . $i])) {
                $targetsksp = strtoupper($_POST['targetsksp' . $i]);
            }

            if (isset($_POST['currentskbu' . $i])) {
                $currentskbu = strtoupper($_POST['currentskbu' . $i]);
            }

            if (isset($_POST['gapsp' . $i])) {
                $gapsp = strtoupper($_POST['gapsp' . $i]);
            }

            if (isset($_POST['trtypesp' . $i])) {
                $trtypesp = strtoupper($_POST['trtypesp' . $i]);
            }

            if (isset($_POST['datetrsp' . $i])) {
                $datetrsp = $_POST['datetrsp' . $i];
            }

            if ($tasksp != '' && $trainingsp != '') {
                $sql = "insert into tna (task,training,othertr,targetskill,currentskill,gap,trainingtype,monthapply,section,year,userid,status,dateapprove) values ('$tasksp','$trainingsp','$otrsp','$targetsksp','$currentskbu','$gapsp','$trtypesp','$datetrsp','special','2023','$userid','APPROVE',curdate())";
                mysqli_query($conn, $sql);
            }
        }

        echo json_encode(['message' => 'insertapp']);
    }
}
?>