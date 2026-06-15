<?php
// 1. Setup
$jsonFile = __DIR__ . '/english2025.json';
$action = $_GET['action'] ?? null;

// 2. Handle API actions (If an action is requested, process it and stop)
if ($action) {
    header('Content-Type: application/json');
    
    // Load and decode JSON
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    // Extract the questions array from your specific structure
    $fullData = $data['WAEC_English_Language_Objective_Questions']['questions'] ?? [];

    if ($action === 'get_questions') {
        $questions = [];
        foreach ($fullData as $q) {
            $questions[] = [
                'questionId' => $q['id'],
                'question' => ($q['instruction'] ?? '') . ' ' . ($q['question'] ?? ''),
                'sectionName' => $q['section'] ?? 'English Language',
                'options' => array_map(fn($id, $text) => ['optionId' => $id, 'text' => $text], array_keys($q['options']), $q['options']),
                'image' => $q['image'] ?? null
            ];
        }
        echo json_encode(['success' => true, 'questions' => $questions]);
    } 
    elseif ($action === 'submit') {
        $input = json_decode(file_get_contents('php://input'), true);
        $userAnswers = $input['answers'] ?? [];
        $score = 0;
        foreach ($fullData as $q) {
            if (isset($userAnswers[$q['id']]) && $userAnswers[$q['id']] === $q['answer']) {
                $score++;
            }
        }
        echo json_encode(['score' => $score, 'total' => count($fullData), 'percentage' => round(($score / count($fullData)) * 100, 2)]);
    } 
    elseif ($action === 'get_explanations') {
        $exps = [];
        foreach ($fullData as $q) {
            $exps[] = [
                'questionId' => $q['id'],
                'question' => $q['question'],
                'correctAnswer' => $q['answer'],
                'explanation' => $q['explanation'] ?? 'No explanation provided.',
                'options' => array_map(fn($id, $text) => ['optionId' => $id, 'text' => $text], array_keys($q['options']), $q['options'])
            ];
        }
        echo json_encode(['success' => true, 'questions' => $exps]);
    }
    // AI Logic handled by your ai_logic.php include
    exit(); 
}
?>
<?php include 'ai_logic.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — WAEC English CBT Demo (2025 Exam)</title>
    <script src="exam_engine.js"></script>
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



<?php include 'footer2.php'; ?>
</body>
</html>
