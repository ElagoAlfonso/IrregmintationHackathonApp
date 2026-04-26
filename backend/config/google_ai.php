<?php
// Load environment variables
$env_file = dirname(__DIR__, 2) . '/.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    $env = [];
}

// Google AI Configuration
define('GOOGLE_AI_API_KEY', $env['GOOGLE_AI_API_KEY'] ?? '');
define('GOOGLE_AI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

// Function to call Google AI
function callGoogleAI($prompt) {
    if (empty(GOOGLE_AI_API_KEY)) {
        throw new Exception('Google AI API key not configured');
    }

    $url = GOOGLE_AI_BASE_URL . '?key=' . urlencode(GOOGLE_AI_API_KEY);
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for cURL errors
    if ($curl_error) {
        throw new Exception('cURL Error: ' . $curl_error);
    }

    // Check for HTTP errors
    if ($http_code !== 200) {
        $error_msg = 'Google AI API Error (HTTP ' . $http_code . '): ' . $response;
        throw new Exception($error_msg);
    }

    $result = json_decode($response, true);
    
    // Handle API response errors
    if (isset($result['error'])) {
        throw new Exception('Google AI Error: ' . $result['error']['message']);
    }

    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid API response structure: ' . json_encode($result));
    }

    return $result['candidates'][0]['content']['parts'][0]['text'];
}
?>
