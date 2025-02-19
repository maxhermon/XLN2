<?php //code to log the user out and end the session

//connect to the session
session_start();

//unset the session variables
session_unset();

//destroy the session
session_destroy();

//return the user to the homepage
header("location: LoginPage.php")

?>