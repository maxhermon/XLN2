<?php
session_start();
require 'db_connection.php';
$db = connectToDatabase();

if ($_SESSION['jobID'] != 3) {
    header("Location: Homepage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['tempCaseID'])) {
        $tempCaseID = $_POST['tempCaseID'];
        
        try {
            if (!$db->exec('BEGIN TRANSACTION')) {
                throw new Exception("Failed to start transaction.");
            }

            if ($_POST['action'] === 'accept') {
                $insert_sql = "INSERT INTO cases (userID, customerID, reasonID, description, created, status) 
                               SELECT userID, customerID, reasonID, description, datetime('now'), 1 
                               FROM temp_cases WHERE tempCaseID = :tempCaseID";
                $insert_stmt = $db->prepare($insert_sql);
                $insert_stmt->bindValue(':tempCaseID', $tempCaseID, SQLITE3_INTEGER);
                if (!$insert_stmt->execute()) {
                    throw new Exception("Error inserting case: " . $db->lastErrorMsg());
                }

                $delete_sql = "DELETE FROM temp_cases WHERE tempCaseID = :tempCaseID";
                $delete_stmt = $db->prepare($delete_sql);
                $delete_stmt->bindValue(':tempCaseID', $tempCaseID, SQLITE3_INTEGER);
                if (!$delete_stmt->execute()) {
                    throw new Exception("Error deleting temp case: " . $db->lastErrorMsg());
                }
            } elseif ($_POST['action'] === 'reject') {
                $delete_sql = "DELETE FROM temp_cases WHERE tempCaseID = :tempCaseID";
                $delete_stmt = $db->prepare($delete_sql);
                $delete_stmt->bindValue(':tempCaseID', $tempCaseID, SQLITE3_INTEGER);
                if (!$delete_stmt->execute()) {
                    throw new Exception("Error deleting temp case: " . $db->lastErrorMsg());
                }
            }
            if (!$db->exec('COMMIT')) {
                throw new Exception("Commit failed.");
            }

            $_SESSION['message'] = ($_POST['action'] === 'accept') 
                ? "Case accepted successfully." 
                : "Case rejected successfully.";
        } catch (Exception $e) {
            $db->exec('ROLLBACK');
            $_SESSION['message'] = "Transaction failed: " . $e->getMessage();
        }

        header("Location: OverridePage.php");
        exit;
    }
}

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

    <div class="container">
        <h2>Conflicting Cases</h2>
        <?php
        $conflictingCases = $db->query("
            SELECT 
                c.caseID AS original_caseID, 
                u1.fname || ' ' || u1.lname AS original_user_name, 
                cu1.name AS original_customer_name, 
                d1.deptName AS original_department_name, 
                r1.reason AS original_reason_name, 
                c.description AS original_description,
                t.tempCaseID AS duplicate_tempCaseID, 
                u2.fname || ' ' || u2.lname AS duplicate_user_name, 
                cu2.name AS duplicate_customer_name, 
                d2.deptName AS duplicate_department_name, 
                r2.reason AS duplicate_reason_name, 
                t.description AS duplicate_description
            FROM temp_cases t
            INNER JOIN cases c 
                ON t.customerID = c.customerID 
                AND t.reasonID = c.reasonID
            LEFT JOIN users u1 ON c.userID = u1.userID
            LEFT JOIN customers cu1 ON c.customerID = cu1.customerID
            LEFT JOIN reasons r1 ON c.reasonID = r1.reasonID
            LEFT JOIN department_reasons dr1 ON r1.reasonID = dr1.reasonID
            LEFT JOIN departments d1 ON dr1.departmentID = d1.departmentID
            LEFT JOIN users u2 ON t.userID = u2.userID
            LEFT JOIN customers cu2 ON t.customerID = cu2.customerID
            LEFT JOIN reasons r2 ON t.reasonID = r2.reasonID
            LEFT JOIN department_reasons dr2 ON r2.reasonID = dr2.reasonID
            LEFT JOIN departments d2 ON dr2.departmentID = d2.departmentID
        ");

        if (!$conflictingCases) {
            die("Error in query: " . $db->lastErrorMsg());
        }

        while ($row = $conflictingCases->fetchArray(SQLITE3_ASSOC)) {
            echo "<div class='case-group'>";
            echo "<h3>Original Case</h3>";
            echo "<table class='styled-table'>";
            echo "<tr><th>Case ID</th><th>User Name</th><th>Customer Name</th><th>Department</th><th>Reason</th><th>Description</th></tr>";
            echo "<tr>";
            echo "<td>{$row['original_caseID']}</td>";
            echo "<td>{$row['original_user_name']}</td>";
            echo "<td>{$row['original_customer_name']}</td>";
            echo "<td>{$row['original_department_name']}</td>";
            echo "<td>{$row['original_reason_name']}</td>";
            echo "<td>{$row['original_description']}</td>";
            echo "</tr></table>";

            echo "<h3>Duplicate Case</h3>";
            echo "<table class='styled-table'>";
            echo "<tr><th>Temp Case ID</th><th>User Name</th><th>Customer Name</th><th>Department</th><th>Reason</th><th>Description</th><th>Actions</th></tr>";
            echo "<tr>";
            echo "<td>{$row['duplicate_tempCaseID']}</td>";
            echo "<td>{$row['duplicate_user_name']}</td>";
            echo "<td>{$row['duplicate_customer_name']}</td>";
            echo "<td>{$row['duplicate_department_name']}</td>";
            echo "<td>{$row['duplicate_reason_name']}</td>";
            echo "<td>{$row['duplicate_description']}</td>";
            echo "<td>
                <form method='POST'>
                    <input type='hidden' name='tempCaseID' value='{$row['duplicate_tempCaseID']}'>
                    <button type='submit' name='action' value='accept'>Accept</button>
                    <button type='submit' name='action' value='reject'>Reject</button>
                </form>
            </td>";
            echo "</tr></table>";
            echo "</div><br>";
        }
        ?>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='message'>{$_SESSION['message']}</p>";
            unset($_SESSION['message']);
        }
        ?>
    </div>
</body>
</html>

