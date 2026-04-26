<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}

include_once("../../../backend/config/database.php");
include_once("../../../backend/controllers/scoreController.php");

$faculty_id   = isset($_GET['faculty_id']) ? trim($_GET['faculty_id']) : null;
$faculty_name = null;
$faculty_data = null;
if ($faculty_id) {
    $q = $conn->prepare("SELECT name, college, department FROM faculty WHERE faculty_id = ?");
    $q->bind_param("s", $faculty_id); $q->execute();
    $r = $q->get_result();
    if ($r->num_rows > 0) { $faculty_data = $r->fetch_assoc(); $faculty_name = $faculty_data['name']; }
    else { $faculty_id = null; }
    $q->close();
}

$totalEvaluations = (int)$conn->query("SELECT COUNT(*) as t FROM evaluations")->fetch_assoc()['t'];

$collegeData = [];
$cq = $conn->query("SELECT college, COUNT(*) as cnt FROM evaluations GROUP BY college ORDER BY cnt DESC");
while ($row = $cq->fetch_assoc()) $collegeData[] = $row;

$fq = $conn->query("SELECT faculty_id, name FROM faculty");
$scores = [];
while ($row = $fq->fetch_assoc()) {
    $scores[] = ['faculty_id' => $row['faculty_id'], 'name' => $row['name'], 'score' => calculate_final_score($conn, $row['faculty_id'])];
}
usort($scores, fn($a,$b) => $b['score'] <=> $a['score']);
$topFaculty = array_slice($scores, 0, 10);

ob_start();
?>

<?php if ($faculty_id): ?>
<div class="page-title">Faculty Report</div>
<div class="page-subtitle">Detailed evaluation data for <?php echo htmlspecialchars($faculty_name); ?></div>

<div class="faculty-hero">
    <h2><?php echo htmlspecialchars($faculty_name); ?></h2>
    <?php
    $finalScore = calculate_final_score($conn, $faculty_id);
    $sq = $conn->prepare("SELECT COUNT(*) as cnt FROM evaluations WHERE faculty_id = ?");
    $sq->bind_param("s", $faculty_id); $sq->execute();
    $totalEval = (int)$sq->get_result()->fetch_assoc()['cnt']; $sq->close();
    ?>
    <div class="hero-row"><span class="hero-row-label">Final Score</span><span class="hero-row-value"><?php echo number_format($finalScore, 2); ?> / 5.00</span></div>
    <div class="hero-row"><span class="hero-row-label">Total Evaluations</span><span class="hero-row-value"><?php echo $totalEval; ?></span></div>
    <div class="hero-row"><span class="hero-row-label">Department</span><span class="hero-row-value" style="font-size:14px"><?php echo htmlspecialchars($faculty_data['college'] ?? '—'); ?></span></div>
    <div class="hero-row"><span class="hero-row-label">Rating Scale</span><span class="hero-row-value" style="font-size:14px">1 – 5</span></div>
</div>

<div class="report-block">
    <h2>Breakdown by Evaluator Role</h2>
    <div class="breakdown-grid">
        <?php foreach (['student' => 'Students (50%)', 'program_head' => 'Program Head (30%)', 'dean' => 'Dean (20%)'] as $role => $label):
            $avg = get_avg_score($conn, $faculty_id, $role);
            $cq2 = $conn->prepare("SELECT COUNT(*) as cnt FROM evaluations WHERE faculty_id = ? AND rater_role = ?");
            $cq2->bind_param("ss", $faculty_id, $role); $cq2->execute();
            $cnt = (int)$cq2->get_result()->fetch_assoc()['cnt']; $cq2->close();
        ?>
        <div class="breakdown-card">
            <h4><?php echo $label; ?></h4>
            <div class="breakdown-value"><?php echo $avg > 0 ? number_format($avg, 2) : '—'; ?></div>
            <div class="breakdown-sub"><?php echo $cnt; ?> evaluation<?php echo $cnt !== 1 ? 's' : ''; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="report-block">
    <h2>Recent Comments</h2>
    <?php
    $commQ = $conn->prepare("SELECT comments, rater_role, created_at FROM evaluations WHERE faculty_id = ? AND comments IS NOT NULL AND comments != '' ORDER BY created_at DESC LIMIT 10");
    $commQ->bind_param("s", $faculty_id); $commQ->execute();
    $commRes = $commQ->get_result();
    if ($commRes->num_rows > 0): ?>
    <div class="comments-list">
        <?php while ($c = $commRes->fetch_assoc()): ?>
        <div class="comment-item">
            <div class="comment-role"><?php echo ucfirst(str_replace('_', ' ', $c['rater_role'])); ?></div>
            <div class="comment-text"><?php echo htmlspecialchars($c['comments']); ?></div>
            <div class="comment-date"><?php echo $c['created_at']; ?></div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p style="color:var(--text-3);font-size:14px">No comments submitted yet.</p>
    <?php endif; $commQ->close(); ?>
</div>

<div class="nav-links">
    <a href="reports.php" class="btn btn-ghost">← Back to Reports</a>
    <a href="faculty_list.php" class="btn btn-ghost">← Faculty List</a>
</div>

<?php else: ?>
<div class="page-title">Reports</div>
<div class="page-subtitle">System-wide evaluation analytics</div>

<div class="report-block">
    <h2>Overall Assessment</h2>
    <div class="breakdown-grid">
        <div class="breakdown-card">
            <h4>Total Evaluations</h4>
            <div class="breakdown-value"><?php echo $totalEvaluations; ?></div>
            <div class="breakdown-sub">Goal: 100+</div>
        </div>
        <div class="breakdown-card">
            <h4>Progress</h4>
            <div class="breakdown-value"><?php echo min(round(($totalEvaluations / 100) * 100, 1), 100); ?>%</div>
            <div class="breakdown-sub"><?php echo $totalEvaluations >= 100 ? '✓ Achieved' : 'In progress'; ?></div>
        </div>
        <div class="breakdown-card">
            <h4>Faculty Evaluated</h4>
            <div class="breakdown-value"><?php echo count($scores); ?></div>
            <div class="breakdown-sub">Members in system</div>
        </div>
    </div>
</div>

<div class="report-block">
    <h2>Evaluations by College</h2>
    <div class="college-grid">
        <?php foreach ($collegeData as $d): ?>
        <div class="college-card">
            <div class="college-name"><?php echo htmlspecialchars($d['college']); ?></div>
            <div class="college-count"><?php echo $d['cnt']; ?></div>
            <div class="college-sub">evaluations</div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($collegeData)): ?><p style="color:var(--text-3);font-size:14px">No data yet.</p><?php endif; ?>
    </div>
</div>

<div class="report-block">
    <h2>Top Faculty by Final Score</h2>
    <div class="ranking-list">
        <?php foreach ($topFaculty as $i => $f): ?>
        <div class="ranking-item">
            <div class="ranking-left">
                <span class="rank-num <?php echo $i===0?'gold':($i===1?'silver':($i===2?'bronze':'')); ?>"><?php echo $i+1; ?></span>
                <a href="?faculty_id=<?php echo htmlspecialchars($f['faculty_id']); ?>" class="ranking-name"><?php echo htmlspecialchars($f['name']); ?></a>
            </div>
            <span class="ranking-score"><?php echo number_format($f['score'], 2); ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($topFaculty)): ?><p style="color:var(--text-3);font-size:14px;padding:10px">No data yet.</p><?php endif; ?>
    </div>
</div>

<div class="nav-links">
    <a href="faculty_list.php" class="btn btn-primary">👥 View All Faculty</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include_once("../layout.php");
?>
