<?php
// Daily reminder: emails every participant a digest of the trainings they
// attended whose attendance form they still have not filled in.
//
// A participant stops appearing - and stops being emailed - the moment their
// participation row moves off PENDING, because the query below simply won't
// return them anymore. The two ways that happens:
//   - staff fills the attendance form in their own profile, which sets
//     attendance = 'COMPLETED'  (staff/*/attendance/attendance_action.php)
//   - admin marks them absent,  which sets attendance = 'ABSENT'
//     (admin/training/public/participant/participant_action.php)
// So there is no "stop sending" flag to maintain - it is self-terminating,
// the same way scripts/notify_pme_incomplete.php works.
//
// Reminders begin only once the training's enddate has PASSED, since staff
// cannot fill the form for a training that has not finished yet.
//
// Runnable two ways:
//  - CLI (php scripts/notify_attendance_incomplete.php) - no token needed, CLI
//    access already implies server access. This is how cron runs it on the VPS,
//    via scripts/run_reminders.sh. See that file for the crontab line.
//  - HTTP GET with a matching ?key= token, kept as a fallback for hosts with no
//    cron feature. Note portal2.phn.com.my sits behind Cloudflare with Bot
//    Fight Mode on, which blocks datacenter IPs (GitHub Actions runners,
//    cron-job.org), so an external HTTP scheduler needs an ordinary IP.
//    Change the token below before relying on it.
define('ATT_REMINDER_TOKEN', 'a8f31c07d94b62e5187ac4be03f7d215c6e94a8b7d310fe2');

// Every date in this script - the dedup log key, the "has the training ended
// yet" comparison, the days-overdue figure - comes from PHP's clock, so the
// timezone has to be pinned explicitly. A VPS almost always runs on UTC, which
// is 8 hours behind Malaysia: a cron firing at 07:00 MYT would otherwise see
// 23:00 UTC of the *previous* day, writing the wrong dedup row and treating a
// training that ended today as not yet ended. Matches what the rest of the
// codebase already does (see admin/fetch_dash.php:13 and others).
date_default_timezone_set('Asia/Kuala_Lumpur');

// ---------------------------------------------------------------------------
// TWO SWITCHES TO FLIP BEFORE THIS GOES LIVE
// ---------------------------------------------------------------------------

// 1. TEST MODE: when non-empty, every email is redirected here instead of the
//    real participant, with the subject tagged with who it was actually meant
//    for. Set to '' (empty string) to go live and send to real participants.
define('ATT_REMINDER_TEST_EMAIL', 'amir.anwar@phn.com.my');

// 2. CUTOFF: only trainings that ended on or after this date are chased.
//    Deliberately conservative so the first live run does not blast the whole
//    historical backlog at once.
//    As of 2026-09-03 the pending backlog is 120 rows / 100 staff (74 with an
//    email on file), spread as: Jun 22, Jul 49, Aug 48, Sep 1 - nothing older
//    than 2026-06-03. So:
//      '2026-09-01' (current) -> first run chases only the most recent trainings
//      '2026-06-01'           -> first run chases the entire existing backlog
define('ATT_REMINDER_CUTOFF', '2026-08-01');

// ---------------------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(ATT_REMINDER_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }

    // Over HTTP, PHP applies the web server's max_execution_time (commonly 30-60s)
    // where the CLI has no limit at all. Each Office365 SMTP send takes roughly a
    // second or two, so a run covering the full backlog needs a couple of minutes
    // and would otherwise be killed partway through. That matters more than usual
    // here because the dedup row is written up front: a kill does not just delay
    // the remaining participants, it skips them for the whole day, and the same
    // truncation would repeat every day - the people early in the sort order would
    // be the only ones ever reminded.
    //
    // ignore_user_abort keeps the run going if curl gives up or the cron job is
    // interrupted, so a client-side timeout cannot truncate the send either.
    @set_time_limit(0);
    @ignore_user_abort(true);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../dbconn.php';

$today = date('Y-m-d');

// Guard against sending the same day's digest twice (a scheduler retrying
// after a slow response, or someone hitting the URL manually after Task
// Scheduler already ran it). Kept in the database rather than a file next to
// the script, since a file here gets wiped by any deploy that re-syncs this
// folder from source control - a DB row survives that.
$conn->query("CREATE TABLE IF NOT EXISTS attendance_reminder_log (run_date DATE NOT NULL PRIMARY KEY)");
$checkStmt = $conn->prepare("SELECT 1 FROM attendance_reminder_log WHERE run_date = ?");
$checkStmt->bind_param("s", $today);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo "[" . date('Y-m-d H:i:s') . "] Already sent today ($today). Skipping.\n";
    exit();
}
// Marked as sent up front, before the loop, so a mid-run failure (PHP timeout,
// SMTP outage) can't cause a retry to resend everyone who already got their
// email that day - worst case a few participants wait until tomorrow's run,
// which is a much smaller problem than duplicate reminders.
$conn->query("INSERT IGNORE INTO attendance_reminder_log (run_date) VALUES ('" . $conn->real_escape_string($today) . "')");

// attendance is NULL for rows never touched and '' for some older rows - the
// UI treats both as PENDING (see fetch_participant.php), so both are chased.
$sql = "SELECT p.userid, u.staffname, u.staffno, u.email,
               t.trainingcode, t.title,
               DATE_FORMAT(t.startdate, '%e/%c/%Y') AS formatted_startdate,
               DATE_FORMAT(t.enddate,   '%e/%c/%Y') AS formatted_enddate,
               DATEDIFF(?, t.enddate) AS days_overdue
        FROM participation p
        JOIN training t ON t.id = p.trainingid
        JOIN user u     ON u.id = p.userid
        WHERE (p.attendance IS NULL OR p.attendance = '')
          AND t.enddate < ?
          AND t.enddate >= ?
          AND u.email IS NOT NULL AND u.email <> ''
        ORDER BY t.enddate DESC, u.staffname";
$stmt = $conn->prepare($sql);
$cutoff = ATT_REMINDER_CUTOFF;
$stmt->bind_param("sss", $today, $today, $cutoff);
$stmt->execute();
$result = $stmt->get_result();

// One digest per participant, so someone with three outstanding trainings gets
// a single email listing all three rather than three separate emails.
$participants = [];
while ($row = $result->fetch_assoc()) {
    $participants[$row['userid']][] = $row;
}

// Counted separately for the run summary: staff who are pending but have no
// email address on file, so nobody assumes they were reminded.
$noEmailCount = 0;
$noEmailStmt = $conn->prepare(
    "SELECT COUNT(DISTINCT p.userid) AS c
     FROM participation p
     JOIN training t ON t.id = p.trainingid
     JOIN user u     ON u.id = p.userid
     WHERE (p.attendance IS NULL OR p.attendance = '')
       AND t.enddate < ? AND t.enddate >= ?
       AND (u.email IS NULL OR u.email = '')"
);
$noEmailStmt->bind_param("ss", $today, $cutoff);
$noEmailStmt->execute();
if ($noEmailRow = $noEmailStmt->get_result()->fetch_assoc()) {
    $noEmailCount = (int) $noEmailRow['c'];
}

$sentCount = 0;
$failedList = [];

foreach ($participants as $userid => $trainings) {
    $staffname = $trainings[0]['staffname'];
    $email     = $trainings[0]['email'];

    $message  = "<p style='font-size: 1.17em; font-style: italic;'>Assalamualaikum Warahmatullahi Wabarakatuh & Salam Sejahtera,</p>";
    $message .= "<h4 style='font-style: italic;'>Greetings From Learning & Development, PHN Industry Sdn Bhd.</h4>";
    $message .= "<p>Dear <strong>" . htmlspecialchars($staffname) . "</strong>,<br><br>Please complete the following training(s): </p>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th>Training Code</th>
                        <th>Training Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days Overdue</th>
                    </tr>";
    foreach ($trainings as $tr) {
        $message .= "<tr>
                        <td>" . htmlspecialchars($tr['trainingcode']) . "</td>
                        <td>" . htmlspecialchars($tr['title']) . "</td>
                        <td>{$tr['formatted_startdate']}</td>
                        <td>{$tr['formatted_enddate']}</td>
                        <td>{$tr['days_overdue']}</td>
                    </tr>";
    }
    $message .= "</table>";

    $message .= "<h4>Action Required:</h4>";
    $message .= "<p>Please log in to the portal, go to <strong>Attendance</strong> in your profile, and complete the attendance form for each training listed above. Your attendance status will remain <strong>PENDING</strong> until you do.</p>";
    $message .= "<h4>Submission Portal:</h4>";
    $message .= "<p>Please complete the form via the following link: <a href='https://portal.phn.com.my/ldms' target='_blank' style='color: #FFA73B;'>https://portal.phn.com.my/ldms</a></p>";

    $message .= "<p>You will continue to receive this reminder daily until the attendance form for each training above is completed.</p>";
    $message .= "<p>Should you require any further clarification, please do not hesitate to contact us. We appreciate your cooperation in ensuring the effectiveness of our training programs.</p>";
    $message .= "<p>If you have any questions or login issues, please contact Learning & Development to reset your password.</p>";

    $message .= "<p><strong>Thank You.</strong></p>";
    $message .= "<p>--This is an auto-generated email, no reply is needed--</p>";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'server.info@phn.com.my';
        $mail->Password   = 'P@ssw0rd';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $sendTo = ATT_REMINDER_TEST_EMAIL !== '' ? ATT_REMINDER_TEST_EMAIL : $email;

        $mail->setFrom('server.info@phn.com.my', 'PHN System');
        $mail->addAddress($sendTo);
        $mail->isHTML(true);
        $mail->Subject = 'Training Attendance Notification - Incomplete Attendance Reminder';
        if (ATT_REMINDER_TEST_EMAIL !== '') {
            $mail->Subject = '[TEST - intended for ' . $email . '] ' . $mail->Subject;
        }
        $mail->Body = $message;

        $mail->send();
        $sentCount++;
    } catch (Exception $e) {
        $failedList[] = ['userid' => $userid, 'email' => $email, 'error' => $mail->ErrorInfo];
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Attendance reminder run\n";
echo "- Mode: " . (ATT_REMINDER_TEST_EMAIL !== '' ? 'TEST (all mail to ' . ATT_REMINDER_TEST_EMAIL . ')' : 'LIVE') . "\n";
echo "- Cutoff: trainings ending on/after " . ATT_REMINDER_CUTOFF . "\n";
echo "- Participants pending: " . count($participants) . "\n";
echo "- Sent: $sentCount\n";
echo "- Failed: " . count($failedList) . "\n";
echo "- Skipped (no email on file): $noEmailCount\n";
if (!empty($failedList)) {
    echo "- Failed sends:\n";
    foreach ($failedList as $f) {
        echo "  - User ID {$f['userid']} ({$f['email']}): {$f['error']}\n";
    }
}

$conn->close();
?>
