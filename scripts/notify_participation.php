<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../dbconn.php';

$today = date('Y-m-d');

$sql = "SELECT pme.userid, pme.staffname, pme.staffno, pme.training_title, 
               DATE_FORMAT(training.startdate, '%e/%c/%Y') AS formatted_startdate,
               training.venue, user.email
        FROM pme 
        JOIN training ON pme.trainingid = training.id
        JOIN user ON pme.userid = user.id
        WHERE DATE_ADD(pme.to_date, INTERVAL 7 DAY) = ?
          AND pme.designation IN ('Executive', 'MANAGER (AM/HOS & ABOVE)') 
          AND pme.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    $participant_email = $row['email'];
    
    if (empty($participant_email)) {
        echo "No email found for User ID: {$row['userid']}, skipping...<br>";
        continue; 
    }

   
    $message = "<p style='font-size: 1.17em; font-style: italic;'>Assalamualaikum Warahmatullahi Wabarakatuh & Salam Sejahtera,</p>";
    $message .= "<h4 style='font-style: italic;'>Greetings From Learning & Development, PHN Industry Sdn Bhd.</h4>";
    $message .= "<p>Dear {$row['staffname']},</p>";
    $message .= "<p>As part of our continuous effort to ensure the effectiveness of training programmes, we are conducting a Performance Monitoring Evaluation for your recent training session:</p>";
    $message .= "<strong>Staff Name:</strong> {$row['staffname']}<br>";
    $message .= "<strong>Staff No:</strong> {$row['staffno']}<br>";
    $message .= "<strong>Training Title:</strong> {$row['training_title']}<br>";
    $message .= "<strong>Date:</strong> {$row['formatted_startdate']}<br>";
    $message .= "<strong>Venue:</strong> {$row['venue']}<br><br>";
    
    $message .= "<p>The evaluation period has concluded, and you are required to discuss your Performance Monitoring Evaluation Form with your HOD.</p>";
    $message .= "<h4>Action Required:</h4>";
    $message .= "<ul>";
    $message .= "<li>Please meet with your HOD to discuss how you have applied the training in your role.</li>";
    $message .= "<li>Complete the <strong>Performance Monitoring Evaluation Form</strong> together.</li>";
    $message .= "</ul>";
    
    $message .= "<h4>Submission Portal:</h4>";
    $message .= "<p>Please submit the completed evaluation form via the following link: <a href='https://portal.phn.com.my/ldms' target='_blank' style='color: #FFA73B;'>https://portal.phn.com.my/ldms</a></p>";
    $message .= "<p>Kindly return the completed evaluation form to Learning & Development (L&D) before the deadline.</p>";
    
    $message .= "<p>Should you require any further clarification, please do not hesitate to contact us.</p>";
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


        $mail->setFrom('server.info@phn.com.my', 'PHN System');
        $mail->addAddress($participant_email);
        $mail->isHTML(true);
        $mail->Subject = 'Performance Monitoring Evaluation Notification - Pending Evaluation';
        $mail->Body    = $message;

        
        $mail->send();
        echo "Email sent to User ID: {$row['userid']} ({$participant_email}) <br>";
    } catch (Exception $e) {
        echo "Email failed for User ID: {$row['userid']} ({$participant_email}). Error: {$mail->ErrorInfo} <br>";
    }
}

$conn->close();
?>
