<?php

session_start();

if(!isset($_SESSION['userID'])){
    header("Location: LoginPage.php");
    exit();
}

if ($_SESSION['jobID'] == 2){ //admin
   echo "admin";
}else{
    echo "jobID: ".$_SESSION['jobID'];
}

?>