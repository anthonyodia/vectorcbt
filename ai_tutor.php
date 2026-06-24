<?php
// ai_tutor.php
// Shared AI Tutor Engine for all CBT pages

if (!isset($action)) {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
}

/* ================= AI TUTOR ROUTE ================= */
if ($action === 'get_ai_help') {

    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    $apiKey = getenv('GROQ_API_KEY') ?: 'YOUR_GROQ_API_KEY';

    $question = $input['question'] ?? '';
    $correctAnswer = $input['correctAnswer'] ?? '';
    $explanation = $input['explanation'] ?? '';

    $prompt = "
You are a WAEC CBT expert tutor.

Question:
$question

Correct Answer:
$correctAnswer

Standard Explanation:
$explanation

Explain step-by-step in simple terms so a secondary school student understands.
";

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
?>
