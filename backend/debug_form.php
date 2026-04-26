<?php
session_start();
$_SESSION['role'] = 'student';
$_SESSION['user_id'] = 1;
$_SESSION['name'] = 'Test Student';

include_once(__DIR__ . "/config/database.php");

echo "=== HTML Faculty Options Debug ===\n\n";

$facultyResult = $conn->query("
    SELECT f.faculty_id, f.name, f.college 
    FROM faculty f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.role = 'professor' AND (u.role = 'professor' OR u.id IS NULL)
    ORDER BY f.name ASC
");

echo "Query returned: " . $facultyResult->num_rows . " rows\n\n";

if ($facultyResult && $facultyResult->num_rows > 0) {
    echo "HTML Options:\n";
    while ($row = $facultyResult->fetch_assoc()) {
        $facultyId    = htmlspecialchars($row['faculty_id']);
        $name         = htmlspecialchars($row['name']);
        $collegeValue = htmlspecialchars($row['college']);
        echo "<option value=\"$facultyId\" data-college=\"$collegeValue\">$name</option>\n";
    }
} else {
    echo "No professors available\n";
}

echo "\n=== College options ===\n";
$colleges = [
    "Business Administration",
    "Entrepreneurship", 
    "Accountancy",
    "Education",
    "Computer Studies"
];
foreach ($colleges as $col) {
    echo "- $col\n";
}

$conn->close();
