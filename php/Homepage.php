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
    <title>Staff Homepage</title>
    <link rel="stylesheet" href="../css/Homepage.css">
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
                <li><a href="Contact.html"><i class="fa-solid fa-envelope"></i> Contact</a></li>
            </ul>
            <ul class="right-menu">
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn"><i class="fa-solid fa-circle-user"></i> MyAccount</a>
                    <div class="dropdown-content">
                        <a href="../php/ProfilePage.php">View Profile</a>
                        <a href="logOut.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($userData['fName'] . ' ' . ($userData['mName'] ?: '') . ' ' . $userData['lName']); ?></h1>
            <h2><?php echo htmlspecialchars($userData['jobID'] == 1) ? "Case Handler" : "Admin"; ?> </h2>
            <p>Today is <span id="currentDate"></span></p>
        </section>
        <section class="quick-links">
            <h2>Quick Links</h2>
            <div class="links-container">
                <a href="CaseCreation.php" class="link-box">Create New Case</a>
                <a href="ViewAllCases.php" class="link-box">View All Cases</a>
                <a href="ProfilePage.php" class="link-box">Profile</a>
                <a href="../html/Contact.html" class="link-box">Contact Support</a>
            </div>
        </section>
        <section class="notifications">
            <h2>Notifications</h2>
            <ul>
                <li>New case assigned to you.</li>
                <li>System maintenance scheduled for tomorrow.</li>
                <li>New company policy update.</li>
            </ul>
        </section>
        <section class="recent-activities">
            <h2>Recent Activities</h2>
            <table>
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Case #12345 updated</td>
                        <td>2023-10-01</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>Profile updated</td>
                        <td>2023-09-30</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>New case created</td>
                        <td>2023-09-29</td>
                        <td>Pending</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();
        document.getElementById("currentDate").innerHTML = new Date().toLocaleDateString();
    </script>
</body>
</html>