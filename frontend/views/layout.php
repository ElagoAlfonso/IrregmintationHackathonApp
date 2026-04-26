<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
// Only compute $basePath if the calling page hasn't already set it.
// Each view computes the correct path relative to its own location;
// layout.php is one level shallower so its own calculation would be wrong.
if (!isset($basePath)) {
    $basePath = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
    if ($basePath === '') { $basePath = '/'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Evaluation System</title>
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/main.css">
    <?php
    // Load page-specific stylesheets based on current file
    $page = basename($_SERVER['PHP_SELF'], '.php');
    $adminPages   = ['dashboard', 'faculty_list', 'users', 'reports'];
    $formPages    = ['student_form', 'dean_form', 'program_head_form'];
    if (in_array($page, $adminPages)):
    ?>
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/admin.css">
    <?php elseif (in_array($page, $formPages)): ?>
    <link rel="stylesheet" href="<?= $basePath ?>/public/css/forms.css">
    <?php endif; ?>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
                          stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <div class="logo-title">EvalPro</div>
                <div class="logo-sub">Faculty System</div>
            </div>
        </div>

        <div class="nav-section-label">Menu</div>

        <nav class="sidebar-nav">
            <?php if (isset($_SESSION['role'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="../admin/dashboard.php"><span class="ni">🖥️</span>Dashboard</a>
                    <a href="../admin/faculty_list.php"><span class="ni">👥</span>Faculty List</a>
                    <a href="../admin/users.php"><span class="ni">👤</span>User Management</a>
                    <a href="../admin/reports.php"><span class="ni">📈</span>Reports</a>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <a href="../student/student_form.php"><span class="ni">📝</span>Evaluation Form</a>
                <?php elseif ($_SESSION['role'] === 'program_head'): ?>
                    <a href="../program_head/program_head_form.php"><span class="ni">👨‍💼</span>Program Head Eval</a>
                <?php elseif ($_SESSION['role'] === 'dean'): ?>
                    <a href="../dean/dean_form.php"><span class="ni">🎓</span>Dean Evaluation</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="sidebar-bottom">
            <a href="../auth/logout.php" class="logout-btn">
                <span class="ni">🚪</span>Sign Out
            </a>
        </div>
    </aside>

    <div class="main-wrap">
        <div class="topbar">
            <div class="topbar-path">
                Faculty Evaluation &rsaquo;
                <strong><?php
                    $labels = [
                        'dashboard'          => 'Dashboard',
                        'faculty_list'       => 'Faculty List',
                        'reports'            => 'Reports',
                        'student_form'       => 'Evaluation Form',
                        'program_head_form'  => 'Evaluation Form',
                        'dean_form'          => 'Evaluation Form',
                        'users'              => 'User Management',
                    ];
                    echo $labels[$page] ?? ucfirst(str_replace('_', ' ', $page));
                ?></strong>
            </div>
            <?php if (isset($_SESSION['role'])): ?>
                <span class="role-pill"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></span>
            <?php endif; ?>
        </div>

        <main class="main-content">
            <?php echo $content; ?>
        </main>
    </div>
</body>
</html>
