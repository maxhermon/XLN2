<?php
session_start();
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
        logActivity($userID, "Created new case #$newCaseID", "Opened    ");

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
    <title>Create Case</title>
    <link rel="stylesheet" href="../css/CaseCreation.css">
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
                    <a href="javascript:void(0)" class="dropbtn">
                        <i class="fa-solid fa-circle-user"></i> MyAccount
                    </a>
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
        <form method="POST" action="CaseCreation.php">
            
            <label for="departmentID">Department:</label>
            <select id="departmentID" name="departmentID" required>
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['departmentID']; ?>">
                        <?php echo $dept['deptName']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="reasonID">Reason:</label>
            <select id="reasonID" name="reasonID" required>
                <option value="">-- Select Reason --</option>
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

            <label for="description">Notes:</label>
            <textarea id="description" name="description" rows="4"></textarea>

            <button type="submit" name="submitCase">Submit Case</button>
        </form>
    </main>

    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>

    <script>
    document.getElementById("year").innerHTML = new Date().getFullYear();

    const deptSelect = document.getElementById('departmentID');
    const reasonSelect = document.getElementById('reasonID');

    deptSelect.addEventListener('change', function() {
        const deptID = this.value;
        reasonSelect.innerHTML = '<option value="">-- Select Reason --</option>';

        if (!deptID) {
            return;
        }

        fetch('GetReasons.php?departmentID=' + deptID)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.reasonID;
                    opt.textContent = item.reason;
                    reasonSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Failed to load reasons:', err);
            });
    });
    </script>
</body>
</html>
