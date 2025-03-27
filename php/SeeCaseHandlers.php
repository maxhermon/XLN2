<?php
// filepath: C:/xampp/htdocs/XLN2/php/SeeCaseHandlers.php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['userID']) || $_SESSION['jobID'] != 3) { // Ensure only managers can access
    header("Location: LoginPage.php");
    exit;
}

$db = connectToDatabase();
$managerID = $_SESSION['userID'];

$stmt = $db->prepare("
    SELECT u.userID, u.fName, u.mName, u.lName, u.email, j.job
    FROM users u
    INNER JOIN jobs j ON u.jobID = j.jobID
    WHERE u.managerID = :managerID
    ORDER BY u.fName, u.lName
");
$stmt->bindValue(':managerID', $managerID, SQLITE3_INTEGER);
$result = $stmt->execute();

$caseHandlers = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $caseHandlers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case Handlers</title>
    <link rel="stylesheet" href="../css/ViewAllCases.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
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
    <h2>Case Handlers You Manage</h2>
    <?php if (!empty($caseHandlers)): ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Job Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($caseHandlers as $handler): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($handler['userID']); ?></td>
                        <td><?php echo htmlspecialchars($handler['fName']); ?></td>
                        <td><?php echo htmlspecialchars($handler['mName']); ?></td>
                        <td><?php echo htmlspecialchars($handler['lName']); ?></td>
                        <td><?php echo htmlspecialchars($handler['email']); ?></td>
                        <td><?php echo htmlspecialchars($handler['job']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No case handlers found under your management.</p>
    <?php endif; ?>
</main>
<footer>
    <p>&copy; <span id="year"></span> XLN</p>
</footer>
<script>
    document.getElementById("year").innerHTML = new Date().getFullYear();
</script>
</body>
</html>