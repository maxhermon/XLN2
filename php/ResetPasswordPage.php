<?php

session_start();
require 'db_connection.php';
$db = connectToDatabase();

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? null;
} else {
    $token = $_GET['token'] ?? null;
}

if (!$token) {
    $response['status'] = 'error';
    $response['message'] = 'No token provided.';
    echo json_encode($response);
    exit;
}

if ($token) {
    $stmt = $db->prepare("SELECT email
        FROM password_resets
        WHERE token = :token
        AND expires > :now
        LIMIT 1
    ");
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':now', date("Y-m-d H:i:s"), SQLITE3_TEXT);
    $result = $stmt->execute();
    $row    = $result->fetchArray(SQLITE3_ASSOC);

    if (!$row) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid or expired token.';
        echo json_encode($response);
        exit;
    }
    $email = $row['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword     = $_POST['newPassword']     ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $response['status'] = 'error';
            $response['message'] = 'Passwords do not match!';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $db->prepare("
                UPDATE users
                SET password = :password
                WHERE email  = :email
            ");
            $updateStmt->bindValue(':password',    $hashed, SQLITE3_TEXT);
            $updateStmt->bindValue(':email', $email,  SQLITE3_TEXT);
            $updateStmt->execute();

            $delStmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
            $delStmt->bindValue(':email', $email, SQLITE3_TEXT);
            $delStmt->execute();

            $response['status'] = 'success';
            $response['message'] = 'Password has been reset successfully! You can now <a href="LoginPage.php">log in</a>.';
        }
        echo json_encode($response);
        exit;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'No token provided.';
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/ResetPassword.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <a href="../html/Contact.html"><i class="fa-solid fa-envelope"></i> Contact</a>
    </header>
    <main>
        <form id="ResetPassword" action="ResetPasswordPage.php" method="POST">
            <h1>Reset Password</h1>

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />

            <label for="newPassword"><b>New Password</b></label>
            <input type="password" id="newPassword" name="newPassword" required>
            <label for="confirmPassword"><b>Confirm Password</b></label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <button type="submit">Reset Password</button>
            <div id="message" style="display:none;"></div>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();

        $(document).ready(function() {
            $('#ResetPassword').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: 'ResetPasswordPage.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        $('#message').html(response.message).show();
                        if (response.status === 'success') {
                            $('#ResetPassword')[0].reset();
                        }
                    },
                    error: function() {
                        $('#message').html('An error occurred. Please try again.').show();
                    }
                });
            });
        });
    </script>
</body>
</html>