<?php
session_start();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../../public/index.php");
    exit;
}

$basePath = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
if ($basePath === '') { $basePath = '/'; }

// Wrap the DB include so a connection failure (die()) doesn't truncate
// the buffered output mid-form. The $dbError flag lets us show an inline
// warning instead of a blank/cut-off page.
ob_start();
$dbPath = realpath(__DIR__ . '/../../../backend/config/database.php');
if ($dbPath) {
    include_once($dbPath);
} else {
    error_log('Database config not found at expected path');
}
ob_end_clean();
$dbError = !isset($conn) || (is_object($conn) && $conn->connect_errno);

ob_start();
?>

<div class="page-title">Student Evaluation Form</div>
<div class="page-subtitle">Rate your faculty members to help improve teaching quality</div>

<div class="progress-banner">
    <div class="progress-banner-top">
        <span class="progress-banner-label">Form Completion</span>
        <span class="progress-banner-pct" id="progressPercent">0%</span>
    </div>
    <div class="progress-track">
        <div class="progress-fill" id="progressFill"></div>
    </div>
</div>

<form id="evaluationForm" data-autosave-id="student-eval"
      method="POST" action="../../../backend/controllers/evaluationController.php">

    <div class="form-section">
        <h3>🎓 Faculty &amp; College</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="college">Department</label>
                <select id="college" name="college" required>
                    <option value="" disabled selected>Select department…</option>
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
                    <option value="" disabled selected>Select department first…</option>
                    <?php
                    if (!$dbError) {
                        $facultyResult = $conn->query("
                            SELECT f.faculty_id, f.name, f.college 
                            FROM faculty f
                            LEFT JOIN users u ON f.user_id = u.id
                            WHERE f.role = 'professor' AND (u.role = 'professor' OR u.id IS NULL)
                            ORDER BY f.name ASC
                        ");
                        if ($facultyResult && $facultyResult->num_rows > 0) {
                            while ($row = $facultyResult->fetch_assoc()) {
                                $facultyId    = htmlspecialchars($row['faculty_id']);
                                $name         = htmlspecialchars($row['name']);
                                $collegeValue = htmlspecialchars($row['college']);
                                echo "<option value=\"$facultyId\" data-college=\"$collegeValue\">$name</option>";
                            }
                        } else {
                            echo "<option value=\"\" disabled>No professors available</option>";
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
                <label for="score1">Teaching Clarity</label>
                <div class="score-row">
                    <input type="number" id="score1" name="score1" min="1" max="5" required oninput="validateScore(this)">
                    <span class="score-hint">1 – 5</span>
                </div>
            </div>
            <div class="form-group">
                <label for="score2">Student Engagement</label>
                <div class="score-row">
                    <input type="number" id="score2" name="score2" min="1" max="5" required oninput="validateScore(this)">
                    <span class="score-hint">1 – 5</span>
                </div>
            </div>
            <div class="form-group">
                <label for="score3">Fairness &amp; Impartiality</label>
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
