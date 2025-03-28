<?php
session_start();

require 'db_connection.php';
$db = connectToDatabase();




//if not manager then redirect to homepage
if ($_SESSION['jobID'] != 3) {
    header("Location: Homepage.php");
    exit;
}

//get all handlers that are under the manager
$handler_sql = "SELECT userID FROM users WHERE managerID = :managerID;";

$handler_stmt = $db->prepare($handler_sql);
$handler_stmt->bindValue(':managerID', $_SESSION['userID'], SQLITE3_INTEGER);
$handler_result = $handler_stmt->execute();

$handlers = [];
while ($row = $handler_result->fetchArray(SQLITE3_ASSOC)) {
    $handlers[] = $row['userID'];
}

$handler_stmt->close();



//get all cases that are assigned to the handlers
$temp_cases_sql = "SELECT tempCaseID, departments.deptName, reasons.reasonID, reasons.reason, description, 
                    users.fName || ' ' || users.lName as handler,
                    customers.customerID, customers.name, customers.email
                    FROM temp_cases
                    INNER JOIN reasons on reasons.reasonID = temp_cases.reasonID
                    INNER JOIN department_reasons on department_reasons.reasonID = reasons.reasonID
                    INNER JOIN departments on department_reasons.departmentID = departments.departmentID
                    INNER JOIN users on users.userID = temp_cases.userID
                    INNER JOIN customers on customers.customerID = temp_cases.customerID
                    WHERE temp_cases.userID IN (" . implode(',', array_fill(0, count($handlers), '?')) . ");";
           
$temp_cases_stmt = $db->prepare($temp_cases_sql);

foreach ($handlers as $index => $handler) {
    $temp_cases_stmt->bindValue($index + 1, $handler, SQLITE3_INTEGER);
}

$temp_cases_result = $temp_cases_stmt->execute();

$caseDetails = [];
while ($row = $temp_cases_result->fetchArray(SQLITE3_ASSOC)) {
    $caseDetails[] = $row;
}

$temp_cases_stmt->close();

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Override Case Decision</title>
    <link rel="stylesheet" href="../css/OverridePage.css">
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
            <?php
            if (empty($caseDetails)) {
                echo "<h1>No cases to display</h1>";
            } else {
                foreach ($caseDetails as $case) {
                    echo "<div class='container'>";
                    echo "<h1>Proposed Case:</h1>";
                    ?>
                    <table>
                        <tr>
                            <th>Department</th>
                            <th>Case Reason</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Handler</th>
                            <th>Customer Name</th>
                            <th>Customer Email</th>
                        </tr>
                        <tr>
                            <td><?php echo $case['deptName']; ?></td>
                            <td><?php echo $case['reason']; ?></td>
                            <td><?php echo $case['description']; ?></td>
                            <td>Pending</td>
                            <td><?php echo $case['handler']; ?></td>
                            <td><?php echo $case['name']; ?></td>
                            <td><?php echo $case['email']; ?></td>
                        </tr>
                    </table>
                    
                <?php
            echo "<div class='existing-cases'>";
            echo "<br><br><h1>Existing Similar Case(s):</h1>";

            $sql = "SELECT cases.caseID
                    FROM cases
                    INNER JOIN reasons   ON cases.reasonID    = reasons.reasonID
                    INNER JOIN customers ON cases.customerID  = customers.customerID
                    WHERE status = 1 
                    AND reasons.reasonID   = :reasonID
                    AND customers.customerID = :customerID;";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':reasonID', $case['reasonID'], SQLITE3_INTEGER);
                    $stmt->bindValue(':customerID', $case['customerID'], SQLITE3_INTEGER);

                    $result = $stmt->execute();
                    $existingCases = [];
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $existingCases[] = $row;
                    }
                    $stmt->close();

                    $caseIDs = array_column($existingCases, 'caseID');

                    if (!empty($caseIDs)) {
                        // Corrected SQL with proper placeholders
                        $cases_sql = "SELECT cases.caseID, created, departments.deptName, reasons.reason,
                                    cases.description, status, 
                                    users.fName || ' ' || users.lName as handler,
                                    customers.name, customers.email
                                    FROM cases
                                    INNER JOIN reasons ON cases.reasonID = reasons.reasonID
                                    INNER JOIN department_reasons ON department_reasons.reasonID = reasons.reasonID
                                    INNER JOIN departments ON department_reasons.departmentID = departments.departmentID
                                    INNER JOIN users ON cases.userID = users.userID
                                    INNER JOIN customers ON cases.customerID = customers.customerID
                                    WHERE cases.caseID IN (" . implode(',', array_fill(0, count($caseIDs), '?')) . ")";

                        $cases_stmt = $db->prepare($cases_sql);

                        // Bind caseIDs correctly
                        foreach ($caseIDs as $index => $caseID) {
                            $cases_stmt->bindValue($index + 1, $caseID, SQLITE3_INTEGER);
                        }

                        $cases_result = $cases_stmt->execute();

                        // Fetch case data
                        $cases = [];
                        while ($row = $cases_result->fetchArray(SQLITE3_ASSOC)) {
                            $cases[] = $row;
                        }
                        $cases_stmt->close();
                    } else {
                        $cases = []; // If no cases, ensure it's an empty array
                    }

                    ?>

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
                                <th>Customer Name</th>
                                <th>Customer Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($cases)) {
                                echo "<tr><td colspan='9'>No cases available</td></tr>";
                            } else {
                                foreach ($cases as $case) {
                                    // Convert status to readable format
                                    $statusText = ($case['status'] == 0) ? "Closed" : "Open";
                                    
                                    echo "<tr>
                                            <td>{$case['caseID']}</td>
                                            <td>{$case['created']}</td>
                                            <td>{$case['deptName']}</td>
                                            <td>{$case['reason']}</td>
                                            <td>{$case['description']}</td>
                                            <td>{$statusText}</td>
                                            <td>{$case['handler']}</td>
                                            <td>{$case['name']}</td>
                                            <td>{$case['email']}</td>
                                        </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <button>Accept Proposed case</button>
                    <button>Reject Proposed case</button>
                </div>
                <?php
                }
            }
         ?>



    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
        <script>
            document.getElementById("year").innerHTML = new Date().getFullYear();
        </script>
    </footer>
</body>
</html>
