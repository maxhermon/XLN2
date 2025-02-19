<!DOCTYPE html>
<html lang="en">

<?php
function getUsers($searchBy = '', $searchTerm = '') {
    $db = new SQLite3('../data/XLN_new_DBA.db');

    $sql = "SELECT u.*,
                j.job AS job_name
        FROM users u
            LEFT JOIN jobs j ON u.jobID = j.jobID";

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

$users = getUsers($searchBy, $searchTerm);
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>
    <link rel="stylesheet" href="../css/ViewAllCases.css">
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
        <h2>View All Cases</h2>
        <form method="GET" action="">
        <label for="searchBy">Search By:</label>
        <select name="searchBy" id="searchBy">
            <option value="userID">User ID</option>
            <option value="fName">First Name</option>
            <option value="lName">Last Name</option>
            <option value="email">Email</option>
            <option value="job_name">Job</option>
        </select>
    <input type="text" name="searchTerm" placeholder="Enter search term">
    <button type="submit">Search</button>
</form>

        <table id="casesTable">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Job</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?php echo $user['userID']; ?></td>
                <td><?php echo $user['fName']; ?></td>
                <td><?php echo $user['mName']; ?></td>
                <td><?php echo $user['lName']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['password']; ?></td>
                <td><?php echo $user['job_name']; ?></td>
                <td>
                    <?php if ($user['jobID'] == 1) : ?>
                        <a href="EditUser.php?uid=<?php echo $user['userID']; ?>">Edit</a>
                    <?php else : ?>
                        <a href="ViewUser.php?uid=<?php echo $user['userID']; ?>">View</a>
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