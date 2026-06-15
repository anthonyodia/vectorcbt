<?php
// ai_logic.php - Handles the AI request
if (isset($_GET['action']) && $_GET['action'] === 'get_ai_help') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $apiKey = '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'; 
    
    $prompt = "The student failed this math question. Question: {$input['question']}. Correct Answer: {$input['correctAnswer']}. Standard Explanation: {$input['explanation']}. Please explain, step-by-step, the logic to arrive at the correct answer and identify the fundamental topic the student must know.";

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]));
    echo curl_exec($ch);
    curl_close($ch);
    exit();
}
?>
