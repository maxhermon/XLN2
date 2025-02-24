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
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <a href="../html/Contact.html"><i class="fa-solid fa-envelope"></i> Contact</a>
    </header>
    <main>
        <form action="../php/ResetPasswordPage.php" method="POST">
            <h1>Reset Password</h1>
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