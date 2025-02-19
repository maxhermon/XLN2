<?php

require 'db_connection.php';  
$db = connectToDatabase();   


if (isset($_POST['addCaseHandler'])) {




    $fName = $_POST['fName'] ?? null;
    $mName = $_POST['mName'] ?? null;
    $lName = $_POST['lName']     ?? null;
    $email = $_POST['email']       ?? null;
    $password = $_POST['password'] ?? null;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT into users (fName, mName, lName, email, password, jobID)
    VALUES (:fName, :mName, :lName, :email, :password, :jobID)";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':fName', $fName, SQLITE3_TEXT);
    $stmt->bindValue(':mName', $mName, SQLITE3_TEXT);
    $stmt->bindValue(':lName', $lName, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->bindValue(':jobID', 1, SQLITE3_INTEGER);

    $stmt->execute();



    header('Location: UserCreated.php');
    exit;
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>
    <link rel="stylesheet" href="../css/UserCreation.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
  />
</head>
<body>
<header>
        <a href="../html/Homepage.html"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <nav>
            <ul class="left-menu">
                <li><a href="../html/Homepage.html"><i class="fa-solid fa-house"></i> XLN Home</a></li>
                <li><a href="../html/Contact.html"><i class="fa-solid fa-envelope"></i> Contact</a></li>
            </ul>
            <ul class="right-menu">
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn"><i class="fa-solid fa-circle-user"></i> MyAccount</a>
                    <div class="dropdown-content">
                        <a href="ProfilePage.php">View Profile</a>
                        <a href="logOut.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <form action="UserCreation.php" method="POST">
            <h2>User Creation Page</h2>


            <label for="fName"><b>First Name</b></label>
            <input type="text" id="fName" name="fName" required>
            <label for="fName"><b>Middle Name</b></label>
            <input type="text" id="mName" name="mName">
            <label for="lName"><b>Last Name</b></label>
            <input type="text" id="lName" name="lName" required>
            <label for="email"><b>Email</b></label>
            <input type="text" id="email" name="email" required>
            <label for="password"><b>Password</b></label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="addCaseHandler">Add Case Handler</button>
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