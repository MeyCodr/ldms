<?php

    include "dbconn.php";

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

    $totaltraining = "";

    $sql = "SELECT count(*) as totaltraining from training where startdate = DATE(NOW() - INTERVAL 3 MONTH)";
    $query = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_assoc($query)) {
        $totaltraining = mysqli_real_escape_string($conn, $row['totaltraining']);
    }

    $sql = "SELECT trainingcode,title,startdate from training where startdate = DATE(NOW() - INTERVAL 3 MONTH)";
    $query = mysqli_query($conn,$sql);
    $options = '<table style="border: 1px solid;"><thead><tr><th style="border: 1px solid;">Training Code</th><th style="border: 1px solid;">Title</th><th style="border: 1px solid;">Start Date</th></tr></thead><tbody>';
    if (mysqli_num_rows($query) > 0) {
        // output data of each row

        while($row = mysqli_fetch_assoc($query)) {
            $options.= '<tr><td style="border: 1px solid;">'.$row['trainingcode'].'</td><td style="border: 1px solid;">'.$row['title'].'</td><td style="border: 1px solid;">'.$row['startdate'].'</td></tr>';
        }
    }
    $options.= '</tbody></table>';

    if ($totaltraining > 0) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'server.info@phn.com.my';
            $mail->Password = 'P@ssw0rd';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
    
            //Recipients
            $mail->setFrom('server.info@phn.com.my', 'LDMS Official');
            $mail->addAddress('norfikri@phn.com.my');
			$mail->addAddress('norhidayah@phn.com.my');
			$mail->addAddress('zahrah@phn.com.my');
    
            //Content
            $mail->isHTML(true);
            $mail->Subject = 'LDMS Portal : 3 Months Old Public Training';
            $mail->Body    = '
                <p>Dear Sir/Miss/Madam,</p>
                <p>There are few public trainings which is already past 3 months. Details as below:</p>
                ' .$options.'
                <p>Kindly proceed to this link : <a href="https://portal.phn.com.my/ldms" target="_blank" style="color: #FFA73B;">https://portal.phn.com.my/ldms</a> for further action. </p>
                <p>Thank You</p>
                <p>--This is an auto-generated email, no reply is needed--</p>
            ';
    
            $mail->send();
        }catch (Exception $e) {

        }
    }
?>