<?php
$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

$sqlFile = __DIR__ . '/database/schema.sql';
if (!file_exists($sqlFile)) {
    die("Schema file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die("Unable to read schema file: $sqlFile\n");
}

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
        if ($conn->errno) {
            echo "SQL error: " . $conn->error . "\n";
            break;
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Database schema imported successfully.\n";
} else {
    echo "Import failed: " . $conn->error . "\n";
}

$conn->close();
