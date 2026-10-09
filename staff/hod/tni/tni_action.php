<?php
    session_start();
    include "../../../dbconn.php";
    include_once "../../../planning_year.php";

    // Same gate as tni.php. The department is taken from the logged-in HOD,
    // not from the posted userid, so one HOD cannot save another's TNI.
    if (!isset($_SESSION['fullname'], $_SESSION['id'], $_SESSION['usertype']) || $_SESSION['usertype'] != 'HOD') {
        echo json_encode(['message' => 'error']);
        exit();
    }

    if (isset($_POST['btn_action'])) {
        if ($_POST['btn_action'] == 'addtni') {
            $userid = (int) $_SESSION['id'];
            // TNI is stored under the planning year (rolls over on 1 October).
            $tniYear = (string) ldmsPlanningYear();
            $mandatory = isset($_POST['mandatory']) ? (int) $_POST['mandatory'] : 0;

            $department = '';
            $stmt = $conn->prepare("select department from user where id = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $department = (string) $row['department'];
            }

            if ($department === '') {
                echo json_encode(['message' => 'error']);
                exit();
            }

            // Read and check every row BEFORE touching the table: the save
            // replaces the department's whole TNI for the year, so a bad row
            // must stop it while the old rows are still there. Lengths match
            // the tni column sizes (strict mode rejects longer values).
            $rows = array();
            for ($i = 1; $i <= $mandatory; $i++) {
                $task = strtoupper(trim(isset($_POST['task'.$i]) ? (string) $_POST['task'.$i] : ''));
                if ($task === '') {
                    continue;
                }

                $rows[] = array(
                    'task' => $task,
                    'targetsk' => isset($_POST['targetsk'.$i]) ? (int) $_POST['targetsk'.$i] : 0,
                    'currentsk' => isset($_POST['currentsk'.$i]) ? (int) $_POST['currentsk'.$i] : 0,
                    'cause' => trim(isset($_POST['cause'.$i]) ? (string) $_POST['cause'.$i] : ''),
                    'ask' => trim(isset($_POST['ask'.$i]) ? (string) $_POST['ask'.$i] : ''),
                    'trtype' => strtoupper(trim(isset($_POST['trtype'.$i]) ? (string) $_POST['trtype'.$i] : '')),
                    'evaluate' => trim(isset($_POST['evaluate'.$i]) ? (string) $_POST['evaluate'.$i] : ''),
                );
            }

            foreach ($rows as $r) {
                if (
                    mb_strlen($r['task']) > 500 || mb_strlen($r['cause']) > 100 || mb_strlen($r['ask']) > 100
                    || mb_strlen($r['trtype']) > 100 || mb_strlen($r['evaluate']) > 100
                ) {
                    echo json_encode(['message' => 'toolong']);
                    exit();
                }
            }

            // Delete + re-insert as one transaction: if any insert fails the
            // delete is rolled back and the department keeps its old TNI.
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $conn->begin_transaction();

                $delete = $conn->prepare("delete from tni where department = ? and year = ?");
                $delete->bind_param("ss", $department, $tniYear);
                $delete->execute();

                $insert = $conn->prepare("insert into tni (training,expected,actual,gap,cause,ask,method,evaluation,department,year) values (?,?,?,?,?,?,?,?,?,?)");
                foreach ($rows as $r) {
                    $gap = $r['targetsk'] - $r['currentsk'];
                    $insert->bind_param("siiissssss", $r['task'], $r['targetsk'], $r['currentsk'], $gap, $r['cause'], $r['ask'], $r['trtype'], $r['evaluate'], $department, $tniYear);
                    $insert->execute();
                }

                $conn->commit();
                echo json_encode(['message' => 'insert']);
            } catch (Throwable $e) {
                $conn->rollback();
                echo json_encode(['message' => 'error']);
            }
        }
    }
?>
