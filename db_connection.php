<?php

function connectToDatabase() {
static $db = null;
    if ($db === null) {
        $db = new SQLite3('XLN_new_DB.db');
        if (!$db) {
            die("Database connection error: " . $db->lastErrorMsg());
        }
    }
    return $db;
}
?>
