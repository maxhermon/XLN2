<!DOCTYPE html>
<html lang="en">

<?php
function getCases($searchBy = '', $searchTerm = '') {
    $db = new SQLite3('XLN_new_DB.db');

    $sql = "SELECT c.*, 
               d.deptName AS department_name, 
               r.reason AS reason_name, 
               CASE WHEN c.status = 1 THEN 'Open' ELSE 'Closed' END AS status_text
        FROM cases c
        LEFT JOIN departments d ON c.departmentID = d.departmentID  
        LEFT JOIN reasons r ON c.reasonID = r.reasonID";
    
    // Apply search filter if user has entered a search term
    if (!empty($searchBy) && !empty($searchTerm)) {
        $sql .= " WHERE $searchBy LIKE :searchTerm";
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
    <link rel="stylesheet" href="ViewAllCases.css">
</head>
<body>
    <main>
        <h2>View All Cases</h2>
        <form method="GET" action="">
        <label for="searchBy">Search By:</label>
        <select name="searchBy" id="searchBy">
            <option value="caseID">Case ID</option>
            <option value="department_name">Department</option>
            <option value="reason_name">Reason</option>
            <option value="status_text">Status</option>
            <option value="customerEmail">Customer Email</option>
        </select>
    <input type="text" name="searchTerm" placeholder="Enter search term">
    <button type="submit">Search</button>
</form>

        <table id="casesTable">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Creation Timestamp</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Customer Email</th>
                    <th>Notes</th>
                    <th>Closed Date</th>
                    <td colspan="2" align="center">Action</td>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($cases as $case) : ?>
            <tr>
                <td><?php echo $case['caseID']; ?></td>
                <td><?php echo $case['created']; ?></td>
                <td><?php echo $case['department_name']; ?></td>
                <td><?php echo $case['reason_name']; ?></td>
                <td><?php echo $case['status_text']; ?></td>
                <td><?php echo $case['customerEmail']; ?></td>
                <td><?php echo $case['description']; ?></td>
                <td><?php echo $case['closed']; ?></td>
                <td>
                    <a href="EditCases.php?uid=<?php echo $case['caseID']; ?>">Edit</a>
                    <a href="CloseCase.php?uid=<?php echo $case['caseID']; ?>">Close</a>
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
            // Add your search logic here
        });
    </script>
</body>
</html>