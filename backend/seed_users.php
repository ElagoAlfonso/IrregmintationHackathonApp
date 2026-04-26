<?php
include 'config/database.php';

// Test user credentials
$testUsers = [
    [
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'password' => 'student123',
        'role' => 'student'
    ],
    [
        'name' => 'Test Professor',
        'email' => 'professor@test.com',
        'password' => 'prof123',
        'role' => 'professor'
    ]
];

foreach ($testUsers as $user) {
    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param('s', $user['email']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "User {$user['email']} already exists. Skipping...\n";
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Hash password and insert user
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ssss', $user['name'], $user['email'], $hashedPassword, $user['role']);
        if ($stmt->execute()) {
            echo "Created user: {$user['email']} with password: {$user['password']}\n";
        } else {
            echo "Failed to create user {$user['email']}: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "Prepare failed: " . $conn->error . "\n";
    }
}

$conn->close();
echo "\nSeed users complete!\n";
