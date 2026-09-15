<?php
include "../../../dbconn.php";
require '../../../asset/vendor/autoload.php';

date_default_timezone_set("Asia/Kuala_Lumpur");
header('Content-Type: application/json; charset=utf-8');

if (empty($_FILES["import_file"]["name"])) {
    echo json_encode(['message' => 'error', 'errors' => ['No file was uploaded.']]);
    exit;
}

$fileName = $_FILES['import_file']['name'];
$file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$clerkid = (int) $_POST['clerkid'];

$allowed_ext = ['xls', 'csv', 'xlsx'];
if (!in_array($file_ext, $allowed_ext, true)) {
    echo json_encode(['message' => 'error', 'errors' => ['Unsupported file type. Upload .xls, .xlsx or .csv.']]);
    exit;
}

$inputFileNamePath = $_FILES['import_file']['tmp_name'];
try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
} catch (\Throwable $e) {
    echo json_encode(['message' => 'error', 'errors' => ['Could not read the file: ' . $e->getMessage()]]);
    exit;
}

$data_rows = $spreadsheet->getSheet(0)->toArray();
array_shift($data_rows); // header row

// ===== PASS 1: parse and structurally validate every row =====
$parsed = [];
$errors = [];
$staffnos = [];
$lineNo = 1; // the header was line 1

foreach ($data_rows as $row_day) {
    $lineNo++;

    $isBlank = true;
    foreach ($row_day as $cell) {
        if (trim((string) $cell) !== '') { $isBlank = false; break; }
    }
    if ($isBlank) {
        continue;
    }

    $title = strtoupper(trim((string) ($row_day[0] ?? '')));
    $venue = strtoupper(trim((string) ($row_day[1] ?? '')));
    $startdateRaw = trim((string) ($row_day[2] ?? ''));
    $enddateRaw = trim((string) ($row_day[3] ?? ''));
    $starttimeRaw = trim((string) ($row_day[4] ?? ''));
    $endtimeRaw = trim((string) ($row_day[5] ?? ''));
    $trainertype = strtoupper(trim((string) ($row_day[6] ?? '')));
    $trainername = strtoupper(trim((string) ($row_day[7] ?? '')));
    $staffno = strtoupper(trim((string) ($row_day[8] ?? '')));

    $rowErrors = [];
    if ($title === '') $rowErrors[] = 'Title is blank';

    $startdateTs = $startdateRaw !== '' ? strtotime($startdateRaw) : false;
    $enddateTs = $enddateRaw !== '' ? strtotime($enddateRaw) : false;
    if ($startdateTs === false) $rowErrors[] = 'Start Date is missing or not a valid date';
    if ($enddateTs === false) $rowErrors[] = 'End Date is missing or not a valid date';

    $starttimeTs = $starttimeRaw !== '' ? strtotime($starttimeRaw) : false;
    $endtimeTs = $endtimeRaw !== '' ? strtotime($endtimeRaw) : false;
    if ($starttimeTs === false) $rowErrors[] = 'Start Time is missing or not a valid time';
    if ($endtimeTs === false) $rowErrors[] = 'End Time is missing or not a valid time';

    if (!in_array($trainertype, ['INTERNAL', 'EXTERNAL'], true)) $rowErrors[] = 'Trainer Type must be INTERNAL or EXTERNAL';
    if ($trainername === '') $rowErrors[] = 'Trainer Name is blank';
    if ($staffno === '') $rowErrors[] = 'Participant Staff No is blank';

    // Feedback columns (J-L) are optional per row - a row with none of them
    // filled stays pending, same as before this feature existed, so the
    // participant fills in their own attendance form later. Once any one of
    // them is filled, all three are required so we never mark a row
    // COMPLETEDOJT with a half-answered questionnaire.
    $fbLearn = trim((string) ($row_day[9] ?? ''));
    $fbBeforeRaw = trim((string) ($row_day[10] ?? ''));
    $fbAfterRaw = trim((string) ($row_day[11] ?? ''));
    $fbFilledCount = ($fbLearn !== '' ? 1 : 0) + ($fbBeforeRaw !== '' ? 1 : 0) + ($fbAfterRaw !== '' ? 1 : 0);
    $feedbackComplete = false;
    $fbBefore = null;
    $fbAfter = null;

    if ($fbFilledCount > 0 && $fbFilledCount < 3) {
        $rowErrors[] = 'What Did You Learn / Skill Before Training / Skill After Training must be all filled in or all left blank';
    } elseif ($fbFilledCount === 3) {
        if (!ctype_digit($fbBeforeRaw) || (int) $fbBeforeRaw < 1 || (int) $fbBeforeRaw > 5) {
            $rowErrors[] = 'Skill Before Training must be a whole number from 1 to 5';
        } else {
            $fbBefore = (int) $fbBeforeRaw;
        }
        if (!ctype_digit($fbAfterRaw) || (int) $fbAfterRaw < 1 || (int) $fbAfterRaw > 5) {
            $rowErrors[] = 'Skill After Training must be a whole number from 1 to 5';
        } else {
            $fbAfter = (int) $fbAfterRaw;
        }
        if ($fbBefore !== null && $fbAfter !== null) {
            $feedbackComplete = true;
        }
    }

    if ($rowErrors) {
        $errors[] = "Row {$lineNo}: " . implode('; ', $rowErrors);
        continue;
    }

    $parsed[] = [
        'line' => $lineNo,
        'title' => $title,
        'venue' => $venue,
        'startdate' => date('Y-m-d', $startdateTs),
        'enddate' => date('Y-m-d', $enddateTs),
        'starttime' => date('H:i:s', $starttimeTs),
        'endtime' => date('H:i:s', $endtimeTs),
        'trainertype' => $trainertype,
        'trainername' => $trainername,
        'staffno' => $staffno,
        'feedback_complete' => $feedbackComplete,
        'fb_learn' => $fbLearn,
        'fb_before' => $fbBefore,
        'fb_after' => $fbAfter,
    ];
    $staffnos[$staffno] = true;
}

if (!$parsed && !$errors) {
    echo json_encode(['message' => 'error', 'errors' => ['The file has no data rows.']]);
    exit;
}

// ===== PASS 2: batch-resolve every referenced Staff No in one query =====
$staffLookup = [];
if ($staffnos) {
    $staffnoList = array_keys($staffnos);
    $placeholders = implode(',', array_fill(0, count($staffnoList), '?'));
    $stmt = mysqli_prepare($conn, "SELECT staffno, id, department FROM user WHERE staffno IN ($placeholders)");
    $stmt->bind_param(str_repeat('s', count($staffnoList)), ...$staffnoList);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $staffLookup[$r['staffno']] = ['id' => $r['id'], 'department' => $r['department']];
    }
    $stmt->close();
}

foreach ($parsed as $p) {
    if (!isset($staffLookup[$p['staffno']])) {
        $errors[] = "Row {$p['line']}: Staff No \"{$p['staffno']}\" was not found.";
    }
}

// Nothing is written unless every row in the file is valid, so a bad row
// never leaves a half-imported training behind.
if ($errors) {
    echo json_encode(['message' => 'error', 'errors' => $errors]);
    exit;
}

// ===== Group rows into trainings by exact Title match, preserving file order =====
$groups = [];
foreach ($parsed as $p) {
    if (!isset($groups[$p['title']])) {
        $groups[$p['title']] = ['meta' => $p, 'participants' => []];
    }
    $groups[$p['title']]['participants'][] = $p;
}

$suffix = ' - (' . date('Ymd/His') . ')';
$trainingsCreated = 0;
$participantsAdded = 0;
$completedWithFeedback = 0;

$insertOjt = mysqli_prepare($conn, "insert into ojt (title, startdate, enddate, starttime, endtime, venue, trainername, totalday, totalhour, trainertype) values (?,?,?,?,?,?,?,?,?,?)");
$updateOjt = mysqli_prepare($conn, "update ojt set totalman = ?, trainingcode = ? where id = ?");
$insertParticipant = mysqli_prepare($conn, "insert into participateojt (ojtid, userid, totalman, department, clerkid, q1, q2, q3, attendance) values (?,?,1,?,?,?,?,?,?)");

mysqli_begin_transaction($conn);
try {
    foreach ($groups as $group) {
        $meta = $group['meta'];
        $fullTitle = $meta['title'] . $suffix;

        $startDt = new DateTime($meta['startdate']);
        $endDt = new DateTime($meta['enddate']);
        $totalday = (int) $startDt->diff($endDt)->format('%a') + 1;
        $totalhour = round((strtotime($meta['endtime']) - strtotime($meta['starttime'])) / 3600, 2);

        $insertOjt->bind_param(
            'sssssssids',
            $fullTitle,
            $meta['startdate'],
            $meta['enddate'],
            $meta['starttime'],
            $meta['endtime'],
            $meta['venue'],
            $meta['trainername'],
            $totalday,
            $totalhour,
            $meta['trainertype']
        );
        $insertOjt->execute();
        $ojtid = mysqli_insert_id($conn);
        $trainingsCreated++;

        foreach ($group['participants'] as $participant) {
            $staff = $staffLookup[$participant['staffno']];
            $q1 = $participant['feedback_complete'] ? $participant['fb_learn'] : null;
            $q2 = $participant['feedback_complete'] ? (string) $participant['fb_before'] : null;
            $q3 = $participant['feedback_complete'] ? (string) $participant['fb_after'] : null;
            $attendance = $participant['feedback_complete'] ? 'COMPLETEDOJT' : '';
            $insertParticipant->bind_param(
                'iisissss',
                $ojtid,
                $staff['id'],
                $staff['department'],
                $clerkid,
                $q1,
                $q2,
                $q3,
                $attendance
            );
            $insertParticipant->execute();
            $participantsAdded++;
            if ($participant['feedback_complete']) {
                $completedWithFeedback++;
            }
        }

        $totalman = count($group['participants']);
        $trainingcode = 'OJ' . date('dmy') . sprintf('%05d', $ojtid);
        $updateOjt->bind_param('isi', $totalman, $trainingcode, $ojtid);
        $updateOjt->execute();
    }
    mysqli_commit($conn);
} catch (\Throwable $e) {
    mysqli_rollback($conn);
    echo json_encode(['message' => 'error', 'errors' => ['Import failed and was rolled back: ' . $e->getMessage()]]);
    exit;
}

echo json_encode([
    'message' => 'ok',
    'trainings_created' => $trainingsCreated,
    'participants_added' => $participantsAdded,
    'completed_with_feedback' => $completedWithFeedback,
]);
?>
