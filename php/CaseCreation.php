
<?php

session_start();
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";


require 'db_connection.php';  
$db = connectToDatabase();    

$departments = [];
$deptResult = $db->query("SELECT departmentID, deptName FROM departments");
while ($row = $deptResult->fetchArray(SQLITE3_ASSOC)) {
    $departments[] = $row;
}

$customers = [];
$cResult = $db->query("SELECT customerID, name FROM customers");
while ($row = $cResult->fetchArray(SQLITE3_ASSOC)) {
    $customers[] = $row;
}

$selectedDepartmentID = null;
$reasonsForDepartment = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['selectDepartment'])) {
        $selectedDepartmentID = $_POST['departmentID'] ?? null;

        if ($selectedDepartmentID) {
            $stmt = $db->prepare("SELECT reasonID, reason 
                                  FROM reasons 
                                  WHERE departmentID = :deptID");
            $stmt->bindValue(':deptID', $selectedDepartmentID, SQLITE3_INTEGER);
            $rResult = $stmt->execute();
            while ($rRow = $rResult->fetchArray(SQLITE3_ASSOC)) {
                $reasonsForDepartment[] = $rRow;
            }

        }
    }

    if (isset($_POST['submitCase'])) {
        
        if (!isset($_SESSION['userID'])) {
            header("Location: LoginPage.php");
            exit;
        }

        $userID = $_SESSION['userID'];

        $deptID = $_POST['departmentID'] ?? null;
        $reasonID = $_POST['reasonID']     ?? null;
        $status = $_POST['status']       ?? null;
        $customerID = $_POST['customerID'] ?? null;
        $description    = $_POST['description']        ?? '';

        $password = $_POST['password'] ?? null;

        echo "<br><br><br><br>";
        echo "reasonID: $reasonID";
        echo "<br>";
        echo "customerID: $customerID";
        echo "<br><br><br><br>";

        $sql = "SELECT cases.caseID
        FROM cases
        INNER JOIN reasons ON cases.reasonID = reasons.reasonID
        INNER JOIN customers ON cases.customerID = customers.customerID
        WHERE status = 1
        AND reasons.reasonID = :reasonID
        AND customers.customerID = :customerID;";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':reasonID', $reasonID, SQLITE3_TEXT);
        $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);

        $result = $stmt->execute();

        $duplicateCases = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $duplicateCases[] = $row['caseID']; // Extract only caseID values
        }

        if(empty($duplicateCases)){
            $createdTime = date('Y-m-d H:i:s');

            $sql = "INSERT into cases (userID, reasonID, description, status, created, closed, customerID)
            VALUES (:userID, :reasonID, :description, :status, :created, :closed, :customerID)";

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
            $stmt->bindValue(':reasonID', $reasonID, SQLITE3_INTEGER);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            $stmt->bindValue(':status', 1, SQLITE3_INTEGER);
            $stmt->bindValue(':created', $createdTime, SQLITE3_TEXT);
            $stmt->bindValue(':closed', null, SQLITE3_NULL);
            $stmt->bindValue(':customerID', $customerID, SQLITE3_INTEGER);

            $stmt->execute();

            $newCaseID = $db->lastInsertRowID();
            $_SESSION['caseID'] = $newCaseID;
            header('Location: caseCreated.php');
            exit();

        } else {
            $_SESSION['duplicateIDs'] = $duplicateCases;
            header('Location: SimilarCaseExists.php');
            exit();
        }         
        
    }
}
?>