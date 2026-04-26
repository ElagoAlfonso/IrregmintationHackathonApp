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
        'name' => 'Test Program Head',
        'email' => 'program_head@test.com',
        'password' => 'ph123',
        'role' => 'program_head'
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

// Add sample faculty members linked to professor users
echo "\n--- Adding Faculty Members ---\n";
$sampleFaculty = [
    [
        'faculty_id' => '2025-01-00001',
        'name' => 'Dr. James Smith',
        'college' => 'Computer Studies',
        'role' => 'professor',
        'email' => 'professor@test.com'
    ],
    [
        'faculty_id' => '2025-01-00002',
        'name' => 'Dr. Maria Garcia',
        'college' => 'Computer Studies',
        'role' => 'professor',
        'email' => 'professor@test.com'
    ],
    [
        'faculty_id' => '2025-01-00003',
        'name' => 'Dr. John Davis',
        'college' => 'Engineering',
        'role' => 'professor',
        'email' => 'professor@test.com'
    ]
];

foreach ($sampleFaculty as $faculty) {
    // Check if faculty already exists
    $checkStmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
    $checkStmt->bind_param('s', $faculty['faculty_id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Faculty {$faculty['faculty_id']} already exists. Skipping...\n";
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Get user_id for the professor
    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'professor'");
    $userStmt->bind_param('s', $faculty['email']);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $user_id = $user['id'];
        
        // Insert faculty record
        $insertStmt = $conn->prepare("
            INSERT INTO faculty (faculty_id, name, college, role, user_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($insertStmt) {
            $insertStmt->bind_param('ssssi', 
                $faculty['faculty_id'], 
                $faculty['name'], 
                $faculty['college'], 
                $faculty['role'], 
                $user_id
            );
            
            if ($insertStmt->execute()) {
                echo "Created faculty: {$faculty['name']} ({$faculty['faculty_id']})\n";
            } else {
                echo "Failed to create faculty {$faculty['faculty_id']}: " . $insertStmt->error . "\n";
            }
            $insertStmt->close();
        }
    }
    $userStmt->close();
}

$conn->close();
echo "\nSeed complete!\n";
