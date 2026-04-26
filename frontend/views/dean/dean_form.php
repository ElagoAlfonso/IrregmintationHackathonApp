<?php
session_start();
if ($_SESSION['role'] !== 'dean') {
    header("Location: ../../public/index.php");
    exit;
}

$basePath = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
if ($basePath === '') { $basePath = '/'; }

// DB connection
ob_start();
$dbPath = realpath(__DIR__ . '/../../../backend/config/database.php');
if ($dbPath) {
    include_once($dbPath);
} else {
    error_log('Database config not found');
}
ob_end_clean();

ob_start();
?>

<div class="page-title">Dean Evaluation Form</div>
<div class="page-subtitle">Evaluate faculty on attendance, commitment, and work quality</div>

<div class="progress-banner">
    <div class="progress-banner-top">
        <span class="progress-banner-label">Form Completion</span>
        <span class="progress-banner-pct" id="progressPercent">0%</span>
    </div>
    <div class="progress-track">
        <div class="progress-fill" id="progressFill"></div>
    </div>
</div>

<form id="evaluationForm" data-autosave-id="dean-eval"
      method="POST" action="../../../backend/controllers/evaluationController.php">

    <div class="form-section">
        <h3>🎓 Faculty &amp; College</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="college">College</label>
                <select id="college" name="college" required>
                    <option value="" disabled selected>Select college…</option>
                    <option value="Business Administration">Business Administration</option>
                    <option value="Entrepreneurship">Entrepreneurship</option>
                    <option value="Accountancy">Accountancy</option>
                    <option value="Education">Education</option>
                    <option value="Computer Studies">Computer Studies</option>
                </select>
            </div>
            <div class="form-group">
                <label for="member">Faculty Member</label>
                <select id="member" name="member" required disabled>
                    <option value="" disabled selected>Select college first…</option>
                    <?php
                    $currentUserId = $_SESSION['user_id'] ?? null;
                    $facultyResult = $conn->query("
                        SELECT f.faculty_id, f.name, f.college 
                        FROM faculty f
                        LEFT JOIN users u ON f.user_id = u.id
                        WHERE f.role = 'professor' AND (u.role = 'professor' OR u.id IS NULL)
                        ORDER BY f.name ASC
                    ");
                    if ($facultyResult && $facultyResult->num_rows > 0) {
                        $foundAny = false;
                        while ($row = $facultyResult->fetch_assoc()) {
                            $facultyId = htmlspecialchars($row['faculty_id']);
                            $name = htmlspecialchars($row['name']);
                            $collegeValue = htmlspecialchars($row['college']);
                            
                            // Check if this faculty is the current user (exclude self-evaluation)
                            $checkStmt = $conn->prepare("SELECT id FROM faculty WHERE faculty_id = ? AND user_id = ?");
                            $checkStmt->bind_param('si', $row['faculty_id'], $currentUserId);
                            $checkStmt->execute();
                            $selfResult = $checkStmt->get_result();
                            $checkStmt->close();
                            
                            if ($selfResult->num_rows === 0) {
                                echo "<option value=\"$facultyId\" data-college=\"$collegeValue\">$name</option>";
                                $foundAny = true;
                            }
                        }
                        if (!$foundAny) {
                            echo "<option value=\"\" disabled>No available professors</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="faculty_code">Faculty ID</label>
                <input type="text" id="faculty_code" name="faculty_code"
                       class="faculty-id-input"
                       pattern="[0-9]{4}-[0-9]{2}-[0-9]{5}" placeholder="0000-00-00000"
                       maxlength="13" autocomplete="off" inputmode="numeric" readonly
                       aria-describedby="faculty_code_error" required>
                <div class="field-error" id="faculty_code_error"></div>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3>⭐ Ratings (1 = Poor, 5 = Excellent)</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="score1">Class Attendance</label>
                <div class="score-row">
                    <input type="number" id="score1" name="score1" min="1" max="5" required oninput="validateScore(this)">
                    <span class="score-hint">1 – 5</span>
                </div>
            </div>
            <div class="form-group">
                <label for="score2">Professional Commitment</label>
                <div class="score-row">
                    <input type="number" id="score2" name="score2" min="1" max="5" required oninput="validateScore(this)">
                    <span class="score-hint">1 – 5</span>
                </div>
            </div>
            <div class="form-group">
                <label for="score3">Work Quality</label>
                <div class="score-row">
                    <input type="number" id="score3" name="score3" min="1" max="5" required oninput="validateScore(this)">
                    <span class="score-hint">1 – 5</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3>💬 Comments</h3>
        <div class="form-group">
            <label for="comments">Remarks</label>
            <textarea id="comments" name="comments" rows="4"
                      placeholder="Any additional observations or notes…"></textarea>
        </div>
    </div>

    <div class="submit-row">
        <button class="btn btn-primary" type="submit">✓ Submit Evaluation</button>
    </div>
</form>

<script src="<?= $basePath ?>/public/js/script.js"></script>

<?php
$content = ob_get_clean();
include_once("../layout.php");
?>
