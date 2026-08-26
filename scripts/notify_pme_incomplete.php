<?php
// Daily reminder: emails every boss (pme.hodid) a digest of their PME
// evaluations that are due (from_date <= today) but still not completed.
// A boss stops appearing - and stops being emailed - the moment all their
// pending rows move to 'approved'/'completed'/'verified', since the query
// below simply won't return them anymore.
//
// Runnable two ways:
//  - CLI (php scripts/notify_pme_incomplete.php), e.g. from script.bat / Task
//    Scheduler - no token needed, CLI access already implies server access.
//  - HTTP GET with a matching ?key= token, for hosts with no cron feature -
//    point an external scheduler at this URL once a day:
//      https://<your-domain>/ldms/scripts/notify_pme_incomplete.php?key=c23c26f50df52e734e73c1e4e43c598b476594355ff6e8bc
//    portal2.phn.com.my sits behind Cloudflare with Bot Fight Mode on, which
//    challenges/blocks non-browser HTTP clients (confirmed: GitHub Actions'
//    and likely cron-job.org's datacenter IPs get a "Just a moment" 403,
//    while a normal office/ISP-IP client passes straight through) - so the
//    scheduler needs to run somewhere with an IP Cloudflare doesn't flag,
//    e.g. Windows Task Scheduler on an ordinary server, not a cloud CI runner.
//    Change PME_REMINDER_TOKEN below before relying on this in production -
//    the value here is a placeholder generated for setup, not a secret kept
//    out of source control.
define('PME_REMINDER_TOKEN', 'c23c26f50df52e734e73c1e4e43c598b476594355ff6e8bc');

// TEST MODE: every email is redirected here instead of the real boss, with
// the subject tagged with who it was actually meant for, so a test run can
// be verified without spamming real inboxes. Set to '' to send to the real
// boss addresses again - MUST be done before relying on this in production.
define('PME_REMINDER_TEST_EMAIL', 'amir.anwar@phn.com.my');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(PME_REMINDER_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../dbconn.php';

$today = date('Y-m-d');

// Guard against sending the same day's digest twice (e.g. a scheduler
// retrying after a slow response, or someone hitting the URL manually after
// Task Scheduler already ran it). Not needed for correctness of the "stop
// once done" behaviour - just avoids duplicate emails within the same day.
// Kept in the database rather than a file next to the script, since a file
// here gets wiped by any deploy that re-syncs this folder from source
// control - a DB row survives that.
$conn->query("CREATE TABLE IF NOT EXISTS pme_reminder_log (run_date DATE NOT NULL PRIMARY KEY)");
$checkStmt = $conn->prepare("SELECT 1 FROM pme_reminder_log WHERE run_date = ?");
$checkStmt->bind_param("s", $today);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo "Already sent today ($today). Skipping.<br>";
    exit();
}
// Marked as sent up front, before the loop, so a mid-run failure (PHP
// timeout, SMTP outage) can't cause a retry to resend everyone who already
// got their email that day - worst case a few bosses wait until tomorrow's
// run instead, which is a much smaller problem than duplicate reminders.
$conn->query("INSERT IGNORE INTO pme_reminder_log (run_date) VALUES ('" . $conn->real_escape_string($today) . "')");

$sql = "SELECT pme.hodid, pme.staffname, pme.staffno, pme.training_title,
               DATE_FORMAT(pme.from_date, '%e/%c/%Y') AS formatted_from_date,
               DATE_FORMAT(pme.to_date, '%e/%c/%Y') AS formatted_to_date
        FROM pme
        WHERE pme.from_date <= ?
          AND pme.designation IN ('Executive', 'MANAGER (AM/HOS & ABOVE)')
          AND pme.status = 'pending'
        ORDER BY pme.training_title, pme.staffname";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$hod_subordinates = [];
while ($row = $result->fetch_assoc()) {
    $hodid = $row['hodid'];
    if (!isset($hod_subordinates[$hodid])) {
        $hod_subordinates[$hodid] = [];
    }
    $hod_subordinates[$hodid][] = $row;
}

$hod_emails = [];
$hod_ids = array_keys($hod_subordinates);
if (!empty($hod_ids)) {
    $placeholders = implode(',', array_fill(0, count($hod_ids), '?'));
    $email_sql = "SELECT id, email FROM user WHERE id IN ($placeholders)";
    $email_stmt = $conn->prepare($email_sql);
    $email_stmt->bind_param(str_repeat('i', count($hod_ids)), ...$hod_ids);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();
    while ($email_row = $email_result->fetch_assoc()) {
        $hod_emails[$email_row['id']] = $email_row['email'];
    }
}

foreach ($hod_subordinates as $hodid => $subordinates) {
    if (!isset($hod_emails[$hodid]) || $hod_emails[$hodid] === '') {
        echo "No email found for HOD ID: $hodid, skipping...<br>";
        continue;
    }

    $hod_email = $hod_emails[$hodid];
    $message = "<p style='font-size: 1.17em; font-style: italic;'>Assalamualaikum Warahmatullahi Wabarakatuh & Salam Sejahtera,</p>";
    $message .= "<h4 style='font-style: italic;'>Greetings From Learning & Development, PHN Industry Sdn Bhd.</h4>";
    $message .= "<p>Dear All,<br><br>This is a reminder that the following Performance Monitoring Evaluation(s) assigned to you are <strong>not yet completed</strong>:</p>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th>Name</th>
                        <th>Staff No</th>
                        <th>Training Title</th>
                        <th>Evaluation Period Start</th>
                        <th>Evaluation Period End</th>
                    </tr>";
    foreach ($subordinates as $sub) {
        $message .= "<tr>
                        <td>{$sub['staffname']}</td>
                        <td>{$sub['staffno']}</td>
                        <td>{$sub['training_title']}</td>
                        <td>{$sub['formatted_from_date']}</td>
                        <td>{$sub['formatted_to_date']}</td>
                    </tr>";
    }
    $message .= "</table>";

    $message .= "<h4> Action Required:</h4>";
    $message .= "<p>Please assess your staff's performance, provide feedback on their progress, and suggest areas for improvement. The completed <strong>Performance Monitoring Evaluation Form</strong> must be <strong>reviewed and agreed upon by both the participant and the HOD</strong> before submission.</p>";
    $message .= "<h4> Submission Portal:</h4>";
    $message .= "<p>Please submit the completed evaluation form via the following link: <a href='https://portal.phn.com.my/ldms' target='_blank' style='color: #FFA73B;'>https://portal.phn.com.my/ldms</a></p>";

    $message .= "<p>You will continue to receive this reminder daily until all evaluations listed above are completed.</p>";
    $message .= "<p>Should you require any further clarification, please do not hesitate to contact us. We appreciate your cooperation in ensuring the effectiveness of our training programs.</p>";

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

        $sendTo = PME_REMINDER_TEST_EMAIL !== '' ? PME_REMINDER_TEST_EMAIL : $hod_email;

        $mail->setFrom('server.info@phn.com.my', 'PHN System');
        $mail->addAddress($sendTo);
        $mail->isHTML(true);
        $mail->Subject = 'Performance Monitoring Evaluation Notification - Pending Evaluation Reminder';
        if (PME_REMINDER_TEST_EMAIL !== '') {
            $mail->Subject = '[TEST - intended for ' . $hod_email . '] ' . $mail->Subject;
        }
        $mail->Body    = $message;

        $mail->send();
        echo "Email sent to HOD ID: $hodid (intended: $hod_email, actually sent to: $sendTo) <br>";
    } catch (Exception $e) {
        echo "Email failed for HOD ID: $hodid ($hod_email). Error: {$mail->ErrorInfo} <br>";
    }
}

$conn->close();
?>
