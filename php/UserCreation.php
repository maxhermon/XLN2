<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['jobID'] != 2) {
    header("Location: LoginPage.php");
    exit;
}

require 'db_connection.php';  
$db = connectToDatabase();   

$departments = [];
$deptResult = $db->query("SELECT departmentID, deptName FROM departments;");
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

$customers = [];
$cResult = $db->query("SELECT customerID, name FROM customers;");
while ($row = $cResult->fetchArray(SQLITE3_ASSOC)) {
    $customers[] = $row;
}

if (isset($_POST['addCaseHandler'])) {

    $fName = $_POST['fName'] ?? null;
    $mName = $_POST['mName'] ?? null;
    $lName = $_POST['lName']     ?? null;
    $email = $_POST['email']       ?? null;
    $password = $_POST['password'] ?? null;
    $jobID = $_POST['jobID'] ?? null;
    $managerID = isset($_POST['managerID']) ? $_POST['managerID'] : NULL;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT into users (fName, mName, lName, email, password, jobID, managerID)
    VALUES (:fName, :mName, :lName, :email, :password, :jobID, :managerID);";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':fName', $fName, SQLITE3_TEXT);
    $stmt->bindValue(':mName', $mName, SQLITE3_TEXT);
    $stmt->bindValue(':lName', $lName, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->bindValue(':jobID', $jobID, SQLITE3_INTEGER);
    if ($managerID === null || $managerID === '') {
        $stmt->bindValue(':managerID', null, SQLITE3_NULL);
    } else {
        $stmt->bindValue(':managerID', $managerID, SQLITE3_INTEGER);
    }

    $stmt->execute();



    header('Location: UserCreated.php');
    exit;
}else{
    $sql = "SELECT jobID, job FROM jobs";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    $jobs = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $jobs[] = [
            'jobID' => $row['jobID'],
            'job' => $row['job']
        ]; 
    }
    


    $sql = "SELECT userID, fName || ' ' || lname AS name FROM users 
    WHERE jobID = 3;";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    $managers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $managers[] = [
            'userID' => $row['userID'],
            'name' => $row['name']
        ];
        
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitCase'])) {

    if (!isset($_SESSION['userID'])) {
        header("Location: LoginPage.php");
        exit;
    }
    $userID      = $_SESSION['userID'];

    $departmentID = $_POST['departmentID']   ?? null; // might not be used directly
    $reasonID     = $_POST['reasonID']       ?? null;
    $customerID   = $_POST['customerID']     ?? null;
    $description  = $_POST['description']    ?? '';

    $sql = "SELECT cases.caseID
            FROM cases
            INNER JOIN reasons   ON cases.reasonID    = reasons.reasonID
            INNER JOIN customers ON cases.customerID  = customers.customerID
            WHERE status = 1
              AND reasons.reasonID   = :reasonID
              AND customers.customerID = :customerID;
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':reasonID',    $reasonID,   SQLITE3_INTEGER);
    $stmt->bindValue(':customerID',  $customerID, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $duplicateCases = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $duplicateCases[] = $row['caseID'];
    }

    if (empty($duplicateCases)) {
        $createdTime = date('Y-m-d H:i:s');
        $sql = "INSERT INTO cases 
                (userID, reasonID, description, status, created, closed, customerID)
                VALUES 
                (:userID, :reasonID, :description, :status, :created, :closed, :customerID)
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':userID',      $userID,       SQLITE3_INTEGER);
        $stmt->bindValue(':reasonID',    $reasonID,     SQLITE3_INTEGER);
        $stmt->bindValue(':description', $description,  SQLITE3_TEXT);
        $stmt->bindValue(':status',      1,             SQLITE3_INTEGER);
        $stmt->bindValue(':created',     $createdTime,  SQLITE3_TEXT);
        $stmt->bindValue(':closed',      null,          SQLITE3_NULL);
        $stmt->bindValue(':customerID',  $customerID,   SQLITE3_INTEGER);
        $stmt->execute();

        $newCaseID = $db->lastInsertRowID();
        $_SESSION['caseID'] = $newCaseID;

        // Log the activity
        logActivity($userID, "Created new case #$newCaseID", "Completed");

        header('Location: caseCreated.php');
        exit;
    } else {
        $_SESSION['duplicateIDs'] = $duplicateCases;
        header('Location: SimilarCaseExists.php');
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>

    <link rel="stylesheet" href="../css/UserCreation.css">
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
        <form action="UserCreation.php" method="POST">
            <h2>User Creation Page</h2>


            <label for="fName"><b>First Name</b></label>
            <input type="text" id="fName" name="fName" required>
            <label for="fName"><b>Middle Name</b></label>
            <input type="text" id="mName" name="mName">
            <label for="lName"><b>Last Name</b></label>
            <input type="text" id="lName" name="lName" required>
            <label for="email"><b>Email</b></label>
            <input type="text" id="email" name="email" required>
            <label for="password"><b>Password</b></label>
            <input type="password" id="password" name="password" required>
            

            <label for="job"><b>Select Job</b></label>
            <select id="job" name="jobID" required onchange="toggleManagerDropdown()">
                <option value="">-- Select Job --</option>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?= htmlspecialchars($job['jobID']) ?>">
                        <?= htmlspecialchars($job['job']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div id="manager-container" style="display: none;">
                <label for="manager"><b>Select Manager</b></label>
                <select id="manager" name="managerID">
                    <option value="">-- Select Manager --</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?= htmlspecialchars($manager['userID']) ?>">
                            <?= htmlspecialchars($manager['name']) ?>
                        </option>
                    <?php endforeach;?>
                </select>
            </div>

            <button type="submit" name="addCaseHandler">Add User</button>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
</body>
<script>
    document.getElementById("year").innerHTML = new Date().getFullYear();
</script>

<script>
function toggleManagerDropdown() {
    let jobDropdown = document.getElementById("job");
    let managerContainer = document.getElementById("manager-container");
    let managerSelect = document.getElementById("manager");

    let requiresManagerJobID = "1";

    if (jobDropdown.value === requiresManagerJobID) {
        managerContainer.style.display = "block";
        managerSelect.removeAttribute("disabled");
    } else {
        managerContainer.style.display = "none";
        managerSelect.setAttribute("disabled", "disabled");
    }
}
</script>

</html>
