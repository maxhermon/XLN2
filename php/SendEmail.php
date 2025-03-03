<?php
require_once "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require 'db_connection.php';
$db = connectToDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    $stmt = $db->prepare("SELECT userID, fName, mName, lName FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result   = $stmt->execute();
    $userData = $result->fetchArray(SQLITE3_ASSOC);

    if ($userData) {
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires) 
                              VALUES (:email, :token, :expires)");
        $stmt->bindValue(':email',   $email,                          SQLITE3_TEXT);
        $stmt->bindValue(':token',   $token,                          SQLITE3_TEXT);
        $stmt->bindValue(':expires', date('Y-m-d H:i:s', strtotime('+1 hour')), SQLITE3_TEXT);
        $stmt->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'xlnhelpbot@gmail.com';
            $mail->Password   = 'idxq vspa vzzd ivqw'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('xlnhelpbot@gmail.com', 'XLN');
            
            $fullName = trim($userData['fName'] . ' ' . ($userData['mName'] ?: '') . ' ' . $userData['lName']);
            $mail->addAddress($email, $fullName);

            $mail->isHTML(true);
            $mail->Subject = 'Password reset request';
            $mail->Body    = 'Dear ' . htmlspecialchars($userData['fName']) . ',<br><br>' .
                             'Click the following link to set up your password: ' .
                             '<a href="http://localhost/XLN2/php/ResetPasswordPage.php?token=' . $token . '">Set Up Password</a><br><br>' .
                             'Best Regards,<br> XLN Team';

            $mail->send();
            echo 'Message has been sent successfully';
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        echo 'If that email exists, a reset link has been sent!';
    }
} else {
    header("Location: ../html/InputEmailForPasswordPage.php");
    exit;
}
?>
