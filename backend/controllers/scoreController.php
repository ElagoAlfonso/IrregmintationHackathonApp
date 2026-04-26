<?php
include_once(__DIR__ . "/../config/database.php");

function get_avg_score($conn, $faculty_id, $role) {
    $stmt = $conn->prepare("SELECT AVG(avg_score) as avg_score FROM evaluations WHERE faculty_id = ? AND rater_role = ?");
    $stmt->bind_param("ss", $faculty_id, $role);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['avg_score'] ?? 0;
}

function calculate_final_score($conn, $faculty_id) {
    $student_avg = get_avg_score($conn, $faculty_id, 'student');
    $ph_avg = get_avg_score($conn, $faculty_id, 'program_head');
    $dean_avg = get_avg_score($conn, $faculty_id, 'dean');

    $final = ($student_avg * 0.5) + ($ph_avg * 0.3) + ($dean_avg * 0.2);
    return round($final, 2);
}
?>