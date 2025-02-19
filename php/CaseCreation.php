<?php

session_start();


require 'db_connection.php';  
$db = connectToDatabase();    

$departments = [];
$deptResult = $db->query("SELECT departmentID, deptName FROM departments");
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

$customers = [];
$cResult = $db->query("SELECT customerID, name FROM customers");
while ($row = $cResult->fetchArray(SQLITE3_ASSOC)) {
    $customers[] = $row;
}

$selectedDepartmentID = null;
$reasonsForDepartment = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['selectDepartment'])) {
        $selectedDepartmentID = $_POST['departmentID'] ?? null;

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

    if (isset($_POST['submitCase'])) {
        
        if (!isset($_SESSION['userID'])) {
            header("Location: LoginPage.php");
            exit;
        }

        $userID = $_SESSION['userID'];

        $deptID = $_POST['departmentID'] ?? null;
        $reasonID = $_POST['reasonID']     ?? null;
        $customerID = $_POST['customerID'] ?? null;
        $description    = $_POST['description']        ?? '';

        $createdTime = date('Y-m-d H:i:s');

        $sql = "INSERT into cases (userID, reasonID, description, status, created, closed, customerID)
        VALUES (:userID, :reasonID, :description, :status, :created, :closed, :customerID)";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
        $stmt->bindValue(':reasonID', $reasonID, SQLITE3_INTEGER);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':status', 1, SQLITE3_INTEGER);
        $stmt->bindValue(':created', $createdTime, SQLITE3_TEXT);
        $stmt->bindValue(':closed', null, SQLITE3_NULL);
        $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);

        $stmt->execute();

        

        $newCaseID = $db->lastInsertRowID();
        $_SESSION['caseID'] = $newCaseID;
        header('Location: CaseCreated.php');
        exit();
        
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
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
  />
</head>
<body>
    <header>
        <a href="../html/Homepage.html"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <nav>
            <ul class="left-menu">
                <li><a href="../html/Homepage.html"><i class="fa-solid fa-house"></i> XLN Home</a></li>
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
        <h2>Create a Case</h2>
        <form action="CaseCreation.php" method="POST">
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

            <label for="customerID">Customer:</label>
            <select id="customerID" name="customerID" required>
                <option value="">-- Select Customer --</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo $customer['customerID']; ?>">
                        <?php echo $customer['name']; ?>
                    </option>
                    <?php endforeach; ?>
            </select>


            

            <!-- Notes field (optional) -->
            <label for="description">Notes:</label>
            <textarea id="description" name="description" rows="4"></textarea>

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
<?php


session_start();


require 'db_connection.php';  
$db = connectToDatabase();    

$departments = [];
$deptResult = $db->query("SELECT departmentID, deptName FROM departments");
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

$customers = [];
$cResult = $db->query("SELECT customerID, name FROM customers");
while ($row = $cResult->fetchArray(SQLITE3_ASSOC)) {
    $customers[] = $row;
}

$selectedDepartmentID = null;
$reasonsForDepartment = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['selectDepartment'])) {
        $selectedDepartmentID = $_POST['departmentID'] ?? null;

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

    if (isset($_POST['submitCase'])) {
        
        if (!isset($_SESSION['userID'])) {
            header("Location: LoginPage.php");
            exit;
        }

        $userID = $_SESSION['userID'];

        $deptID = $_POST['departmentID'] ?? null;
        $reasonID = $_POST['reasonID']     ?? null;
        $status = $_POST['status']       ?? null;
        $customerID = $_POST['customerID'] ?? null;
        $description    = $_POST['description']        ?? '';

        $password = $_POST['password'] ?? null;

        echo "<br><br><br><br>";
        echo "reasonID: $reasonID";
        echo "<br>";
        echo "customerID: $customerID";
        echo "<br><br><br><br>";

        $sql = "SELECT cases.caseID
        FROM cases
        INNER JOIN reasons ON cases.reasonID = reasons.reasonID
        INNER JOIN customers ON cases.customerID = customers.customerID
        WHERE status = 1
        AND reasons.reasonID = :reasonID
        AND customers.customerID = :customerID;";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':reasonID', $reasonID, SQLITE3_TEXT);
        $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);

        $result = $stmt->execute();

        $duplicateCases = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $duplicateCases[] = $row['caseID']; // Extract only caseID values
        }

        if(empty($duplicateCases)){
            $createdTime = date('Y-m-d H:i:s');

            $sql = "INSERT into cases (userID, reasonID, description, status, created, closed, customerID)
            VALUES (:userID, :reasonID, :description, :status, :created, :closed, :customerID)";

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
            $stmt->bindValue(':reasonID', $reasonID, SQLITE3_INTEGER);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            $stmt->bindValue(':status', 1, SQLITE3_INTEGER);
            $stmt->bindValue(':created', $createdTime, SQLITE3_TEXT);
            $stmt->bindValue(':closed', null, SQLITE3_NULL);
            $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);

            $stmt->execute();

            $newCaseID = $db->lastInsertRowID();
            $_SESSION['caseID'] = $newCaseID;
            header('Location: caseCreated.php');
            exit();

        } else {
            $_SESSION['duplicateIDs'] = $duplicateCases;
            header('Location: SimilarCaseExists.php');
            exit();
        }         
        
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
        <img class="logo" src="../xlnLogo.png" alt="XLN Logo">
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

            <label for="customerID">Customer:</label>
            <select id="customerID" name="customerID" required>
                <option value="">-- Select Customer --</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo $customer['customerID']; ?>">
                        <?php echo $customer['name']; ?>
                    </option>
                    <?php endforeach; ?>
            </select>


            

            <!-- Notes field (optional) -->
            <label for="description">Notes:</label>
            <textarea id="description" name="description" rows="4"></textarea>

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
