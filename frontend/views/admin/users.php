<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}

include_once("../../../backend/config/database.php");

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name     = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email    = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = $conn->real_escape_string($_POST['role'] ?? 'student');

    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check && $check->num_rows > 0) {
            $error = 'That email is already registered.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);
                if ($stmt->execute()) {
                    $success = 'User account created successfully.';
                } else {
                    $error = 'Failed to add user. Please try again.';
                }
                $stmt->close();
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    }
}

$userResult = $conn->query("SELECT id, name, email, role FROM users ORDER BY name ASC");

ob_start();
?>

<div class="page-title">User Management</div>
<div class="page-subtitle">Admin-only account creation for students and evaluators</div>

<div class="admin-card">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="admin-form">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Set a password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="student" selected>Student</option>
                    <option value="professor">Professor</option>
                    <option value="program_head">Program Head</option>
                    <option value="dean">Dean</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" name="add_user">Create User</button>
    </form>
</div>

<div class="admin-filter-row">
    <div class="form-group" style="max-width: 280px;">
        <label for="roleFilter">Filter by role</label>
        <select id="roleFilter" class="input-field">
            <option value="">All roles</option>
            <option value="student">Student</option>
            <option value="professor">Professor</option>
            <option value="program_head">Program Head</option>
            <option value="dean">Dean</option>
            <option value="admin">Admin</option>
        </select>
    </div>
</div>

<div class="section-hdr">
    <h2>Existing Accounts</h2>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($userResult && $userResult->num_rows > 0): ?>
                <?php while ($user = $userResult->fetch_assoc()): ?>
                    <tr data-role="<?php echo htmlspecialchars($user['role']); ?>">
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('roleFilter');
    if (!filter) return;

    filter.addEventListener('change', function() {
        const selectedRole = filter.value;
        document.querySelectorAll('.admin-table tbody tr[data-role]').forEach(row => {
            row.style.display = !selectedRole || row.dataset.role === selectedRole ? '' : 'none';
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include_once("../layout.php");
?>