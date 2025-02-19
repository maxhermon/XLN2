<?php

session_start();
$newCaseID = isset($_SESSION['caseID']) ? $_SESSION['caseID'] : null;
unset($_SESSION['caseID']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Created</title>
    <link rel="stylesheet" href="../css/CaseCreated.css">
</head>
<body>
<header>
        <a href="../html/Homepage.html"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <nav>
            <ul class="left-menu">
                <li><a href="../html/Homepage.html">XLN Home</a></li>
                <li><a href="../html/Contact.html">Contact</a></li>
            </ul>
            <ul class="right-menu">
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">MyAccount</a>
                    <div class="dropdown-content">
                        <a href="#">View Profile</a>
                        <a href="#">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <div class="message">No similar cases exist. Case successfully created.</div>
        <div class="buttons">
            <button onclick="window.location.href='EditCase.php?uid=<?php echo $newCaseID; ?>'">Edit Case</button>
            <button onclick="window.location.href='CaseCreation.php'">Create Another Case</button>
            <button onclick="window.location.href='ViewAllCases.php'">View All Cases</button>
        </div>
    </div>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
        <script>
            document.getElementById("year").innerHTML = new Date().getFullYear();
        </script>
    </footer>
</body>
</html>