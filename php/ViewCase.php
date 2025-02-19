<?php

$caseID = isset($_GET['uid']) ? $_GET['uid'] : null;
$caseData = null;
$db = new SQLite3('../data/XLN_new_DBA.db');

if ($caseID) {
    $stmt = $db->prepare("SELECT c.*, 
                        d.deptName AS department_name, 
                        r.reason AS reason_name,
                        cu.name AS customer_name,
                        u.fname || ' ' || u.lname AS user_name,
                        CASE WHEN c.status = 1 THEN 'Open' ELSE 'Closed' END AS status_text
                FROM cases c
                LEFT JOIN reasons r ON c.reasonID = r.reasonID
                LEFT JOIN departments d ON r.departmentID = d.departmentID
                LEFT JOIN customers cu ON c.customerID = cu.customerID
                LEFT JOIN users u ON c.userID = u.userID
                WHERE c.caseID = :caseID");
    $stmt->bindValue(':caseID', $caseID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $caseData = $result->fetchArray(SQLITE3_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>
    <link rel="stylesheet" href="../css/EditCase.css">
</head>
<body>
<header>
<a href="../html/Homepage.html"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <nav>
            <ul class="left-menu">
                <li><a href="../html/Homepage.html">XLN Home</a></li>
                <li><a href="../html/Contact.html">Contact</a></li>
            </ul>
            <ul class="right-menu">
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">MyAccount</a>
                    <div class="dropdown-content">
                        <a href="#">View Profile</a>
                        <a href="#">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>View Case</h1>
            
            <?php if ($caseData): ?>
                <div id="viewCaseForm">
                    <div class="form-group">
                        <label>Case ID:</label>
                        <p><?php echo $caseData['caseID']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>Case Handler:</label>
                        <p><?php echo $caseData['user_name']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>Department:</label>
                        <p><?php echo $caseData['department_name']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason:</label>
                        <p><?php echo $caseData['reason_name']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Customer Name:</label>
                        <p><?php echo $caseData['customer_name']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Case Status:</label>
                        <p><?php echo $caseData['status_text']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Created Date:</label>
                        <p><?php echo $caseData['created']; ?></p>
                    </div>
                    
                    <?php if ($caseData['closed']): ?>
                    <div class="form-group">
                        <label>Closed Date:</label>
                        <p><?php echo $caseData['closed']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Case Notes:</label>
                        <div class="notes-container">
                            <?php echo nl2br(htmlspecialchars($caseData['description'])); ?>
                        </div>
                    </div>
                    
                    <a href="ViewAllCases.php" class="button">Back to All Cases</a>
                </div>
            <?php else: ?>
                <p>No case found or invalid case ID.</p>
                <a href="ViewAllCases.php">Back to All Cases</a>
            <?php endif; ?>
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