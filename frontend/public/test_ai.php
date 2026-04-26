<?php
require_once '../backend/config/google_ai.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Google AI Connection Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Google AI Connection Test</h1>
    
    <div class="info">
        <h3>System Status:</h3>
        <p><strong>API Key Configured:</strong> <?php echo !empty(GOOGLE_AI_API_KEY) ? "✓ Yes" : "✗ No"; ?></p>
        <p><strong>cURL Extension:</strong> <?php echo extension_loaded('curl') ? "✓ Enabled" : "✗ Disabled"; ?></p>
        <p><strong>API Endpoint:</strong> <code><?php echo GOOGLE_AI_BASE_URL; ?></code></p>
    </div>

    <h2>Test API Connection</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $prompt = $_POST['prompt'] ?? '';
        
        if (empty($prompt)) {
            echo '<div class="error">Please enter a prompt</div>';
        } else {
            try {
                echo '<div class="info">Processing: <em>' . htmlspecialchars($prompt) . '</em></div>';
                $response = callGoogleAI($prompt);
                echo '<div class="success">';
                echo '<strong>✓ Success!</strong><br><br>';
                echo '<strong>Response:</strong><br>';
                echo '<pre>' . htmlspecialchars($response) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>✗ Error:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
    }
    ?>
    
    <form method="POST">
        <label for="prompt">Test Prompt:</label><br><br>
        <textarea id="prompt" name="prompt" rows="4" cols="50" placeholder="Enter a test prompt..."></textarea><br><br>
        <button type="submit">Test Connection</button>
    </form>
</body>
</html>
