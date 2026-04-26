<?php
include 'config/database.php';

echo "=== Faculty Records ===\n";
$facultyResult = $conn->query("
    SELECT f.faculty_id, f.name, f.college, f.role, f.user_id, u.id, u.email, u.role as user_role
    FROM faculty f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.role = 'professor'
    ORDER BY f.name ASC
");

if ($facultyResult) {
    while ($row = $facultyResult->fetch_assoc()) {
        echo "\nFaculty ID: " . htmlspecialchars($row['faculty_id']) . "\n";
        echo "Name: " . htmlspecialchars($row['name']) . "\n";
        echo "College: " . htmlspecialchars($row['college']) . "\n";
        echo "Faculty Role: " . htmlspecialchars($row['role']) . "\n";
        echo "user_id: " . ($row['user_id'] ?? 'NULL') . "\n";
        echo "User ID: " . ($row['id'] ?? 'NULL') . "\n";
        echo "User Email: " . ($row['email'] ?? 'NULL') . "\n";
        echo "User Role: " . ($row['user_role'] ?? 'NULL') . "\n";
        echo "---\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\n=== Professor Users ===\n";
$userResult = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'professor'");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Email: " . $row['email'] . " | Role: " . $row['role'] . "\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

$conn->close();
?>
