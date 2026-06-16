<?php
// ai_logic.php - CLEAN AI ENDPOINT ONLY

if (isset($_GET['action']) && $_GET['action'] === 'get_ai_help') {

    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    $apiKey = 'YOUR_GROQ_API_KEY_HERE'; // ⚠️ move to env later

    $prompt = "Explain this WAEC question step by step:

Question: {$input['question']}
Correct Answer: {$input['correctAnswer']}
Explanation: {$input['explanation']}

Teach the student clearly and briefly.";

    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
    exit();
}
