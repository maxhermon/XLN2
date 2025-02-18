<?php


// For simplicity, put everything in one file (create_case.php).
// In production, you’d typically separate concerns.

require 'db_connection.php';  // This has your connectToDatabase() function
$db = connectToDatabase();    // Reuse the same connection

// Step A: Load all departments for the first dropdown
$departments = [];
$deptResult = $db->query("SELECT departmentID, deptName FROM departments");
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

// We'll see if the user posted a department selection
$selectedDepartmentID = null;
$reasonsForDepartment = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if user selected a department in the first step
    if (isset($_POST['selectDepartment'])) {
        // The user just clicked “Load reasons” (or similar)
        $selectedDepartmentID = $_POST['departmentID'] ?? null;

        // If department is set, load the reasons
        if ($selectedDepartmentID) {
            $stmt = $db->prepare("SELECT reasonID, reason 
                                  FROM reasons 
                                  WHERE departmentID = :deptID");
            $stmt->bindValue(':deptID', $selectedDepartmentID, SQLITE3_INTEGER);
            $rResult = $stmt->execute();
            while ($rRow = $rResult->fetchArray(SQLITE3_ASSOC)) {
                $reasonsForDepartment[] = $rRow;
            }
        }
    }

    // Check if user finally submitted the entire case form
    if (isset($_POST['submitCase'])) {
        // Here you would handle saving the form to your `cases` table
        $deptID   = $_POST['departmentID'] ?? null; // Hidden or re-sent?
        $reasonID = $_POST['reasonID']     ?? null;
        $status   = $_POST['status']       ?? null;
        $notes    = $_POST['notes']        ?? '';

        // Validate and insert into 'cases' as needed
        // ...
        
        // For now, we can just echo out or redirect
        echo "<p>Case submitted successfully for Dept $deptID, Reason $reasonID</p>";
        // exit or redirect to success page
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Case</title>
    <link rel="stylesheet" href="../css/CaseCreation.css">
</head>
<body>

    <header>
        <img class="logo" src="images/xlnLogo.png" alt="XLN Logo">
        <nav>
            <ul>
                <li><a href="#">MyAccount</a></li>
                <li><a href="#">XLN Home</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Create a Case</h2>
        <form action="CaseCreation.php" method="POST">
            <!-- Case ID and Timestamp will be generated automatically by PHP -->
            <label for="departmentID">Department:</label>
            <select id="departmentID" name="departmentID" required>
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['departmentID'];?>"
                    <?php
                    if ($selectedDepartmentID == $dept['departmentID']) {
                        echo 'selected';
                    }
                    ?>>
                    <?php echo $dept['deptName']; ?>
                    </option>
                    <?php endforeach ?>
            </select>
            <button type="submit" name= "selectDepartment">Select Department</button>
            </form>


            <?php if ($selectedDepartmentID && !empty($reasonsForDepartment)): ?>
            
            <form action="CaseCreation.php" method="POST">

            <input type="hidden" name="departmentID"
                value="<?php echo htmlspecialchars($selectedDepartmentID);?>">

            <label for="reasonID">Reason:</label>
            <select id="reasonID" name="reasonID" required>
                <option value="">-- Select Reason --</option>
                <?php foreach ($reasonsForDepartment as $reason): ?>
                    <option value="<?php echo $reason['reasonID']; ?>">
                        <?php echo $reason['reason']; ?>
                    </option>
                    <?php endforeach; ?>
            </select>


            <!-- Status field (Open/Closed) -->
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="">Select Status</option>
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>

            <!-- Notes field (optional) -->
            <label for="notes">Notes (Optional):</label>
            <textarea id="notes" name="notes" rows="4"></textarea>

            <button type="submit" name="submitCase">Submit Case</button>
        </form>
        
        <?php elseif ($selectedDepartmentID && empty($reasonsForDepartment)): ?>
        <p>No reasons found for that department.</p>
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
