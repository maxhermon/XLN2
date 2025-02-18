<?php
// Initialize variables
$caseID = isset($_GET['uid']) ? $_GET['uid'] : null;
$errorMessage = '';
$successMessage = '';
$caseData = null;

// Database connection
$db = new SQLite3('../data/XLN_new_DBA.db');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = isset($_POST['caseStatus']) ? 1 : 0; // 1 for Open, 0 for Closed
    $description = $_POST['caseNotes'];
    $caseID = $_POST['caseID'];
    $oldStatus = $_POST['oldStatus'];
    
    // Check if status is changing from open to closed
    $closedDate = null;
    if ($oldStatus == 1 && $newStatus == 0) {
        // Case is being closed, set the current timestamp
        $closedDate = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description, closed = :closedDate WHERE caseID = :caseID");
        $stmt->bindValue(':closedDate', $closedDate, SQLITE3_TEXT);
    } elseif ($newStatus == 1 && $oldStatus == 0) {
        // Case is being reopened, clear the closed date
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description, closed = NULL WHERE caseID = :caseID");
    } else {
        // Status not changing, just update status and description
        $stmt = $db->prepare("UPDATE cases SET status = :status, description = :description WHERE caseID = :caseID");
    }
    
    $stmt->bindValue(':status', $newStatus, SQLITE3_INTEGER);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':caseID', $caseID, SQLITE3_INTEGER);
    
    $result = $stmt->execute();
    
    if ($result) {
        $successMessage = "Case updated successfully!";
    } else {
        $errorMessage = "Failed to update case: " . $db->lastErrorMsg();
    }
}

// Fetch case data if caseID is provided
if ($caseID) {
    $stmt = $db->prepare("SELECT c.*, 
                        d.deptName AS department_name, 
                        r.reason AS reason_name,
                        cu.name AS customer_name
                FROM cases c
                LEFT JOIN reasons r ON c.reasonID = r.reasonID
                LEFT JOIN departments d ON r.departmentID = d.departmentID
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
                    
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" value="<?php echo $caseData['department_name']; ?>" readonly>
                    
                    <label for="reason">Reason:</label>
                    <input type="text" id="reason" name="reason" value="<?php echo $caseData['reason_name']; ?>" readonly>
                    
                    <label for="customerName">Customer Name:</label>
                    <input type="text" id="customerName" name="customerName" value="<?php echo $caseData['customer_name']; ?>" readonly>

                    <label for="openedDate">Opened Date:</label>
                    <input type="text" id="openedDate" name="openedDate" value="<?php echo $caseData['created']; ?>" readonly>
                    
                    <label for="caseNotes">Case Notes:</label>
                    <textarea id="caseNotes" name="caseNotes" rows="4" required><?php echo $caseData['description']; ?></textarea>
                    
                    <label for="openedDate">Opened Date:</label>
                    <input type="text" id="openedDate" name="openedDate" value="<?php echo $caseData['created']; ?>" readonly> 

                    <label for="caseStatus">Close case:</label>
                    <label class="switch">
                        <input type="checkbox" id="caseStatus" name="caseStatus" <?php echo $caseData['status'] == 1 ? 'checked' : ''; ?>>
                        <span class="slider round"></span>
                    </label>

                    <span id="statusText"><?php echo $caseData['status'] == 1 ? 'Open' : 'Closed'; ?></span>

                    <button type="submit">Save Changes</button>
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
        
        // Update status text when checkbox changes
        document.getElementById('caseStatus').addEventListener('change', function() {
            document.getElementById('statusText').textContent = this.checked ? 'Open' : 'Closed';
        });
    </script>
</body>
</html>