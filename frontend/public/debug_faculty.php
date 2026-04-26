<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../public/index.php");
    exit;
}

include_once("../../backend/config/database.php");
include_once("../../backend/controllers/scoreController.php");

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Faculty Data</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Debug Faculty Data</h1>

    <h2>Faculty Count: <?php echo count($facultyData); ?></h2>

    <?php if (empty($facultyData)): ?>
        <p class="error">❌ No faculty data found! Check your database.</p>
    <?php else: ?>
        <p class="success">✅ Faculty data loaded successfully</p>
    <?php endif; ?>

    <h2>Raw Faculty JSON:</h2>
    <pre><?php echo $facultyJson; ?></pre>

    <h2>Test AI Search:</h2>
    <form method="POST" action="../../backend/controllers/aiSearchController.php" target="_blank">
        <input type="hidden" name="query" value="high score teachers">
        <input type="hidden" name="facultyData" value='<?php echo htmlspecialchars($facultyJson); ?>'>
        <button type="submit">Test AI Search</button>
    </form>

    <script>
        const FACULTY_DATA = <?php echo $facultyJson; ?>;
        console.log('FACULTY_DATA:', FACULTY_DATA);

        // Test the fetch call
        async function testFetch() {
            try {
                const res = await fetch('../../backend/controllers/aiSearchController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: 'high score teachers', facultyData: FACULTY_DATA })
                });

                const data = await res.json();
                console.log('AI Search Response:', data);
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch(e) {
                console.error('Error:', e);
                document.getElementById('result').innerHTML = '<p class="error">Error: ' + e.message + '</p>';
            }
        }

        // Auto-test on page load
        testFetch();
    </script>

    <h2>AI Search Test Result:</h2>
    <div id="result">Testing...</div>
</body>
</html>
