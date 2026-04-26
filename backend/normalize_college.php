<?php
include 'config/database.php';

$mappings = [
    'Department of Business Administration' => 'Business Administration',
    'Department of Entrepreneurship' => 'Entrepreneurship',
    'Department of Accountancy' => 'Accountancy',
    'Department of Education' => 'Education',
    'Department of Computer Studies' => 'Computer Studies'
];

foreach ($mappings as $oldValue => $newValue) {
    $stmt = $conn->prepare("UPDATE faculty SET college = ? WHERE college = ?");
    if ($stmt) {
        $stmt->bind_param('ss', $newValue, $oldValue);
        if ($stmt->execute()) {
            echo "Updated faculty: '{$oldValue}' → '{$newValue}' ({$stmt->affected_rows} rows)\n";
        } else {
            echo "Error updating faculty: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
}

foreach ($mappings as $oldValue => $newValue) {
    $stmt = $conn->prepare("UPDATE evaluations SET college = ? WHERE college = ?");
    if ($stmt) {
        $stmt->bind_param('ss', $newValue, $oldValue);
        if ($stmt->execute()) {
            echo "Updated evaluations: '{$oldValue}' → '{$newValue}' ({$stmt->affected_rows} rows)\n";
        } else {
            echo "Error updating evaluations: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
}

$conn->close();
echo "Faculty college normalization complete.\n";
