<?php
require_once '../../backend/config/google_ai.php';

echo "<h2>Available Google AI Models</h2>";

if (empty(GOOGLE_AI_API_KEY)) {
    echo "<p style='color:red'><strong>Error:</strong> API key not found</p>";
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode(GOOGLE_AI_API_KEY);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $http_code</p>";

if ($curl_error) {
    echo "<p style='color:red'><strong>cURL Error:</strong> $curl_error</p>";
} else {
    $data = json_decode($response, true);
    if (isset($data['error'])) {
        echo "<p style='color:red'><strong>API Error:</strong> " . htmlspecialchars($data['error']['message']) . "</p>";
    } elseif (isset($data['models'])) {
        echo "<h3>Available Models:</h3>";
        echo "<ul>";
        foreach ($data['models'] as $model) {
            echo "<li><strong>" . htmlspecialchars($model['name']) . "</strong>";
            if (isset($model['description'])) {
                echo " - " . htmlspecialchars($model['description']);
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    echo "<p><strong>Full Response:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode(json_decode($response, true), JSON_PRETTY_PRINT)) . "</pre>";
}
?>
