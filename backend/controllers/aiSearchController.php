<?php
require_once '../config/google_ai.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';
$faculty_data = $input['facultyData'] ?? [];

// Debug: Log API key status
$log_msg = "DEBUG: API Key present: " . (!empty(GOOGLE_AI_API_KEY) ? "YES" : "NO") . "\n";
$log_msg .= "DEBUG: Query: " . $query . "\n";
$log_msg .= "DEBUG: Faculty data count: " . count($faculty_data) . "\n";
error_log($log_msg);

if (empty($query) || empty($faculty_data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing query or facultyData']);
    exit;
}

try {
    // Check if API key is configured
    if (empty(GOOGLE_AI_API_KEY)) {
        throw new Exception('Google AI API key not configured in .env file');
    }

    // Format faculty data for the prompt
    $summary = '';
    foreach ($faculty_data as $f) {
        $summary .= "ID:{$f['faculty_id']} | Name:{$f['name']} | Dept:{$f['college']} | Score:" . number_format($f['final_score'], 2) . " | Evals:{$f['eval_count']}\n";
    }

    $prompt = "You are a filter assistant. Return ONLY a JSON array of matching faculty_id values — no explanation, no markdown.\n\nFaculty:\n$summary\n\nQuery: \"$query\"\n\nRules: match by name, department, score, or evals. \"high scores\"=score>=3.5, \"low scores\"=score<2.5, \"most evaluated\"=evals>=3. Return [] if none. ONLY JSON array.";

    error_log("DEBUG: Calling Google AI with prompt length: " . strlen($prompt));
    $response = callGoogleAI($prompt);
    error_log("DEBUG: Got response length: " . strlen($response));
    
    // Extract JSON array from response
    $matches = [];
    if (preg_match('/\[.*\]/s', $response, $matches)) {
        $ids = json_decode($matches[0], true);
        if (is_array($ids)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'ids' => array_map('strval', $ids)]);
            exit;
        }
    }
    
    throw new Exception('Invalid AI response format: ' . substr($response, 0, 100));

} catch (Exception $e) {
    error_log("ERROR in aiSearchController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
