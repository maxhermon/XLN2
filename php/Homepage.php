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

$recentActivities = [];

if ($_SESSION['jobID'] == 3) {
    // Fetch recent activities for all case handlers managed by this manager
    $activitiesStmt = $db->prepare("
        SELECT a.activity, a.date, a.status, u.fName || ' ' || u.lName AS handlerName
        FROM activities a
        INNER JOIN users u ON a.userID = u.userID
        WHERE u.managerID = :managerID
        ORDER BY a.date DESC
        LIMIT 5
    ");
    $activitiesStmt->bindValue(':managerID', $userID, SQLITE3_INTEGER);
} else {
    // Fetch recent activities for the logged-in user
    $activitiesStmt = $db->prepare("
        SELECT activity, date, status
        FROM activities
        WHERE userID = :userID
        ORDER BY date DESC
        LIMIT 5
    ");
    $activitiesStmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
}

$activitiesResult = $activitiesStmt->execute();

while ($row = $activitiesResult->fetchArray(SQLITE3_ASSOC)) {
    $recentActivities[] = $row;
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
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
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
            <h2>
                <?php
                if ($userData['jobID'] == 1) {
                    echo "Case Handler";
                } elseif ($userData['jobID'] == 2) {
                    echo "Admin";
                } elseif ($userData['jobID'] == 3) {
                    echo "Manager";
                } else {
                    echo "Unknown Role";
                }
                ?>
            </h2>
            <p>Today is <span id="currentDate"></span></p>
        </section>
        <section class="quick-links">
            <h2>Quick Links</h2>
            <div class="links-container <?php 
                echo ($_SESSION['jobID'] == 2) ? 'admin' : 
                     (($_SESSION['jobID'] == 3) ? 'manager' : 'case-handler'); 
            ?>">
                <a href="../php/CaseCreation.php" class="link-box">Create New Case</a>
                <a href="../php/ViewAllCases.php" class="link-box">View All Cases</a>
                <a href="ProfilePage.php" class="link-box">Profile</a>
                <a href="../html/Contact.html" class="link-box">Contact Support</a>
                <?php if ($_SESSION['jobID'] == 2) { ?>
                    <a href="UserCreation.php" class="link-box">Add Users</a>
                    <a href="UserManagement.php" class="link-box">Manage Users</a>
                    <a href="JobRoleCreation.php" class="link-box job-role">Add Job Role</a>
                <?php } ?>
                <?php if ($_SESSION['jobID'] == 3) { ?>
                    <a href="SeeCaseHandlers.php" class="link-box  SeeCaseHandlers">See Case Handlers</a>
                <?php } ?>
            </div>
        </section>
        <section class="recent-activities">
            <h2>Recent Activities</h2>
            <table>
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Status</th>
                        <?php if ($_SESSION['jobID'] == 3): ?>
                            <th>Case Handler</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentActivities)): ?>
                        <tr>
                            <td colspan="<?php echo ($_SESSION['jobID'] == 3) ? '4' : '3'; ?>">No recent activities found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                <td><?php echo htmlspecialchars($activity['date']); ?></td>
                                <td><?php echo htmlspecialchars($activity['status']); ?></td>
                                <?php if ($_SESSION['jobID'] == 3): ?>
                                    <td><?php echo htmlspecialchars($activity['handlerName']); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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