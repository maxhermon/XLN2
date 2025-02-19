<?php

session_start();

require 'db_connection.php';  
$db = connectToDatabase(); 

$cases = $_SESSION['duplicateIDs'];

if (!empty($cases)) {
    // Generate dynamic placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($cases), '?'));

    // Second query: Retrieve case details for all matched case IDs
    $sql = "SELECT cases.caseID, cases.created, departments.deptName, reasons.reason, cases.description,
                   cases.status, users.fName || ' ' || users.lName AS CaseHandler, jobs.job,
                   customers.name AS customerName, customers.email AS customerEmail
            FROM cases
            INNER JOIN users ON cases.userID = users.userID
            INNER JOIN reasons ON cases.reasonID = reasons.reasonID
            INNER JOIN departments ON reasons.departmentID = departments.departmentID
            INNER JOIN customers ON cases.customerID = customers.customerID
            INNER JOIN jobs ON users.jobID = jobs.jobID
            WHERE cases.caseID IN ($placeholders);";

    $stmt = $db->prepare($sql);

    // Bind caseID values dynamically
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
    <title>Similar Case Exists</title>
    <link rel="stylesheet" href="../css/SimilarCaseExists.css">
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