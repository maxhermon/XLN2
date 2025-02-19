<?php
require_once "vendor/autoload.php";

session_start();                    
require 'db_connection.php';        
$db = connectToDatabase();         

if (!isset($_SESSION['userID'])) {
    header("Location: LoginPage.php");
    exit;
}

$userID = $_SESSION['userID'];
$stmt = $db->prepare("SELECT fName, mName, lName, email, jobID
                      FROM users
                      WHERE userID = :userID");
$stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
$result = $stmt->execute();
$userData = $result->fetchArray(SQLITE3_ASSOC);

if (!$userData) {
    echo "User not found in the database.";
    exit;
}


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
    $mail->addAddress(htmlspecialchars($userData['email']), htmlspecialchars($userData['fName'] . ' ' . ($userData['mName'] ?: '') . ' ' . $userData['lName']) ); // Add a recipient
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

?>