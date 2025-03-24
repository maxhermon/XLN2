<?php
require 'db_connection.php';  
$db = connectToDatabase();   


if (isset($_POST['addJobRole'])) {




    $job = $_POST['job'] ?? null;
    

    $sql = "INSERT into jobs (job) VALUES (:job)";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':job', $job, SQLITE3_TEXT);
    

    $stmt->execute();



    header('Location: JobRoleCreated.php');
    exit;
}


?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case</title>
    <link rel="stylesheet" href="../css/UserCreation.css">
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
        <form action="JobRoleCreation.php" method="POST">
            <h2>Job Role Creation Page</h2>


            <label for="job"><b>Job Role</b></label>
            <input type="text" id="job" name="job" required>
            
            <button type="submit" name="addJobRole">Add Job Role</button>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
</body>
<script>
    document.getElementById("year").innerHTML = new Date().getFullYear();
</script>

</html>