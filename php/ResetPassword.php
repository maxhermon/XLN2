<?php
require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'xlnhelpbot@gmail.com'; // Your email address
    $mail->Password = 'idxq vspa vzzd ivqw'; // Your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('xlnhelpbot@gmail.com', 'XLN');
    $mail->addAddress('recipient@example.com', 'Recipient Name'); // Add a recipient
    $mail->addReplyTo('reply@yourdomain.com', 'Reply');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password reset request';
    $mail->Body    = 'Dear ' . htmlspecialchars($user['fName']) . ',<br><br>' .
                             'Click the following link to set up your password: ' .
                             '<a href="http://localhost/EasyHealth%20Hospital%20Management%20System/setup_password.html?token=' . $token . '">Set Up Password</a><br><br>' .
                             'Best Regards,<br> XLN Team';;
    $mail->AltBody = 'This is the plain text version of the email content';

    $mail->send();
    echo 'Message has been sent successfully';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}