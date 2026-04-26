<?php
session_start();
include_once(__DIR__ . "/../config/database.php");

$allowedRoles = ['student', 'program_head', 'dean'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
    header("Location: ../../frontend/public/index.php");
    exit;
}

// Ensure DB has required columns
$requiredColumns = [
    'college'  => "VARCHAR(100) NULL",
    'comments' => "TEXT NULL"
];
foreach ($requiredColumns as $col => $definition) {
    $colCheck = $conn->query("SHOW COLUMNS FROM evaluations LIKE '$col'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE evaluations ADD COLUMN $col $definition");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facultyCode = trim($_POST['faculty_code'] ?? '');
    $college     = trim($_POST['college']       ?? '');
    $score1      = intval($_POST['score1']       ?? 0);
    $score2      = intval($_POST['score2']       ?? 0);
    $score3      = intval($_POST['score3']       ?? 0);
    $comments    = trim($_POST['comments']       ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{5}$/', $facultyCode)) {
        header("Location: ../../frontend/public/index.php");
        exit;
    }

    $facultyId = $facultyCode;

    if (empty($facultyId) || $score1 < 1 || $score1 > 5 ||
        $score2 < 1 || $score2 > 5 || $score3 < 1 || $score3 > 5 || $college === '') {
        header("Location: ../../frontend/public/index.php");
        exit;
    }

    $avgScore = round(($score1 + $score2 + $score3) / 3, 2);

    $checkStmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
    if ($checkStmt) {
        $checkStmt->bind_param('s', $facultyId);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows === 0) {
            header("Location: ../../frontend/public/index.php?error=invalid_faculty");
            exit;
        }
        $checkStmt->close();
    }

    // Prevent professors from evaluating themselves
    $selfCheckStmt = $conn->prepare("SELECT id FROM faculty WHERE faculty_id = ? AND user_id = ?");
    if ($selfCheckStmt) {
        $currentUserId = $_SESSION['user_id'] ?? null;
        $selfCheckStmt->bind_param('si', $facultyId, $currentUserId);
        $selfCheckStmt->execute();
        $selfCheckStmt->store_result();
        if ($selfCheckStmt->num_rows > 0) {
            header("Location: ../../frontend/public/index.php?error=self_evaluation");
            exit;
        }
        $selfCheckStmt->close();
    }

    $stmt = $conn->prepare(
        "INSERT INTO evaluations (faculty_id, rater_role, score1, score2, score3, avg_score, college, comments)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $raterRole = $_SESSION['role'];
        $stmt->bind_param("ssiiidss",
            $facultyId, $raterRole,
            $score1, $score2, $score3,
            $avgScore, $college, $comments
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../../frontend/public/index.php");
    exit;
}

header("Location: ../../frontend/public/index.php");
exit;
