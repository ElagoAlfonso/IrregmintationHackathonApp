<?php
include 'config/database.php';

echo "=== Testing faculty query ===\n";
$query = "
    SELECT f.faculty_id, f.name, f.college 
    FROM faculty f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.role = 'professor' AND (u.role = 'professor' OR u.id IS NULL)
    ORDER BY f.name ASC
";
echo "Query: " . str_replace("\n", " ", $query) . "\n\n";

$result = $conn->query($query);
if ($result) {
    echo "Rows returned: " . $result->num_rows . "\n";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['name']} ({$row['faculty_id']}) - College: {$row['college']}\n";
        }
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

$conn->close();
