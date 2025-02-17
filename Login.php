<?php
$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Use the PDO connection from db_connection.php
    $conn = require __DIR__ . "/db_connection.php";

    // Check if email is empty to avoid unnecessary queries
    if (empty($_POST["email"])) {
        header("Location: login.php");
        exit;
    }

    // Prepare a statement to select the user by email
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email_address = :email");
    // Bind the email parameter securely
    $stmt->bindValue(':email', $_POST["email"], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Fetch the user as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If a user was found, verify the password
    if ($user) {
        // Check password using password_verify (assuming user_password is a hashed password)
        if (password_verify($_POST["password"], $user["user_password"])) {
            // If password matches, start the session
            session_start();

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id();

            // Store the user data in the session
            $_SESSION["user_id"]   = $user["user_id"];
            $_SESSION["email"]     = $user["email_address"]; // Adjust the column name if needed
            $_SESSION["access"]    = $user["access_level"];  // Adjust if you have a different column

            // Redirect on successful login
            header("Location: index.php");
            exit;
        }
    }

    // If we reach this point, login is invalid
    $is_invalid = true;
}
?>