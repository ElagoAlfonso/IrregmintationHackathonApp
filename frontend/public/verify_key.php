<?php
$api_key = 'AIzaSyDjchn6x2XOw7PmDjijpgSTO-aGEGjMaL4';

echo "<h2>Testing New API Key</h2>";

// Test 1: List models
echo "<h3>Available Models:</h3>";
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($api_key);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $http_code</p>";
$data = json_decode($response, true);

if (isset($data['error'])) {
    echo "<p style='color:red'><strong>Error:</strong> " . htmlspecialchars($data['error']['message']) . "</p>";
} elseif (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        echo "<li>" . htmlspecialchars($model['name']) . "</li>";
    }
}

// Test 2: Call API with a simple prompt
echo "<h3>Test API Call (gemini-2.5-flash):</h3>";

$test_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($api_key);
$test_data = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => 'Say "Hello"']
            ]
        ]
    ]
]);

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $http_code</p>";
$data = json_decode($response, true);

if (isset($data['error'])) {
    echo "<p style='color:red'><strong>Error:</strong> " . htmlspecialchars($data['error']['message']) . "</p>";
} else {
    echo "<p style='color:green'><strong>✓ Success!</strong></p>";
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        echo "<p><strong>Response:</strong> " . htmlspecialchars($data['candidates'][0]['content']['parts'][0]['text']) . "</p>";
    }
}
?>
