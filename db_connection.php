<?php

try {
    
    $databasePath = __DIR__ . '/XLN_db.db';

    $conn = new PDO("sqlite:" . $databasePath);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn;

    echo "Database connection is successful!";
} catch (PDOException $e) {
    exit("Database connection failed: " . $e->getMessage());
}

?>