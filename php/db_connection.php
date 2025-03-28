<?php

function connectToDatabase() {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3('../data/XLN_new_DBA.db');
        if (!$db) {
            die("Database connection error: " . $db->lastErrorMsg());
        }
    }
    return $db;
}

function logActivity($userID, $caseID, $activity, $status) {
    $db = connectToDatabase();
    $stmt = $db->prepare("INSERT INTO activities (userID, caseID, activity, date, status) VALUES (:userID, :caseID, :activity, :date, :status)");
    $stmt->bindValue(':userID', $userID, SQLITE3_INTEGER);
    $stmt->bindValue(':caseID', $caseID, SQLITE3_INTEGER);
    $stmt->bindValue(':activity', $activity, SQLITE3_TEXT);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->execute();
}
?>
