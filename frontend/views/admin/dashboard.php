<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}

include_once("../../../backend/config/database.php");
include_once("../../../backend/controllers/scoreController.php");

$totalEvaluations = (int)$conn->query("SELECT COUNT(*) as t FROM evaluations")->fetch_assoc()['t'];
$facultyCount     = (int)$conn->query("SELECT COUNT(*) as t FROM faculty")->fetch_assoc()['t'];

$goalEvaluations = 100;
$progressPercent = $goalEvaluations > 0 ? min(round(($totalEvaluations / $goalEvaluations) * 100, 1), 100) : 0;

$facultyResult = $conn->query("SELECT id, faculty_id, name, college, department FROM faculty ORDER BY name ASC");
$facultySummary = [];
if ($facultyResult) {
    while ($row = $facultyResult->fetch_assoc()) {
        $fid  = $conn->real_escape_string($row['faculty_id']);
        $evalQ = $conn->query("SELECT COUNT(*) as cnt FROM evaluations WHERE faculty_id = '$fid'");
        $evalCount  = $evalQ ? (int)$evalQ->fetch_assoc()['cnt'] : 0;
        $finalScore = calculate_final_score($conn, $row['faculty_id']);
        $facultySummary[] = [
            'faculty_id'  => $row['faculty_id'],
            'name'        => $row['name'],
            'college'     => $row['college']    ?? '—',
            'department'  => $row['department'] ?? '—',
            'eval_count'  => $evalCount,
            'final_score' => $finalScore,
        ];
    }
}

$maxEvals = max(array_column($facultySummary, 'eval_count') ?: [1]);
$maxEvals = max($maxEvals, 1);

ob_start();
?>

<div class="page-title">Dashboard</div>
<div class="page-subtitle">Overview of faculty evaluations and system metrics</div>

<div class="stats-grid">
    <div class="stat-card color-blue">
        <div class="stat-label">Total Evaluations</div>
        <div class="stat-value"><?php echo $totalEvaluations; ?></div>
        <div class="stat-sub">Goal: <?php echo $goalEvaluations; ?> evaluations</div>
        <div class="stat-progress-track">
            <div class="stat-progress-fill" style="width: <?php echo $progressPercent; ?>%"></div>
        </div>
    </div>
    <div class="stat-card color-green">
        <div class="stat-label">Faculty Members</div>
        <div class="stat-value"><?php echo $facultyCount; ?></div>
        <div class="stat-sub">Active in system</div>
    </div>
    <div class="stat-card color-amber">
        <div class="stat-label">Completion</div>
        <div class="stat-value"><?php echo $progressPercent; ?>%</div>
        <div class="stat-sub"><?php echo $progressPercent >= 100 ? '✓ Goal achieved' : 'Toward goal of ' . $goalEvaluations; ?></div>
        <div class="stat-progress-track">
            <div class="stat-progress-fill" style="width: <?php echo $progressPercent; ?>%"></div>
        </div>
    </div>
</div>

<div class="section-hdr">
    <h2>Faculty Evaluation Summary</h2>
    <a href="faculty_list.php">View all &rsaquo;</a>
</div>

<div class="faculty-table-wrap">
    <?php if (empty($facultySummary)): ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p>No faculty members yet. <a href="faculty_list.php">Add one</a>.</p>
        </div>
    <?php else: ?>
    <table class="faculty-table">
        <thead>
            <tr>
                <th>Faculty Member</th>
                <th>Department</th>
                <th>Evaluations</th>
                <th>Final Score</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($facultySummary as $f): ?>
            <tr>
                <td><div class="ft-name"><?php echo htmlspecialchars($f['name']); ?></div></td>
                <td class="muted"><?php echo htmlspecialchars($f['college']); ?></td>
                <td>
                    <div class="eval-progress-wrap">
                        <div class="eval-progress-track">
                            <div class="eval-progress-fill"
                                 style="width: <?php echo round(($f['eval_count'] / $maxEvals) * 100); ?>%">
                            </div>
                        </div>
                        <span class="eval-progress-label"><?php echo $f['eval_count']; ?></span>
                    </div>
                </td>
                <td>
                    <?php if ($f['final_score'] > 0): ?>
                        <span class="badge badge-blue"><?php echo number_format($f['final_score'], 2); ?></span>
                    <?php else: ?>
                        <span style="color:var(--text-3);font-size:13px">No data</span>
                    <?php endif; ?>
                </td>
                <td class="ft-actions">
                    <a href="reports.php?faculty_id=<?php echo htmlspecialchars($f['faculty_id']); ?>">📊 Report</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("../layout.php");
?>
