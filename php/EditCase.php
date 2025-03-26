<?php

session_start();
require 'db_connection.php';
$db = connectToDatabase();

$caseID = isset($_GET['uid']) ? $_GET['uid'] : null;
$errorMessage = '';
$successMessage = '';
$caseData = null;

$deptStmt = $db->prepare("SELECT * FROM departments ORDER BY deptName");
$deptResult = $deptStmt->execute();
$departments = [];
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

$reasonStmt = $db->prepare("SELECT r.reasonID, r.reason, dr.departmentID 
                            FROM reasons r 
                            INNER JOIN department_reasons dr ON r.reasonID = dr.reasonID
                            ORDER BY r.reason");

$reasonResult = $reasonStmt->execute();
$reasons = [];
while ($row = $reasonResult->fetchArray(SQLITE3_ASSOC)) {
    $reasons[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = isset($_POST['caseStatus']) ? 0 : 1;
    $description = $_POST['caseNotes'];
    $caseID = $_POST['caseID'];
    $oldStatus = $_POST['oldStatus'];
    $reasonID = $_POST['reasonID']; 
    $userID = $_SESSION['userID']; // Get the user ID from the session

    $closedDate = null;
    if ($oldStatus == 1 && $newStatus == 0) {
        $closedDate = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description, closed = :closedDate, reasonID = :reasonID WHERE caseID = :caseID");
        $stmt->bindValue(':closedDate', $closedDate, SQLITE3_TEXT);
    } elseif ($newStatus == 1 && $oldStatus == 0) {
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description, closed = NULL, reasonID = :reasonID WHERE caseID = :caseID");
    } else {
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description, reasonID = :reasonID WHERE caseID = :caseID");
    }
    
    $stmt->bindValue(':status', $newStatus, SQLITE3_INTEGER);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':reasonID', $reasonID, SQLITE3_INTEGER);
    $stmt->bindValue(':caseID', $caseID, SQLITE3_INTEGER);
    
    $result = $stmt->execute();
    
    if ($result) {
        $successMessage = "Case updated successfully!";
        // Log the activity with status text
        $statusText = $newStatus == 1 ? 'Open' : 'Closed';
        logActivity($userID, "Updated case #$caseID", $statusText);
    } else {
        $errorMessage = "Failed to update case: " . $db->lastErrorMsg();
    }
}

if ($caseID) { 
    $stmt = $db->prepare("SELECT c.*, 
                        d.deptName AS department_name, 
                        d.departmentID,
                        r.reason AS reason_name,
                        r.reasonID,
                        cu.name AS customer_name
                        FROM cases c
                        LEFT JOIN reasons r ON c.reasonID = r.reasonID
                        LEFT JOIN department_reasons dr ON r.reasonID = dr.reasonID
                        LEFT JOIN departments d ON dr.departmentID = d.departmentID
                        LEFT JOIN customers cu ON c.customerID = cu.customerID
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
    <title>Edit Case</title>
    <link rel="stylesheet" href="../css/EditCase.css">
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
    <main>
        <div class="container">
            <h1>Edit Case</h1>
            
            <?php if ($errorMessage): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($successMessage): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($caseData): ?>
                <form id="editCaseForm" method="POST">
                    <input type="hidden" name="caseID" value="<?php echo $caseData['caseID']; ?>">
                    <input type="hidden" name="oldStatus" value="<?php echo $caseData['status']; ?>">
                    
                    <label for="departmentID">Department:</label>
                    <select id="departmentID" name="departmentID">
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo $department['departmentID']; ?>" <?php echo ($department['departmentID'] == $caseData['departmentID']) ? 'selected' : ''; ?>>
                                <?php echo $department['deptName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="reasonID">Reason:</label>
                    <select id="reasonID" name="reasonID" required>
                    </select>

                    <label for="customerName">Customer Name:</label>
                    <input type="text" id="customerName" name="customerName" value="<?php echo $caseData['customer_name']; ?>" readonly>

                    <label for="openedDate">Opened Date:</label>
                    <input type="text" id="openedDate" name="openedDate" value="<?php echo $caseData['created']; ?>" readonly>
                    
                    <label for="caseNotes">Case Notes:</label>
                    <textarea id="caseNotes" name="caseNotes" rows="4" required><?php echo $caseData['description']; ?></textarea>
                    
                    <div class="toggle-container">
                    <span class="toggle-label">Case Status:</span>
                    <span id="statusText" class="toggle-status"><?php echo $caseData['status'] == 1 ? 'Open' : 'Closed'; ?></span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="caseStatus" name="caseStatus" <?php echo $caseData['status'] == 0 ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    
                    </div>

                    <button type="submit">Save Changes</button>

                    <a href="ViewAllCases.php" class="button">Back to All Cases</a>
                </form>
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
        
        document.getElementById('caseStatus').addEventListener('change', function() {
            document.getElementById('statusText').textContent = this.checked ? 'Closed' : 'Open';
        });

        const allReasons = <?php echo json_encode($reasons); ?>;
        const currentReasonID = <?php echo $caseData ? $caseData['reasonID'] : 'null'; ?>;
        
        function updateReasonDropdown() {
            const departmentID = document.getElementById('departmentID').value;
            const reasonDropdown = document.getElementById('reasonID');
            
            reasonDropdown.innerHTML = '';
            
            const filteredReasons = allReasons.filter(reason => 
                reason.departmentID == departmentID
            );
            
            filteredReasons.forEach(reason => {
                const option = document.createElement('option');
                option.value = reason.reasonID;
                option.textContent = reason.reason;
                
                if (reason.reasonID == currentReasonID) {
                    option.selected = true;
                }
                
                reasonDropdown.appendChild(option);
            });
            
            if (filteredReasons.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No reasons available for this department';
                reasonDropdown.appendChild(option);
            }
        }
        

        document.addEventListener('DOMContentLoaded', function() {
            updateReasonDropdown();
        });
        
       document.getElementById('departmentID').addEventListener('change', updateReasonDropdown);
    </script>
</body>
</html>