<?php include 'ai_logic.php'; ?>
<?php
// Set the path to your new JSON file
// *** CRITICAL CHANGE: Updated file name to English2025.json ***
$jsonFile = __DIR__ . '/english2025.json';
$action = $_GET['action'] ?? null;

// --- PHP Action: Get Questions (Loads ALL questions) ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . $jsonFile]);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $decodedData = json_decode($jsonContent, true); 
    
    $fullData = [];
    $subjectTitle = 'English Language';
    
    // *** ROBUST PARSING LOGIC: Handles the nested structure of English2024.json ***
    if (isset($decodedData['WAEC_English_Language_Objective_Questions']['questions']) && 
        is_array($decodedData['WAEC_English_Language_Objective_Questions']['questions'])) {
            
        $fullData = $decodedData['WAEC_English_Language_Objective_Questions']['questions'];
        $subjectTitle = $decodedData['WAEC_English_Language_Objective_Questions']['title'] ?? $subjectTitle;
        
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON structure. Expected a top-level "WAEC_English_Language_Objective_Questions" object containing a "questions" array.']);
        exit();
    }
    
    if (empty($fullData)) {
        http_response_code(500);
        echo json_encode(['error' => 'The question array is empty.']);
        exit();
    }
    
    $questions = [];
    $sectionIdCounter = 1; // Used to uniquely track sections if necessary, though sectionName is more useful
    $processedSections = [];

    foreach ($fullData as $questionData) {
        $sectionName = $questionData['section'] ?? $subjectTitle;
        
        // Ensure sectionId is consistent if the section name is repeated
        if (!isset($processedSections[$sectionName])) {
            $processedSections[$sectionName] = $sectionIdCounter++;
        }

        $question = [
            // *** CRITICAL CHANGE: Using 'id' field from JSON ***
            'questionId' => $questionData['id'] ?? null, 
            'instruction' => $questionData['instruction'] ?? '', 
            // Combine instruction (which often comes from the main JSON object) and question text
            'question' => (isset($questionData['instruction']) ? $questionData['instruction'] . ' ' : '') . ($questionData['question'] ?? ''),
            'options' => [],
            // *** CRITICAL CHANGE: Using 'section' field from JSON ***
            'sectionName' => $sectionName, 
            'sectionId' => $processedSections[$sectionName],
            'answer' => $questionData['answer'] ?? '',
            'explanation' => $questionData['explanation'] ?? 'No explanation provided.',
            'imageUrl' => $questionData['image'] ?? null, 
        ];

        // Process options from the associative array
        foreach ($questionData['options'] as $optionId => $optionText) {
            $question['options'][] = [
                'optionId' => $optionId, 
                'text' => $optionText
            ];
        }
        $questions[] = $question;
    }
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// If no action, show the HTML page (The CBT Interface)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — WAEC English CBT Demo (2025 Exam)</title>
    <style>
        /* --- FULL UI/UX STYLES (Unchanged) --- */
        
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; padding: 6px; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; margin: 0; color: #1e2a3a; }
        .title p { margin: 8px 0 22px 0; font-size: 15px; color: #555; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 20px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 300px; }
        .explanation-view .question-container { background: #ffffff; border: 1px solid #cceeff; margin-bottom: 20px; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .question-id { font-size: 14px; color: #007aff; font-weight: 600; }
        .question-section { font-size: 12px; color: #999; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; }
        .question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        label.option-label input[type="radio"]:checked + span { color: #007aff; font-weight: 600; }
        label.option-label input[type="radio"] { margin-right: 10px; cursor: pointer; accent-color: #007aff; }
        .explanation-view input[type="radio"] { display: none; }
        .explanation-view label.option-label { cursor: default; }
        .explanation-view label.option-label:hover { background: initial; border-color: #e0e0e0; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .explanation-box p { margin: 0; font-size: 14px; line-height: 1.6; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:hover:not(.disabled) { background: #38c172; }
        .nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin-top: 30px; margin-bottom: 30px; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: auto; }
        .question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; user-select: none; transition: background-color 0.3s, color 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a:hover { background: #e6f0ff; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600; color: #243246; }
        .loading { text-align: center; padding: 40px; font-size: 18px; color: #666; }
        .error { background: #ffebee; color: #c62828; padding: 20px; margin: 20px 25px; border-radius: 12px; border-left: 4px solid #c62828; }
        .question-image { max-width: 100%; height: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e0e0e0; }
        .export-btn { background: #6f42c1 !important; }
        @media (max-width: 768px) { .container { margin: 20px; } .nav-button-group .nav-btn { flex: 0 0 auto; } }
    </style>
</head>
<body>

<?php include 'header.php'; ?>


    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — WAEC English</h1>
            <p>Test your knowledge with these questions.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>
        
        <div id="section-display">
            </div>

        <div class="question-container" role="main" aria-live="polite" id="q-container">
            <p class="loading">Loading questions...</p>
        </div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev">← Previous</button>
            <button class="nav-btn" id="btn-next">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script src="exam_engine.js"></script>

<?php include 'footer2.php'; ?>
</body>
</html>
