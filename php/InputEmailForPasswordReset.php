<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/InputEmail.css">
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
    </header>
    <main>
        <form action="ResetPassword.php" method="POST">
            <label for="email"><b>Email address</b></label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send email</button>
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