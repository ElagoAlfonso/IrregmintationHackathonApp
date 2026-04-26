<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

switch ($_SESSION['role']) {
    case 'student':
        header("Location: ../views/student/student_form.php");
        break;
    case 'program_head':
        header("Location: ../views/program_head/program_head_form.php");
        break;
    case 'dean':
        header("Location: ../views/dean/dean_form.php");
        break;
    case 'admin':
        header("Location: ../views/admin/dashboard.php");
        break;
    default:
        session_destroy();
        header("Location: ../views/auth/login.php");
        break;
}
exit;
