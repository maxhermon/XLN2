<?php

static $db = null;
    if ($db === null) {
        $db = new SQLite3('data/XLN_new_DB.db');
        if (!$db) {
            die("Database connection error: " . $db->lastErrorMsg());
        }
        else{
            echo("Database connection successful");
        }
    }
    return $db;
?>
