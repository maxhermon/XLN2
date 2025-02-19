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