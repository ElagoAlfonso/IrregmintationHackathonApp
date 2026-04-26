<?php
include 'config/database.php';

echo "=== Linking Faculty to Professor Users ===\n";

// Get all faculty with role = professor that have no user_id
$facultyResult = $conn->query("SELECT faculty_id, name FROM faculty WHERE role = 'professor' AND user_id IS NULL");

if ($facultyResult && $facultyResult->num_rows > 0) {
    while ($faculty = $facultyResult->fetch_assoc()) {
        // Try to match by name first
        $nameForSearch = trim($faculty['name']);
        
        $userStmt = $conn->prepare("
            SELECT id FROM users WHERE role = 'professor' AND name = ?
        ");
        $userStmt->bind_param('s', $nameForSearch);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];
            
            // Update faculty with user_id
            $updateStmt = $conn->prepare("UPDATE faculty SET user_id = ? WHERE faculty_id = ?");
            $updateStmt->bind_param('is', $user_id, $faculty['faculty_id']);
            
            if ($updateStmt->execute()) {
                echo "✓ Linked {$faculty['name']} ({$faculty['faculty_id']}) to user ID {$user_id}\n";
            } else {
                echo "✗ Failed to link {$faculty['name']}: " . $updateStmt->error . "\n";
            }
            $updateStmt->close();
        } else {
            echo "⚠ No matching user found for {$faculty['name']}\n";
        }
        $userStmt->close();
    }
} else {
    echo "No faculty to link\n";
}

echo "\n=== Verification ===\n";
$verifyResult = $conn->query("
    SELECT f.faculty_id, f.name, u.name as user_name, u.email
    FROM faculty f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.role = 'professor'
");

if ($verifyResult) {
    while ($row = $verifyResult->fetch_assoc()) {
        $linked = ($row['user_name'] ? "✓ LINKED to " . $row['user_name'] : "✗ NOT LINKED");
        echo $row['faculty_id'] . " | " . $row['name'] . " | " . $linked . "\n";
    }
}

$conn->close();
echo "\nDone!\n";
?>
