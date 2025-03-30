<?php

$customerID = isset($_GET['cid']) ? $_GET['cid'] : null;
$customerData = null;
$db = new SQLite3('../data/XLN_new_DBA.db');

if ($customerID) {
    $stmt = $db->prepare("SELECT c.*, 
                    COUNT(cs.caseID) AS total_cases
                FROM customers c
                LEFT JOIN cases cs ON c.customerID = cs.customerID
                WHERE c.customerID = :customerID
                GROUP BY c.customerID, c.name, c.email");
    $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $customerData = $result->fetchArray(SQLITE3_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer</title>
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
            <h1>View Customer</h1>
            
            <?php if ($customerData): ?>
                <div id="viewCustomerForm">
                    <div class="form-group">
                        <label>Customer ID:</label>
                        <p><?php echo $customerData['customerID']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>Name:</label>
                        <p><?php echo $customerData['name']; ?></p>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <p><?php echo $customerData['email']; ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Total Cases:</label>
                        <p><?php echo $customerData['total_cases']; ?></p>
                        <a href="ViewCustomerCases.php?cid=<?php echo $customerData['customerID']; ?>" class="button2">View All Cases</a>
                    </div>
                    
                    <div class="actions">
                        <a href="ViewAllCustomers.php" class="button">Back to All Customers</a>
                        <a href="EditCustomer.php?cid=<?php echo $customerData['customerID']; ?>" class="button">Edit Customer</a>
                    </div>
                </div>
            <?php else: ?>
                <p>No customer found or invalid customer ID.</p>
                <a href="ViewAllCustomers.php">Back to All Customers</a>
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