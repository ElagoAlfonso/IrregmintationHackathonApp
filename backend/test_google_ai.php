<?php
require_once 'config/google_ai.php';

echo "<h2>Google AI Connection Test</h2>";

// Check if API key exists
echo "<p><strong>API Key Status:</strong> ";
echo !empty(GOOGLE_AI_API_KEY) ? "✓ Configured" : "✗ Not configured";
echo "</p>";

// Check if cURL is enabled
echo "<p><strong>cURL Status:</strong> ";
echo extension_loaded('curl') ? "✓ Enabled" : "✗ Disabled";
echo "</p>";

// Test API connection
echo "<p><strong>Testing API Connection...</strong></p>";
try {
    $test_prompt = "Say 'Hello, the connection is working!' in exactly those words.";
    $response = callGoogleAI($test_prompt);
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✓ Success!</strong><br>";
    echo "<strong>Response:</strong> " . htmlspecialchars($response);
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✗ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>
