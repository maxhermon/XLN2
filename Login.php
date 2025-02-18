<?php


    $db = new SQLITE3('data\XLN_new_DBA.db');  
    $sql = "SELECT fName || ' ' || COALESCE(mName,'') || ' ' || lName AS name, password, jobID, userID FROM users WHERE email = :email";   
    $stmt = $db->prepare($sql); 
    $stmt->bindParam(':email', $_POST['email'], SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $arrayResult = [];

    //get results
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){  
        $arrayResult[] = $row;
    }

    //check results
    if (!empty($arrayResult)) {
        if (password_verify($_POST["password"], $arrayResult[0]['password'])) {
            session_start();
            $_SESSION['name'] = $arrayResult[0]['name'];
            $_SESSION['jobID'] = $arrayResult[0]['jobID'];
            $_SESSION['userID'] = $arrayResult[0]['userID'];
            echo "true"; // Redirect to home page
            header("Location: php/CaseCreation.php"); //redirect to case creation page
        } else {
            echo "no match"; // Incorrect password
            header("Location: LoginPage.php?Login_Error=1");
        }
    } else {
        echo "no user found"; // No user found
        header("Location: LoginPage.php?Login_Error=1");
    }

    ?>

