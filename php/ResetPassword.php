<?php
require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();                    
require 'db_connection.php';        
$db = connectToDatabase();         

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $db = connectToDatabase();

    // Check if the email exists in the database
    $stmt = $db->prepare("SELECT userID, fName FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $userData = $result->fetchArray(SQLITE3_ASSOC);
}

if (!isset($_SESSION['userID'])) {
    header("Location: LoginPage.php");
    exit;
}
else{
    $userID = $userData['userID'];
    $token = bin2hex(random_bytes(32)); // Generate a unique token

    // Store the token in the database with an expiration time
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires) VALUES (:email, :token, :expires)");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':expires', date("Y-m-d H:i:s", strtotime('+1 hour')), SQLITE3_TEXT);
    $stmt->execute();

    // Send the reset password email
    $mail = new PHPMailer(true);
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

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password reset request';
    $mail->Body    = 'Dear ' . htmlspecialchars($userData['fName']) . ',<br><br>' .
                             'Click the following link to set up your password: ' .
                             '<a href="http://localhost/XLN2/php/ResetPasswordPage.php?token=' . $token . '">Set Up Password</a><br><br>' .
                             'Best Regards,<br> XLN Team';;
    $mail->AltBody = 'Dear ' . htmlspecialchars($userData['fName']) . ', Click the following link to set up your password: http://localhost/EasyHealth%20Hospital%20Management%20System/setup_password.html?token=' . $token . ' Best Regards, XLN Team';

    $mail->send();
    echo 'Message has been sent successfully';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}

?>