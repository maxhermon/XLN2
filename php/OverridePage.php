<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Override Case Decision</title>
    <link rel="stylesheet" href="../css/OverridePage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
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
            <h1>Current Case</h1>
            <div class="case-details">
                <!-- Display current case details here -->
                <p><strong>Case ID:</strong> <!-- Case ID --></p>
                <p><strong>Description:</strong> <!-- Case Description --></p>
                <p><strong>Status:</strong> <!-- Case Status --></p>
                <p><strong>Handler:</strong> <!-- Case Handler --></p>
                <p><strong>Customer Name:</strong> <!-- Customer Name --></p>
            </div>
            <h2>Similar Cases</h2>
            <table>
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Creation Timestamp</th>
                        <th>Department</th>
                        <th>Case Reason</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Handler</th>
                        <th>Customer Name</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loop through similar cases and display them here -->
                    <tr>
                        <td><!-- Similar Case ID --></td>
                        <td><!-- Similar Case Creation Timestamp --></td>
                        <td><!-- Similar Case Department --></td>
                        <td><!-- Similar Case Reason --></td>
                        <td><!-- Similar Case Description --></td>
                        <td><!-- Similar Case Status --></td>
                        <td><!-- Similar Case Handler --></td>
                        <td><!-- Similar Case Customer Name --></td>
                    </tr>
                </tbody>
            </table>
            <button id="overrideButton">Override Decision</button>
        </div>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
        <script>
            document.getElementById("year").innerHTML = new Date().getFullYear();
        </script>
    </footer>
</body>
</html>
