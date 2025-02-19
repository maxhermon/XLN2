<?php

$userID = isset($_GET['uid']) ? $_GET['uid'] : null;
$errorMessage = '';
$successMessage = '';
$caseData = null;


$db = new SQLite3('../data/XLN_new_DBA.db');

// Fetch all jobs for the dropdown
$jobsQuery = "SELECT jobID, job FROM jobs ORDER BY job";
$jobsResult = $db->query($jobsQuery);
$jobs = [];
while ($row = $jobsResult->fetchArray(SQLITE3_ASSOC)) {
    $jobs[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fName'];
    $mname = $_POST['mName'];
    $lname = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $jobID = $_POST['jobID'];
    $userID = $_POST['userID'];

    
    $stmt = $db->prepare("UPDATE users SET fName = :fName, mName = :mName, lName = :lName, email = :email, password = :password, jobID = :jobID WHERE userID = :userID");
    
    $stmt->bindValue(':fName', $fname, SQLITE3_TEXT);
    $stmt->bindValue(':mName', $mname, SQLITE3_TEXT);
    $stmt->bindValue(':lName', $lname, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':jobID', $jobID, SQLITE3_INTEGER);
    $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
    
    $result = $stmt->execute();
    
    if ($result) {
        $successMessage = "User updated successfully!";
    } else {
        $errorMessage = "Failed to update User: " . $db->lastErrorMsg();
    }
}

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
                        <a href="ProfilePage.php">View Profile</a>
                        <a href="logOut.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Edit User</h1>
            
            <?php if ($errorMessage): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($successMessage): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($userData): ?>
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="userID" value="<?php echo $userData['userID']; ?>">
                    
                    <label for="fName">First Name:</label>
                    <input type="text" id="fName" name="fName" value="<?php echo $userData['fName']; ?>">
                    
                    <label for="mName">Middle Name:</label>
                    <input type="text" id="mName" name="mName" value="<?php echo $userData['mName']; ?>">
                    
                    <label for="lName">Last Name:</label>
                    <input type="text" id="lName" name="lName" value="<?php echo $userData['lName']; ?>">

                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" value="<?php echo $userData['email']; ?>">
                    
                    <label for="password">Password:</label>
                    <input type="text" id="password" name="password" value="<?php echo $userData['password']; ?>" readonly>

                    <label for="jobID">Job:</label>
                    <select id="jobID" name="jobID">
                        <?php foreach ($jobs as $job): ?>
                            <option value="<?php echo $job['jobID']; ?>" 
                            <?php echo ($userData['jobID'] == $job['jobID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($job['job']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit">Save Changes</button>

                    <a href="UserManagement.php" class="button">Back to All Users</a>
                </form>
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