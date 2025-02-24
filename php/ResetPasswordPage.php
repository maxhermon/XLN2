<?php

session_start();
require 'db_connection.php';
$db = connectToDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? null;
} else {
    $token = $_GET['token'] ?? null;
}

if (!$token) {
    echo "No token provided.";
    exit;
}



if ($token) {
    $stmt = $db->prepare("
        SELECT email
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
        echo "Invalid or expired token.";
        exit;
    }
    $email = $row['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword     = $_POST['newPassword']     ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if ($newPassword !== $confirmPassword) {
            echo "Passwords do not match!";
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

            echo "Password has been reset successfully! You can now <a href='LoginPage.php'>log in</a>.";
            exit;
        }
    }
} else {
    echo "No token provided.";
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
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
    </header>
    <main>
        <h1>Reset Password</h1>
        <form action="../php/ResetPasswordPage.php" method="POST">

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />

            <label for="newPassword"><b>New Password</b></label>
            <input type="password" id="newPassword" name="newPassword" required>
            <label for="confirmPassword"><b>Confirm Password</b></label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <button type="submit">Reset Password</button>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();
    </script>
</body>
</html>