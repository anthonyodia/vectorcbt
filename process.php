<?php
// process.php
// This detects the requested action and processes the correct JSON file
$jsonFile = $jsonFile ?? 'default.json'; // The subject page defines $jsonFile

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    // 1. Load data
    $data = json_decode(file_get_contents($jsonFile), true);
    
    // 2. Route actions
    if ($action === 'get_questions') {
        // Here, add your logic to parse the specific JSON structure 
        // and echo the standardized JSON for the engine.
    }
    
    if ($action === 'submit') {
        // Calculate score and echo result
    }

    if ($action === 'get_explanations') {
        // Return details for the review screen
    }
    
    if ($action === 'get_ai_help') {
        // Your existing Groq API code
    }
    exit();
}
?>
