<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/LoginPage.css">
</head>
<body>
    <header>
        <img class="logo" src="../xlnLogo.png" alt="XLN Logo">
        <nav>
            <ul class="left-menu">
                <li><a href="#">MyAccount</a></li>
                <li><a href="#">XLN Home</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <ul class="right-menu">
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Profile</a>
                    <div class="dropdown-content">
                        <a href="../html/ProfilePage.html">View Profile</a>
                        <a href="#">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <form action="login.php" method="post">
            <h2>Welcome to the login page</h2>

            <div>
                <?php
                    if (isset($_GET["Login_Error"])) {
                        include_once("LoginError.php");
                }?>
            </div>

            <label for="email"><b>Email</b></label>
            <input type="text" id="email" name="email" required>
            <label for="password"><b>Password</b></label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
            <div class="links">
                <span class="psw"><a href="#">Forgot password?</a></span>
                <span class="psw"> <b> | </b></span>
                <span class="psw">Don't have an account? <a href="register.php">Register</a></span>
            </div>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
</body>
<script>
    document.getElementById("year").innerHTML = new Date().getFullYear();
</script>
</html>