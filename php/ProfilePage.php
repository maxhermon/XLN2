<?php

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
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../css/ProfilePage.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
  />
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <nav>
            <ul class="left-menu">
                <li><a href="Homepage.php"><i class="fa-solid fa-house"></i> XLN Home</a></li>
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
        <div class="profile-container">
            <h1>User Profile</h1>
            <div class="profile-info">
                <p><strong>Name:</strong>
                <?php echo htmlspecialchars($userData['fName'] . ' ' . ($userData['mName'] ?: '') . ' ' . $userData['lName']); ?>
                </p>
                <p><strong>Job Role:</strong>
                <?php echo htmlspecialchars($userData['jobID'] == 1) ? "Case Handler" : "Admin"; ?>
                </p>
                <p><strong>Email:</strong>
                <?php echo htmlspecialchars($userData['email']); ?>
                </p>
                <p><strong>User ID:</strong>
                <?php echo htmlspecialchars($userID); ?>
                </p>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();
    </script>
</body>
</html>