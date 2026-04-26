<?php
include 'config/database.php';

echo "=== Faculty Records ===\n";
$result = $conn->query("SELECT faculty_id, name, college, role, user_id FROM faculty");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['faculty_id']}, Name: {$row['name']}, College: '{$row['college']}', Role: {$row['role']}, User_ID: {$row['user_id']}\n";
    }
} else {
    echo "No faculty records found.\n";
}

echo "\n=== Users with role='professor' ===\n";
$result = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'professor'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}\n";
    }
} else {
    echo "No professors found in users table.\n";
}

$conn->close();
