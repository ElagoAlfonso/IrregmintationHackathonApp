<?php
// Test the AI Search Controller directly
echo "<h2>AI Search Endpoint Test</h2>";

$test_data = json_encode([
    'query' => 'high score teachers',
    'facultyData' => [
        ['faculty_id' => 1, 'name' => 'Dr. Smith', 'college' => 'Computer Studies', 'final_score' => 4.5, 'eval_count' => 5],
        ['faculty_id' => 2, 'name' => 'Prof. Jones', 'college' => 'Business Admin', 'final_score' => 3.2, 'eval_count' => 2],
        ['faculty_id' => 3, 'name' => 'Dr. Brown', 'college' => 'Accountancy', 'final_score' => 2.1, 'eval_count' => 4]
    ]
]);

$ch = curl_init('http://localhost/hackathon/backend/controllers/aiSearchController.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $http_code</p>";
if ($curl_error) {
    echo "<p style='color:red'><strong>cURL Error:</strong> $curl_error</p>";
} else {
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>
