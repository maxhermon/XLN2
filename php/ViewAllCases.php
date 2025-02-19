<!DOCTYPE html>
<html lang="en">

<?php
function getCases($searchBy = '', $searchTerm = '') {
    $db = new SQLite3('../data/XLN_new_DBA.db');

    $sql = "SELECT c.*, 
               d.deptName AS department_name, 
               r.reason AS reason_name,
               cu.name AS customer_name,
               u.fname || ' ' || u.lname AS user_name, 
               CASE WHEN c.status = 1 THEN 'Open' ELSE 'Closed' END AS status_text
        FROM cases c
            LEFT JOIN reasons r ON c.reasonID = r.reasonID
            LEFT JOIN departments d ON r.departmentID = d.departmentID
            LEFT JOIN customers cu ON c.customerID = cu.customerID
            LEFT JOIN users u ON c.userID = u.userID"; 

    
    if (!empty($searchBy) && !empty($searchTerm)) {
        if ($searchBy == 'user_name') {
            $sql .= " WHERE (u.fname || ' ' || u.lname) LIKE :searchTerm";
        } else {
            $sql .= " WHERE $searchBy LIKE :searchTerm";
        }
    }
    $stmt = $db->prepare($sql);
    
    if (!empty($searchBy) && !empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', "%$searchTerm%", SQLITE3_TEXT);
    }

    $result = $stmt->execute();
    $arrayResult = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $arrayResult[] = $row;
    }

    return $arrayResult;
}

$searchBy = isset($_GET['searchBy']) ? $_GET['searchBy'] : '';
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

$cases = getCases($searchBy, $searchTerm);
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Cases</title>
    <link rel="stylesheet" href="../css/ViewAllCases.css">
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
        <h2>View All Cases</h2>
        <form method="GET" action="">
        <label for="searchBy">Search By:</label>
        <select name="searchBy" id="searchBy">
            <option value="caseID">Case ID</option>
            <option value="department_name">Department</option>
            <option value="reason_name">Reason</option>
            <option value="status_text">Status</option>
            <option value="customer_name">Customer Name</option>
            <option value="user_name">Case Handler</option>
        </select>
    <input type="text" name="searchTerm" placeholder="Enter search term">
    <button type="submit">Search</button>
</form>

        <table id="casesTable">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Case Handler</th>
                    <th>Creation Timestamp</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Customer Name</th>
                    <th>Notes</th>
                    <th>Closed Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($cases as $case) : ?>
            <tr>
                <td><?php echo $case['caseID']; ?></td>
                <td><?php echo $case['user_name']; ?></td>
                <td><?php echo $case['created']; ?></td>
                <td><?php echo $case['department_name']; ?></td>
                <td><?php echo $case['reason_name']; ?></td>
                <td><?php echo $case['status_text']; ?></td>
                <td><?php echo $case['customer_name']; ?></td>
                <td><?php echo $case['description']; ?></td>
                <td><?php echo $case['closed']; ?></td>
                <td>
                    <?php if ($case['status'] == 1) : ?>
                        <a href="EditCase.php?uid=<?php echo $case['caseID']; ?>">Edit</a>
                    <?php else : ?>
                        <a href="ViewCase.php?uid=<?php echo $case['caseID']; ?>">View</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
        </table>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();

        document.getElementById('searchForm').addEventListener('submit', function(event) {
            event.preventDefault();
        
        });
    </script>
</body>
</html>
<gay></gay>