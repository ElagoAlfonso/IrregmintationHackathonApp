<?php
session_start();

if (isset($_SESSION['role'])) {
    header("Location: ../../public/index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once("../../../backend/config/database.php");
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['name']    = $user['name'];
                header("Location: ../../public/index.php");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Login query failed: ' . $e->getMessage());
        $error = "Database error. Please contact the administrator.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Faculty Evaluation System</title>
    <link rel="stylesheet" href="../../public/css/auth.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="auth-title">Faculty Evaluation System</div>
        <div class="auth-subtitle">Sign in to your account</div>
        <?php if ($error): ?><div class="auth-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="input-field" type="email" name="email" placeholder="you@example.com" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input class="input-field" type="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <button class="auth-btn" type="submit">Sign In</button>
        </form>
        <div class="auth-footer">Need an account? Please ask your administrator to create one.</div>
    </div>
</div>
</body>
</html>
