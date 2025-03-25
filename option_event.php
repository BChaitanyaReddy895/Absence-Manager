<?php
header('Content-Type: application/json');

// Read input and check both php://input and $_POST
$rawData = file_get_contents('php://input');
$postData = $_POST;

// Debug: Write received data to a log file
file_put_contents("debug_log.txt", "Raw Data: " . $rawData . PHP_EOL, FILE_APPEND);
file_put_contents("debug_log.txt", "POST Data: " . print_r($postData, true) . PHP_EOL, FILE_APPEND);

// Check if input is valid JSON
$data = json_decode($rawData, true);
if (!$data) {
    echo json_encode([
        'type' => 'selection_response',
        'success' => false,
        'message' => 'Error: Invalid JSON received. Check debug_log.txt'
    ]);
    exit;
}

// Check if selection exists
if (!isset($data['data']['selection'])) {
    echo json_encode([
        'type' => 'selection_response',
        'success' => false,
        'message' => 'No selection made. Please select an option.'
    ]);
    exit;
}

// Extract selection
$selection = $data['data']['selection'];

// Define redirection URLs
$redirectUrls = [
    "individual" => "individual.html",
    "team" => "team.html"
];

// Validate and respond
if (array_key_exists($selection, $redirectUrls)) {
    echo json_encode([
        'type' => 'selection_response',
        'success' => true,
        'message' => 'Redirecting to ' . ucfirst($selection) . ' page...',
        'redirect' => $redirectUrls[$selection]
    ]);
} else {
    echo json_encode([
        'type' => 'selection_response',
        'success' => false,
        'message' => 'Invalid selection. Please try again.'
    ]);
}
?>
