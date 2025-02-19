<?php

$userID = isset($_GET['uid']) ? $_GET['uid'] : null;
$userData = null;
$db = new SQLite3('../data/XLN_new_DBA.db');

if ($userID) {
    $stmt = $db->prepare($sql = "SELECT u.*,
                                j.job AS job_name
                    FROM users u
                    LEFT JOIN jobs j ON u.jobID = j.jobID
                    WHERE u.userID = :userID");

    $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $userData = $result->fetchArray(SQLITE3_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>
    <link rel="stylesheet" href="../css/EditCase.css">
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
                        <a href="../html/ProfilePage.html">View Profile</a>
                        <a href="logOut.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>View Case</h1>
            
            <?php if ($userData): ?>
                <div id="viewCaseForm">
                    <div class="form-group">
                        <label>User ID:</label>
                        <p><?php echo $userData['userID']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>First Name:</label>
                        <p><?php echo $userData['fName']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>Middle Name:</label>
                        <p><?php echo $userData['mName']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name:</label>
                        <p><?php echo $userData['lName']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <p><?php echo $userData['email']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Password:</label>
                        <p><?php echo $userData['password']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Job Title:</label>
                        <p><?php echo $userData['job_name']; ?></p>
                    </div>
                    
                    <a href="UserManagement.php" class="button">Back to All Users</a>
                </div>
            <?php else: ?>
                <p>No User found or invalid User ID.</p>
                <a href="UserManagement.php">Back to All Users</a>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
</body>
</html>