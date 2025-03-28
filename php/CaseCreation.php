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

    $departmentID = $_POST['departmentID']   ?? null;
    $reasonID     = $_POST['reasonID']       ?? null;
    $customerID   = $_POST['customerID']     ?? null;
    $description  = $_POST['description']    ?? '';

    // Check if we need to create a new customer
    if ($customerID === 'addNew') {
        $customerName = $_POST['newCustomerName'] ?? null;
        $customerEmail = $_POST['newCustomerEmail'] ?? null;

        if ($customerName && $customerEmail) {
            // Insert new customer
            $sql = "INSERT INTO customers (name, email) VALUES (:name, :email);";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':name', $customerName, SQLITE3_TEXT);
            $stmt->bindValue(':email', $customerEmail, SQLITE3_TEXT);
            $stmt->execute();

            // Get the newly created customer ID
            $customerID = $db->lastInsertRowID();
        } else {
            // Handle error: customer details not provided
            $_SESSION['error'] = "Customer name and email are required.";
            header('Location: CaseCreation.php');
            exit;
        }
    }

    // Check for duplicate cases
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

        logActivity($userID, $newCaseID, "Created new case #$newCaseID", "Opened    ");

        header('Location: caseCreated.php');
        exit;
    } else {
        $_SESSION['duplicateIDs'] = $duplicateCases;
        $_SESSION['proposedCase'] = [
            'userID'      => $userID,
            'reasonID'    => $reasonID,
            'description' => $description,
            'customerID'  => $customerID,
        ];
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
    <style>
        #newCustomerFields {
            display: none;
            margin-top: 10px;
        }
    </style>
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
                        <a href="../php/ProfilePage.php">View Profile</a>
                        <a href="logOut.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Create a Case</h2>
        <?php 
        if (isset($_SESSION['error'])) {
            echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['error']) . "</p>";
            unset($_SESSION['error']);
        }
        ?>
        <form method="POST" action="CaseCreation.php" id="caseForm">
            
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
                <option value="addNew">+ Add New Customer</option>
            </select>

            <div id="newCustomerFields">
                <label for="newCustomerName">Customer Full Name:</label>
                <input type="text" id="newCustomerName" name="newCustomerName">

                <label for="newCustomerEmail">Customer Email:</label>
                <input type="email" id="newCustomerEmail" name="newCustomerEmail">
            </div>

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
    const customerSelect = document.getElementById('customerID');
    const newCustomerFields = document.getElementById('newCustomerFields');
    const newCustomerName = document.getElementById('newCustomerName');
    const newCustomerEmail = document.getElementById('newCustomerEmail');


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


    customerSelect.addEventListener('change', function() {
        if (this.value === 'addNew') {
            newCustomerFields.style.display = 'block';
            newCustomerName.required = true;
            newCustomerEmail.required = true;
        } else {
            newCustomerFields.style.display = 'none';
            newCustomerName.required = false;
            newCustomerEmail.required = false;
        }
    });


    document.getElementById('caseForm').addEventListener('submit', function(event) {
        if (customerSelect.value === 'addNew') {
            if (!newCustomerName.value || !newCustomerEmail.value) {
                event.preventDefault();
                alert('Please fill in the customer name and email.');
            }
        }
    });
    </script>
</body>
</html>