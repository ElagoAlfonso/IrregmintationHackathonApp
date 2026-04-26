<?php
include 'config/database.php';
try {
    $conn->query('TRUNCATE TABLE evaluations');
    echo 'Truncated evaluations.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Truncate error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE evaluations DROP FOREIGN KEY evaluations_ibfk_1');
    echo 'Dropped old foreign key.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Drop FK error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE evaluations MODIFY COLUMN faculty_id VARCHAR(13)');
    echo 'Modified evaluations faculty_id.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Modify error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE faculty ADD COLUMN faculty_id VARCHAR(13) UNIQUE AFTER id');
    echo 'Added faculty_id column.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Column already exists or error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query("ALTER TABLE faculty ADD COLUMN role ENUM('professor') DEFAULT 'professor'");
    echo 'Added faculty role column.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Faculty role column already exists or error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE users MODIFY COLUMN role ENUM(\'student\', \'professor\', \'program_head\', \'dean\', \'admin\')');
    echo 'Updated user roles enum.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Update users role enum error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE evaluations ADD CONSTRAINT evaluations_ibfk_1 FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)');
    echo 'Added new foreign key.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Add FK error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE faculty ADD COLUMN user_id INT AFTER faculty_id');
    echo 'Added user_id column to faculty.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Add user_id column error: ' . $e->getMessage() . PHP_EOL;
}
try {
    $conn->query('ALTER TABLE faculty ADD CONSTRAINT fk_faculty_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
    echo 'Added foreign key from faculty to users.' . PHP_EOL;
} catch (Exception $e) {
    echo 'Add FK to users error: ' . $e->getMessage() . PHP_EOL;
}
echo 'Database update attempted.';
