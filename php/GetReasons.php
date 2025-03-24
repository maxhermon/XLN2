<?php

require 'db_connection.php';
$db = connectToDatabase();

$deptID = isset($_GET['departmentID']) ? (int)$_GET['departmentID'] : 0;

try {
    $sql = "
        SELECT r.reasonID, r.reason
        FROM reasons r
        INNER JOIN department_reasons dr ON r.reasonID = dr.reasonID
        WHERE dr.departmentID = :deptID
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':deptID', $deptID, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $reasons = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $reasons[] = $row;  
    }

    header('Content-Type: application/json');
    echo json_encode($reasons);
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
