<?php

session_start();

require 'db_connection.php';  
$db = connectToDatabase(); 

$cases = $_SESSION['duplicateIDs'];

if (!empty($cases)) {
    //dynamic placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($cases), '?'));

    $sql = "SELECT cases.caseID, cases.created, departments.deptName, reasons.reason, cases.description,
                   cases.status, users.fName || ' ' || users.lName AS CaseHandler, jobs.job,
                   customers.name AS customerName, customers.email AS customerEmail
            FROM cases
            INNER JOIN users ON cases.userID = users.userID
            INNER JOIN reasons ON cases.reasonID = reasons.reasonID
			INNER JOIN department_reasons ON reasons.reasonID = department_reasons.reasonID
            INNER JOIN departments ON department_reasons.departmentID = departments.departmentID
            INNER JOIN customers ON cases.customerID = customers.customerID
            INNER JOIN jobs ON users.jobID = jobs.jobID
            WHERE cases.caseID IN ($placeholders);";

    $stmt = $db->prepare($sql);

    //bind caseID values dynamically
    foreach ($cases as $index => $caseID) {
        $stmt->bindValue($index + 1, $caseID, SQLITE3_INTEGER);
    }

    $result = $stmt->execute();

    $caseDetails = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $caseDetails[] = $row;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../css/SimilarCaseExists.css">
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

    <div class="container">
        <h1>Similar Case Exists</h1>
        <p>A similar case has been found. Please review the details below:</p>
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Creation Timestamp</th>
                    <th>Department</th>
                    <th>Case Reason</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Handler</th>
                    <th>Role</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                </tr>
            </thead>
            <tbody>
        <?php if (!empty($cases)): ?>
            <?php foreach ($caseDetails as $case): ?>
                <tr>
                    <td><?= htmlspecialchars($case['caseID']) ?></td>
                    <td><?= htmlspecialchars($case['created']) ?></td>
                    <td><?= htmlspecialchars($case['deptName']) ?></td>
                    <td><?= htmlspecialchars($case['reason']) ?></td>
                    <td><?= htmlspecialchars($case['description']) ?></td>
                    <td><?= $case['status'] == 1 ? 'Open' : 'Closed' ?></td>
                    <td><?= htmlspecialchars($case['CaseHandler']) ?></td>
                    <td><?= htmlspecialchars($case['job']) ?></td>
                    <td><?= htmlspecialchars($case['customerName']) ?></td>
                    <td><?= htmlspecialchars($case['customerEmail']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No cases found</td>
            </tr>
        <?php endif; ?>
    </tbody>
        </table>

        <div class="buttons">
            <button onclick="window.location.href='CaseCreation.php'">Create Another Case</button>
        </div>
    </div>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
        <script>
            document.getElementById("year").innerHTML = new Date().getFullYear();
        </script>
    </footer>
    
</body>
</html></select></div>