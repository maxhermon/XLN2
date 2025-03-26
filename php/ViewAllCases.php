<!DOCTYPE html>
<html lang="en">

<?php
function getCases($searchBy = '', $searchTerm = '', $page = 1, $casesPerPage = 10, $sortBy = '', $sortOrder = 'ASC') {
    $db = new SQLite3('../data/XLN_new_DBA.db');

    $sql = "SELECT c.*, 
               d.deptName AS department_name, 
               r.reason AS reason_name,
               cu.name AS customer_name,
               u.fname || ' ' || u.lname AS user_name, 
               CASE WHEN c.status = 1 THEN 'Open' ELSE 'Closed' END AS status_text
            FROM cases c
            LEFT JOIN reasons r ON c.reasonID = r.reasonID
            LEFT JOIN department_reasons dr ON dr.reasonID = r.reasonID
            LEFT JOIN departments d ON dr.departmentID = d.departmentID
            LEFT JOIN customers cu ON c.customerID = cu.customerID
            LEFT JOIN users u ON c.userID = u.userID"; 

    $whereClause = '';
    if (!empty($searchBy) && !empty($searchTerm)) {
        if ($searchBy == 'user_name') {
            $whereClause = " WHERE (u.fname || ' ' || u.lname) LIKE :searchTerm";
        } elseif ($searchBy == 'department_name') {
            $whereClause = " WHERE d.deptName LIKE :searchTerm";
        } else {
            $whereClause = " WHERE $searchBy LIKE :searchTerm";
        }
    }

    $orderBy = '';
    if (!empty($sortBy)) {
        $orderBy = " ORDER BY $sortBy $sortOrder";
    }

    $countSql = "SELECT COUNT(*) as total FROM ($sql $whereClause)";
    $countStmt = $db->prepare($countSql);
    
    if (!empty($searchBy) && !empty($searchTerm)) {
        $countStmt->bindValue(':searchTerm', "%$searchTerm%", SQLITE3_TEXT);
    }
    
    $countResult = $countStmt->execute();
    $totalCases = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
    
    $totalPages = ceil($totalCases / $casesPerPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $casesPerPage;
    
    $sql = $sql . $whereClause . $orderBy . " LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    
    $stmt->bindValue(':limit', $casesPerPage, SQLITE3_INTEGER);
    $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    
    if (!empty($searchBy) && !empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', "%$searchTerm%", SQLITE3_TEXT);
    }

    $result = $stmt->execute();
    $arrayResult = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $arrayResult[] = $row;
    }

    return [
        'cases' => $arrayResult,
        'totalCases' => $totalCases,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
}

function getAllDropdownOptions() {
    $db = new SQLite3('../data/XLN_new_DBA.db');
    $options = [
        'status_text' => ['Open', 'Closed'],
        'department_name' => [],
        'reason_name' => [],
        'customer_name' => [],
        'user_name' => []
    ];

    $result = $db->query("SELECT DISTINCT deptName FROM departments ORDER BY deptName");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $options['department_name'][] = $row['deptName'];
    }

    $result = $db->query("SELECT DISTINCT reason FROM reasons ORDER BY reason");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $options['reason_name'][] = $row['reason'];
    }

    $result = $db->query("SELECT DISTINCT name FROM customers ORDER BY name");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $options['customer_name'][] = $row['name'];
    }

    $result = $db->query("SELECT DISTINCT (fname || ' ' || lname) as full_name FROM users ORDER BY full_name");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $options['user_name'][] = $row['full_name'];
    }

    return $options;
}

$searchBy = isset($_GET['searchBy']) ? $_GET['searchBy'] : 'department_name';
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : '';
$sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'ASC';
  
$allDropdownOptions = getAllDropdownOptions();

$result = getCases($searchBy, $searchTerm, $page, 10, $sortBy, $sortOrder);
$cases = $result['cases'];
$totalPages = $result['totalPages'];
$currentPage = $result['currentPage'];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Cases</title>
    <link rel="stylesheet" href="../css/ViewAllCases.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/e3b58c845d.js" crossorigin="anonymous"></script>
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
    
    <form method="GET" action="" id="searchForm">
        <label for="searchBy">Search By:</label>
        <select name="searchBy" id="searchBy">
            <option value="caseID" <?php echo ($searchBy == 'caseID') ? 'selected' : ''; ?>>Case ID</option>
            <option value="department_name" <?php echo ($searchBy == 'department_name') ? 'selected' : ''; ?>>Department</option>
            <option value="reason_name" <?php echo ($searchBy == 'reason_name') ? 'selected' : ''; ?>>Reason</option>
            <option value="status_text" <?php echo ($searchBy == 'status_text') ? 'selected' : ''; ?>>Status</option>
            <option value="customer_name" <?php echo ($searchBy == 'customer_name') ? 'selected' : ''; ?>>Customer Name</option>
            <option value="user_name" <?php echo ($searchBy == 'user_name') ? 'selected' : ''; ?>>Case Handler</option>
        </select>

        <div class="dropdown2">
            <input type="text" name="searchTerm" id="searchTerm" 
                   value="<?php echo htmlspecialchars($searchTerm); ?>" 
                   placeholder="Enter search term" 
                   onfocus="showDropdown()" 
                   onkeyup="filterFunction()">
            
            <div id="filterDropdown" class="dropdown-content2">
            </div>
        </div>
        <input type="hidden" name="page" value="1">
        <button type="submit">Search</button>
    </form>

    <table id="casesTable">
    <input type="text" name="searchTerm" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter search term">
    <input type="hidden" name="page" value="1">
    <button type="submit">Search</button>
</form>

<table id="casesTable">

        <thead>
            <tr>
                <th><a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=caseID&sortOrder=<?php echo ($sortBy == 'caseID' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>">Case ID <?php if ($sortBy == 'caseID') echo $sortOrder == 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>'; ?></a></th>
                <th><a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=department_name&sortOrder=<?php echo ($sortBy == 'department_name' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>">Department <?php if ($sortBy == 'department_name') echo $sortOrder == 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>'; ?></a></th>
                <th><a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=reason_name&sortOrder=<?php echo ($sortBy == 'reason_name' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>">Reason <?php if ($sortBy == 'reason_name') echo $sortOrder == 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>'; ?></a></th>
                <th><a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=customer_name&sortOrder=<?php echo ($sortBy == 'customer_name' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>">Customer Name <?php if ($sortBy == 'customer_name') echo $sortOrder == 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>'; ?></a></th>
                <th><a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=status_text&sortOrder=<?php echo ($sortBy == 'status_text' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>">Status <?php if ($sortBy == 'status_text') echo $sortOrder == 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>'; ?></a></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cases as $case) : ?>
            <tr>
                <td><?php echo $case['caseID']; ?></td>
                <td><?php echo $case['department_name']; ?></td>
                <td><?php echo $case['reason_name']; ?></td>
                <td><?php echo $case['customer_name']; ?></td>
                <td><?php echo $case['status_text']; ?></td>
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

    <?php if ($totalPages > 1) : ?>
    <div class="pagination">
        <?php if ($currentPage > 1) : ?>
            <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=1"><i class="fa-solid fa-angles-left"></i></a>
            <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $currentPage - 1; ?>"><i class="fa-solid fa-angle-left"></i></a>
        <?php else : ?>
            <span class="disabled"><i class="fa-solid fa-angles-left"></i></span>
            <span class="disabled"><i class="fa-solid fa-angle-left"></i></span>
        <?php endif; ?>
        
        <?php
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                echo "<span class=\"active\">$i</span>";
            } else {
                echo "<a href=\"?searchBy=" . urlencode($searchBy) . "&searchTerm=" . urlencode($searchTerm). "&sortBy=" . urlencode($sortBy) . "&sortOrder=" . urlencode($sortOrder) . "&page=$i\">$i</a>";
            }
        }
        ?>
        
        <?php if ($currentPage < $totalPages) : ?>
            <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $currentPage + 1; ?>"><i class="fa-solid fa-angle-right"></i></a>
            <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $totalPages; ?>"><i class="fa-solid fa-angles-right"></i></a>
        <?php else : ?>
            <span class="disabled"><i class="fa-solid fa-angle-right"></i></span>
            <span class="disabled"><i class="fa-solid fa-angles-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <span id="year"></span> XLN</p>
</footer>

<script>
    const allDropdownOptions = <?php echo json_encode($allDropdownOptions); ?>;
        <div class="pagination">
            <?php if ($currentPage > 1) : ?>
                <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=1"><i class="fa-solid fa-angles-left"></i></a>
                <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $currentPage - 1; ?>"><i class="fa-solid fa-angle-left"></i></a>
            <?php else : ?>
                <span class="disabled"><i class="fa-solid fa-angles-left"></i></span>
                <span class="disabled"><i class="fa-solid fa-angle-left"></i></span>
            <?php endif; ?>
            
            <?php
            
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    echo "<span class=\"active\">$i</span>";
                } else {
                    echo "<a href=\"?searchBy=" . urlencode($searchBy) . "&searchTerm=" . urlencode($searchTerm) . "&sortBy=" . urlencode($sortBy) . "&sortOrder=" . urlencode($sortOrder) . "&page=$i\">$i</a>";
                }
            }
            ?>
            
            <?php if ($currentPage < $totalPages) : ?>
                <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $currentPage + 1; ?>"><i class="fa-solid fa-angle-right"></i></a>
                <a href="?searchBy=<?php echo urlencode($searchBy); ?>&searchTerm=<?php echo urlencode($searchTerm); ?>&sortBy=<?php echo urlencode($sortBy); ?>&sortOrder=<?php echo urlencode($sortOrder); ?>&page=<?php echo $totalPages; ?>"><i class="fa-solid fa-angles-right"></i></a>
            <?php else : ?>
                <span class="disabled"><i class="fa-solid fa-angle-right"></i></span>
                <span class="disabled"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    function updateDropdownOptions() {
        const searchBy = document.getElementById('searchBy').value;
        const dropdown = document.getElementById('filterDropdown');
        const searchTermInput = document.getElementById('searchTerm');
        
        dropdown.innerHTML = '';
        searchTermInput.value = '';
        
        const options = allDropdownOptions[searchBy] || [];
        
        options.forEach(value => {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = value;
            a.onclick = function(e) { 
                e.preventDefault();
                selectValue(value); 
            };
            dropdown.appendChild(a);
        });
    }

    function showDropdown() {
        const dropdown = document.getElementById('filterDropdown');
        dropdown.classList.add('show');
    }

    function filterFunction() {
        const input = document.getElementById('searchTerm');
        const filter = input.value.toUpperCase();
        const div = document.getElementById('filterDropdown');
        const a = div.getElementsByTagName('a');
        
        for (let i = 0; i < a.length; i++) {
            const txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = '';
            } else {
                a[i].style.display = 'none';
            }
        }
    }

    function selectValue(value) {
        document.getElementById('searchTerm').value = value;
        document.getElementById('filterDropdown').classList.remove('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('#searchTerm')) {
            const dropdown = document.getElementById('filterDropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchBy').addEventListener('change', updateDropdownOptions);
        
        updateDropdownOptions();
        
        document.getElementById('year').textContent = new Date().getFullYear();
    });
    </script>
</body>
</html>