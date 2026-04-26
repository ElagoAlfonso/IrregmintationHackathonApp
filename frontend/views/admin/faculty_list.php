<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}

include_once("../../../backend/config/database.php");
include_once("../../../backend/controllers/scoreController.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    $faculty_id = $conn->real_escape_string($_POST['faculty_id']);
    $name       = $conn->real_escape_string($_POST['name']);
    $department = $conn->real_escape_string($_POST['department'] ?? '');
    $college    = $conn->real_escape_string($_POST['college']);
    $conn->query("INSERT INTO faculty (faculty_id, name, department, college) VALUES ('$faculty_id', '$name', '$department', '$college')");
    header("Location: faculty_list.php");
    exit;
}

$result = $conn->query("SELECT * FROM faculty ORDER BY name ASC");
$facultyData = [];
while ($row = $result->fetch_assoc()) {
    $fid  = $row['faculty_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM evaluations WHERE faculty_id = ?");
    $stmt->bind_param("s", $fid);
    $stmt->execute();
    $evalCount = (int)$stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();
    $facultyData[] = [
        'id'          => $row['id'],
        'faculty_id'  => $fid,
        'name'        => $row['name'],
        'college'     => $row['college']    ?? '',
        'department'  => $row['department'] ?? '',
        'eval_count'  => $evalCount,
        'final_score' => (float)calculate_final_score($conn, $fid),
    ];
}

$facultyJson = json_encode($facultyData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

ob_start();
?>

<div class="page-title">Faculty List</div>
<div class="page-subtitle">Manage faculty members and search with AI</div>

<div class="ai-search-wrap">
    <div class="ai-search-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Smart Search <span class="ai-badge">AI</span>
    </div>
    <div class="ai-search-row">
        <input type="text" id="aiQuery" placeholder='e.g. "faculty in Computer Studies" or "teachers with high scores"'>
        <button class="ai-search-btn" id="aiSearchBtn" onclick="runAiSearch()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Ask AI
        </button>
        <button class="ai-clear-btn" id="clearBtn" onclick="clearSearch()">✕ Clear</button>
    </div>
    <div class="ai-status" id="aiStatus">Type a query and click Ask AI, or use the quick filters below</div>
    <div class="ai-pills">
        <button class="ai-pill" onclick="quickSearch('faculty in Department of Computer Studies')">Computer Studies</button>
        <button class="ai-pill" onclick="quickSearch('faculty in Department of Business Administration')">Business Admin</button>
        <button class="ai-pill" onclick="quickSearch('faculty in Department of Accountancy')">Accountancy</button>
        <button class="ai-pill" onclick="quickSearch('faculty with highest scores')">Top performers</button>
        <button class="ai-pill" onclick="quickSearch('faculty with more than 3 evaluations')">Most evaluated</button>
    </div>
</div>

<div class="add-panel">
    <div class="add-panel-title">➕ Add New Faculty Member</div>
    <form method="POST" action="">
        <div class="form-row" style="margin-bottom:16px">
            <div class="form-group">
                <label for="faculty_id">Faculty ID</label>
                <input type="text" id="faculty_id" name="faculty_id"
                       class="faculty-id-input"
                       pattern="[0-9]{4}-[0-9]{2}-[0-9]{5}" placeholder="0000-00-00000"
                       maxlength="13" autocomplete="off" inputmode="numeric" required>
            </div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full name" required>
            </div>
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
        </div>
        <button class="btn btn-primary" type="submit" name="add_faculty">Add Faculty</button>
    </form>
</div>

<div class="section-hdr">
    <div style="font-size:15px;font-weight:600;color:var(--text-1)">All Faculty Members</div>
    <span class="count-badge" id="visibleCount"><?php echo count($facultyData); ?> members</span>
</div>

<div class="faculty-grid" id="facultyGrid">
    <?php
    $colors = ['#4361ee','#7c3aed','#0891b2','#059669','#dc2626','#d97706'];
    foreach ($facultyData as $i => $f):
        $color      = $colors[$i % count($colors)];
        $scoreLabel = $f['final_score'] > 0 ? number_format($f['final_score'], 2) : '—';
    ?>
    <div class="faculty-card"
         style="--fc-color:<?php echo $color; ?>"
         data-id="<?php echo htmlspecialchars($f['faculty_id']); ?>"
         data-score="<?php echo $f['final_score']; ?>"
         data-evals="<?php echo $f['eval_count']; ?>">
        <div class="fc-name"><?php echo htmlspecialchars($f['name']); ?></div>
        <div class="fc-dept"><?php echo htmlspecialchars($f['college'] ?: 'No department assigned'); ?></div>
        <div class="fc-meta">
            <span class="fc-score">Score: <?php echo $scoreLabel; ?></span>
            <span class="fc-evals"><?php echo $f['eval_count']; ?> eval<?php echo $f['eval_count'] !== 1 ? 's' : ''; ?></span>
        </div>
        <a href="reports.php?faculty_id=<?php echo htmlspecialchars($f['faculty_id']); ?>" class="view-btn">📊 View Report</a>
    </div>
    <?php endforeach; ?>
    <?php if (empty($facultyData)): ?>
    <div class="empty-state" style="grid-column:1/-1">
        <div class="empty-icon">👥</div>
        <p>No faculty members found. Add one above.</p>
    </div>
    <?php endif; ?>
</div>

<div id="noResults" class="empty-state" style="display:none">
    <div class="empty-icon">🤖</div>
    <p>No faculty matched your query. Try rephrasing or
       <button onclick="clearSearch()" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:14px;font-weight:600;padding:0">clear the search</button>.
    </p>
</div>

<script src="../../public/js/script.js"></script>
<script>
const FACULTY_DATA = <?php echo $facultyJson; ?>;

function quickSearch(q) { document.getElementById('aiQuery').value = q; runAiSearch(); }

async function runAiSearch() {
    const query = document.getElementById('aiQuery').value.trim();
    if (!query) return;
    const btn = document.getElementById('aiSearchBtn');
    const statusEl = document.getElementById('aiStatus');
    btn.disabled = true;
    btn.innerHTML = '<span class="dot-loader"><span></span><span></span><span></span></span>';
    statusEl.className = 'ai-status thinking';
    statusEl.innerHTML = '<span class="dot-loader"><span></span><span></span><span></span></span> Reading your query…';

    const summary = FACULTY_DATA.map(f =>
        `ID:${f.faculty_id} | Name:${f.name} | Dept:${f.college} | Score:${f.final_score.toFixed(2)} | Evals:${f.eval_count}`
    ).join('\n');

    const prompt = `You are a filter assistant. Return ONLY a JSON array of matching faculty_id values — no explanation, no markdown.\n\nFaculty:\n${summary}\n\nQuery: "${query}"\n\nRules: match by name, department, score, or evals. "high scores"=score>=3.5, "low scores"=score<2.5, "most evaluated"=evals>=3. Return [] if none. ONLY JSON array.`;

    try {
        const res  = await fetch('https://api.anthropic.com/v1/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ model: 'claude-sonnet-4-20250514', max_tokens: 500, messages: [{ role: 'user', content: prompt }] })
        });
        const data = await res.json();
        const raw  = (data.content?.[0]?.text || '').trim();
        const m    = raw.match(/\[.*\]/s);
        if (!m) throw new Error('No array');
        const ids  = JSON.parse(m[0]);
        applyFilter(ids, query);
        document.getElementById('clearBtn').style.display = '';
        statusEl.className = ids.length === 0 ? 'ai-status error' : 'ai-status found';
        statusEl.textContent = ids.length === 0
            ? `No faculty matched "${query}"`
            : `✓ ${ids.length} result${ids.length !== 1 ? 's' : ''} for "${query}"`;
    } catch(e) {
        statusEl.className = 'ai-status error';
        statusEl.textContent = '⚠ AI search failed — try again or use the quick filters.';
    }
    btn.disabled = false;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Ask AI';
}

function applyFilter(ids, query) {
    const cards = document.querySelectorAll('.faculty-card');
    const noRes = document.getElementById('noResults');
    const counter = document.getElementById('visibleCount');
    if (ids.length === 0) {
        cards.forEach(c => { c.classList.add('ai-dimmed'); c.classList.remove('ai-highlight'); });
        noRes.style.display = '';
        counter.textContent = '0 results';
        return;
    }
    noRes.style.display = 'none';
    let visible = 0;
    cards.forEach(card => {
        const match = ids.includes(card.getAttribute('data-id'));
        card.classList.toggle('ai-highlight', match);
        card.classList.toggle('ai-dimmed', !match);
        if (match) visible++;
    });
    counter.textContent = `${visible} result${visible !== 1 ? 's' : ''}`;
}

function clearSearch() {
    document.getElementById('aiQuery').value = '';
    document.getElementById('clearBtn').style.display = 'none';
    const s = document.getElementById('aiStatus');
    s.className = 'ai-status';
    s.textContent = 'Type a query and click Ask AI, or use the quick filters below';
    document.getElementById('visibleCount').textContent = `${FACULTY_DATA.length} members`;
    document.querySelectorAll('.faculty-card').forEach(c => c.classList.remove('ai-dimmed','ai-highlight'));
    document.getElementById('noResults').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('aiQuery').addEventListener('keydown', e => { if (e.key === 'Enter') runAiSearch(); });
});
</script>

<?php
$content = ob_get_clean();
include_once("../layout.php");
?>
