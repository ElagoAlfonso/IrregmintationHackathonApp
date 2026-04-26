<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/public/index.php");
    exit;
}

include_once("../config/database.php");

$query = "
    SELECT audit_logs.*, users.name AS username
    FROM audit_logs
    LEFT JOIN users ON audit_logs.user_id = users.id
    ORDER BY audit_logs.timestamp DESC
";

$result = $conn->query($query);

echo "<h1>Audit Logs</h1>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Timestamp</th><th>User</th><th>Action</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
    echo "<td>" . htmlspecialchars($row['username'] ?? 'Unknown') . "</td>";
    echo "<td>" . htmlspecialchars($row['action']) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<a href='../../frontend/public/index.php'>Back to Dashboard</a>";
