<?php
// Mathematics 2022 CBT - Standalone PHP Page
// This file handles both loading and serving the CBT

// CRITICAL: Define the path to the JSON data file.
$jsonFile = __DIR__ . '/maths2022.json'; // *** UPDATED FILENAME HERE ***

// --- Core Function to Load and Validate JSON Data ---
function loadJsonData($file) {
    if (!file_exists($file)) {
        return ['error' => 'Question file (' . basename($file) . ') not found'];
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = json_last_error_msg();
        return ['error' => "JSON Decoding Error: **{$errorMsg}**. Check for malformed JSON in your file."];
    }
    
    // Check if data is an array (the assumed structure from the previous successful loading)
    if (is_array($data) && isset($data[0]['id'])) {
           $questionsArray = $data;
    } elseif (isset($data['questions']) && is_array($data['questions'])) {
        // Fallback for previous suggestion of wrapping in "questions" key
        $questionsArray = $data['questions'];
    } else {
        // Assuming the file content is the array itself if other checks fail but content looks like JSON
        // Re-decoding raw content as a fallback to ensure we get an array if the user used the minimal array structure
        $rawArray = json_decode(trim($jsonContent), true);
        if (is_array($rawArray) && isset($rawArray[0]['id'])) {
            $questionsArray = $rawArray;
        } else {
            return ['error' => 'JSON format is invalid. Ensure it contains a root array of question objects, each with an "id" key.'];
        }
    }
    
    if (empty($questionsArray)) {
        return ['error' => 'No questions found inside the JSON data. The array is empty.'];
    }

    // Standardize structure for the CBT logic, using defaults for section info
    $standardizedQuestions = [];
    foreach ($questionsArray as $q) {
        $q['sectionName'] = $q['sectionName'] ?? "Core Mathematics"; // Default section name
        $q['sectionId'] = $q['sectionId'] ?? "MATH"; // Default section ID
        $standardizedQuestions[] = $q;
    }
    
    return ['questions' => $standardizedQuestions];
}
// ---------------------------------------------------

// --- PHP HTML Escaping Function (kept for security on user-controlled text like sectionName) ---
function safeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
// ---------------------------------------------------

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$jsonResult = loadJsonData($jsonFile);
$allQuestions = $jsonResult['questions'] ?? [];

// If there was an error loading the JSON, stop here and display the error immediately if an action is requested
if (isset($jsonResult['error']) && $action) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $jsonResult['error']]);
    exit();
}


// --- ACTION 1: Get Questions for the Exam View ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    // Flatten questions, but remove answers/explanations for exam security
    $questions = [];
    foreach ($allQuestions as $question) {
        // Convert the old key format to the new required format for JS:
        $q = [
            'questionId' => $question['id'], // Map 'id' to 'questionId'
            'question' => $question['question'],
            'image' => $question['image'] ?? null,
            'sectionName' => $question['sectionName'],
            'sectionId' => $question['sectionId'],
            'options' => []
        ];
        
        // Convert the flat options object into an array of objects for the JS logic
        foreach ($question['options'] as $optionId => $text) {
            $q['options'][] = ['optionId' => $optionId, 'text' => $text];
        }
        
        $questions[] = $q;
    }
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get All Question Details for Explanation View ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    
    // Prepare questions with all details
    $questionsWithDetails = [];
    foreach ($allQuestions as $question) {
        // Convert the old key format to the new required format for JS:
        $q = [
            'questionId' => $question['id'], // Map 'id' to 'questionId'
            'question' => $question['question'],
            'image' => $question['image'] ?? null,
            'sectionName' => $question['sectionName'],
            'sectionId' => $question['sectionId'],
            'correctAnswer' => $question['answer'], // Map 'answer' to 'correctAnswer'
            'explanation' => $question['explanation'] ?? '',
            'options' => []
        ];

        // Convert the flat options object into an array of objects for the JS logic
        foreach ($question['options'] as $optionId => $text) {
            $q['options'][] = ['optionId' => $optionId, 'text' => $text];
        }
        
        $questionsWithDetails[] = $q;
    }
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? []; // Array of user answers keyed by question *number* (1, 2, 3...)
    
    // Create question map using array index (1-based) to easily look up the correct answer
    // Note: The JS sends answers keyed by the question *number* (1-indexed array position).
    $questionMap = [];
    foreach ($allQuestions as $index => $question) {
        $questionMap[$index + 1] = $question; // Use 1-based index as the key
    }
    
    $score = 0;
    $total = count($questionMap);
    
    foreach ($answers as $qNum => $answer) {
        $qNum = intval($qNum);
        // Look up the question object using the 1-based number
        if (isset($questionMap[$qNum]) && $answer === $questionMap[$qNum]['answer']) {
            $score++;
        }
    }
    
    $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => $percentage
    ]);
    exit();
}


// If no action, show the HTML page (The CBT Interface)

// Check if the HTML display needs to show an immediate JSON loading error
$initialError = isset($jsonResult['error']) ? $jsonResult['error'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Mathematics CBT 2022</title>     <style>
        /* --- UI/UX STYLES (MATCHING CRK EXAMPLE) --- */
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #fefdfc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 40px auto;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
            padding-bottom: 20px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            background: #f7f7f7;
            font-size: 15px;
            border-bottom: 1px solid #eaeaea;
            border-radius: 40px;
            margin: 20px auto;
            width: 90%;
        }

        .steps span {
            flex: 1;
            text-align: center;
            padding: 6px;
            color: #aaa;
        }

        .steps .active {
            color: #007aff;
            font-weight: 600;
        }

        .title {
            text-align: center;
            margin-top: 10px;
        }

        .title h1 {
            font-size: 30px;
            margin: 0;
            color: #1e2a3a;
        }

        .title p {
            margin: 8px 0 22px 0;
            font-size: 15px;
            color: #555;
        }

        .form-box {
            background: linear-gradient(90deg, #4facfe, #43e97b);
            color: white;
            text-align: center;
            padding: 18px 15px;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 25px 30px 25px;
            border-radius: 12px;
        }

        .question-container {
            margin: 0 25px 25px 25px;
            padding: 20px;
            border: 1px solid #e6eaf0;
            border-radius: 12px;
            background: #fafafa;
            min-height: 300px;
        }
        
        /* Style for Explanation Container Questions */
        .explanation-view .question-container {
            background: #ffffff;
            border: 1px solid #cceeff;
            margin-bottom: 20px;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .question-id {
            font-size: 14px;
            color: #007aff;
            font-weight: 600;
        }

        .question-section {
            font-size: 12px;
            color: #999;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .question-text {
            font-size: 17px;
            font-weight: 600;
            color: #243246;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        label.option-label {
            display: block;
            font-size: 15px;
            color: #1e2a3a;
            margin-bottom: 12px;
            cursor: pointer;
            user-select: none;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        /* Hide radio buttons in explanation view */
        .explanation-view input[type="radio"] {
            display: none;
        }
        
        .explanation-view label.option-label {
            cursor: default;
        }
        
        .explanation-view label.option-label:hover {
            background: initial;
            border-color: #e0e0e0;
        }

        label.option-label:hover {
            background: #f0f7ff;
            border-color: #007aff;
        }

        label.option-label input[type="radio"]:checked + span {
            color: #007aff;
            font-weight: 600;
        }

        label.option-label input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
            accent-color: #007aff;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin: 30px 25px;
            gap: 10px;
        }

        .nav-btn {
            background: #43e97b;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex: 1;
            justify-content: center;
        }
        
        #btn-submit {
            background: #ff6b6b; /* Reddish for submit */
        }
        
        #btn-submit:hover {
            background: #e05c5c;
        }

        .nav-btn:hover:not(.disabled) {
            background: #38c172;
        }

        .nav-btn.disabled {
            background: #b2d6be;
            cursor: default;
            pointer-events: none;
        }

        .question-nav {
            text-align: center;
            margin: 30px 0 40px 0;
            padding: 0 25px;
            overflow-x: auto;
        }

        .question-nav a {
            display: inline-block;
            margin: 0 4px;
            min-width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 50%;
            background: #f7f7f7;
            color: #007aff;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            user-select: none;
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
        }

        .question-nav a.active {
            background: #007aff;
            color: white;
        }

        .question-nav a:hover {
            background: #e6f0ff;
        }

        .question-nav a.answered {
            background: #4caf50;
            color: white;
        }

        .section-divider {
            margin: 30px 25px 0 25px;
            padding: 15px 20px;
            background: #f0f0f0;
            border-left: 4px solid #007aff;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #243246;
        }
        
        .explanation-btn {
            background: #007aff;
            margin-right: 10px;
        }
        
        .explanation-btn:hover {
            background: #005bb5;
        }
        
        .home-btn {
            background: #6c757d;
            margin-right: 10px;
        }

        .home-btn:hover {
            background: #5a6268;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            margin: 20px 25px;
            border-radius: 12px;
            border-left: 4px solid #c62828;
            font-weight: 600;
        }
        
        .error code {
            display: block;
            margin-top: 10px;
            padding: 10px;
            background: #fce4e4;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            font-weight: normal;
            color: #c62828;
        }
        
        /* Styles for Explanation Highlighting */
        .correct-answer-label {
            border: 2px solid #4CAF50 !important;
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }
        
        .user-answer-label {
            border: 2px solid #FFC107 !important;
            background-color: #fff8e1 !important;
            font-weight: bold;
        }

        .explanation-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #e3f2fd;
            border-left: 5px solid #2196F3;
        }

        .explanation-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .nav-button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
            }
            .nav-button-group .nav-btn {
                flex: 0 0 auto;
            }
        }
        /* --- UI/UX STYLES END --- */
    </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — Mathematics 2022</h1>            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <?php if ($initialError): ?>
        <div class="question-container" id="q-container">
            <div class="error">
                **CRITICAL JSON ERROR:** The quiz cannot load.
                <p><?php echo $initialError; ?></p>
                <p>Please check your <code>maths2022.json</code> file for syntax issues.</p>            </div>
        </div>
        <?php else: ?>

        <div id="section-display"></div>

        <div class="question-container" id="q-container">
            <p class="loading">Loading questions...</p>
        </div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
            <button class="nav-btn" id="btn-next" disabled>Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
        
        <?php endif; ?>
    </div>

<script>
    // Only run JavaScript if there was no PHP JSON error
    <?php if (!$initialError): ?>
    
    // Get the base URL (e.g., http://example.com/quiz.php) without query parameters
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    
    // Get the path of the current script (e.g., /folder/quiz.php)
    const scriptPath = window.location.pathname;
    // Calculate the home URL by replacing the script name (e.g., quiz.php) with index.php
    const homeUrl = scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1) + 'index.php';

    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1; // Current question number (1-based index)
    let duration = parseInt(urlParams.get('duration')) || 60; // Default duration in minutes

    let allQuestions = [];
    let answers = {}; // Stores answers keyed by question *number* (1-based index)
    let currentSection = null;
    let explanationData = null; 
    let timerInterval;

    // --- Initial Loading ---
    async function loadQuestions() {
        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer();
            } else if (data.error) {
                showError('Failed to load questions:', data.error);
            } else {
                showError('Failed to load questions:', 'No questions found.');
            }
        } catch (error) {
            showError('Error loading questions:', 'Network or server error.');
        }
    }

    function showError(title, message) {
        document.getElementById('q-container').innerHTML = `<div class="error">
            <strong>${title}</strong>
            <code>${message}</code>
        </div>`;
    }

    // --- Rendering Question ---
    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // Section divider logic
        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Section: ' + safeHtml(qObj.sectionName) + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Q' + qObj.questionId + '</span>'; 
        html += '<span class="question-section">' + safeHtml(qObj.sectionName) + '</span>';
        html += '</div>';
        
        // **IMPORTANT:** We still escape the image URL for security but the image is displayed directly.
        if (qObj.image) {
              html += `<div style="text-align: center; margin-bottom: 20px;">
                            <img src="${safeHtml(qObj.image)}" alt="Question Image" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc;">
                        </div>`;
        }
        
        // *** FIX APPLIED HERE: Remove htmlEscape() around qObj.question ***
        html += '<div class="question-text">' + qObj.question + '</div>';
        html += '<form id="qForm">';
        
        // Options rendering (using the array format provided by the PHP logic)
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            // *** FIX APPLIED HERE: Remove htmlEscape() around opt.text ***
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            if (answers[current] === opt.optionId) html += 'checked'; 
            html += ' />';
            html += '<span>' + opt.optionId + '. ' + opt.text + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        updateNavButtons();
    }

    // --- Rendering Navigation Buttons (1, 2, 3...) ---
    function renderNav() {
        let html = '';
        for (let i = 1; i <= allQuestions.length; i++) {
            const answered = answers[i] ? 'answered' : '';
            const active = i === current ? 'active' : '';
            html += '<a href="#" onclick="navigate(' + i + '); return false;" class="' + active + ' ' + answered + '">' + i + '</a>';
        }
        document.getElementById('q-nav').innerHTML = html;
    }
    
    // --- Dynamic Button Logic ---
    function updateNavButtons() {
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const nav = document.getElementById('nav-buttons');
        let existingSubmit = document.getElementById('btn-submit');

        btnPrev.disabled = (current <= 1);
        
        if (current === allQuestions.length) {
            btnNext.style.display = 'none';

            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.onclick = submitAnswers; // Attach handler
                nav.appendChild(submitBtn);
            } else {
                existingSubmit.style.display = 'inline-flex';
            }
        } else {
            btnNext.style.display = 'inline-flex';
            btnNext.disabled = false;
            if (existingSubmit) {
                existingSubmit.remove(); // Remove submit button if not on last question
            }
        }
    }

    // --- Navigation & Answer Saving ---
    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }
    
    // Event handlers for Prev/Next
    document.getElementById('btn-prev').addEventListener('click', () => {
        saveAnswer();
        if (current > 1) {
            current--;
            renderQuestion();
            renderNav();
        }
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        saveAnswer();
        if (current < allQuestions.length) {
            current++;
            renderQuestion();
            renderNav();
        }
    });

    // Event listener for option selection to save answer immediately
    document.getElementById('q-container').addEventListener('change', function(event) {
        if (event.target.type === 'radio' && event.target.name === 'answer') {
            saveAnswer();
            renderNav(); // Re-render nav dots to show answered status
        }
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form || !form.answer) return;
        const selected = form.answer.value || null;
        
        // Save answer keyed by question number (1-based index)
        if (selected) answers[current] = selected; 
    }

    // **UPDATED:** Rename the old htmlEscape to safeHtml to reflect its security purpose 
    // and keep it separate from content interpretation (which is now done directly).
    function safeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // --- Timer Logic ---
    let totalSeconds = duration * 60;

    function updateTimer() {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        document.getElementById('countdown').textContent = str;
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            submitAnswers();
        } else {
            totalSeconds--;
        }
    }

    function startTimer() {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    // --- Submission & Results Logic (Confirmation Removed) ---
    async function submitAnswers() {
        saveAnswer();
        if(timerInterval) clearInterval(timerInterval);

        // Confirmation box removed as requested. Submission is immediate.

        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers: answers }) 
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                showError('Submission failed:', (result.error || 'Unknown error.'));
            }
        } catch (error) {
            showError('Error submitting answers:', error.message);
        }
    }

    function goToHome() { window.location.href = homeUrl; }
    function retakeExam() { window.location.href = baseUrl; }

    async function viewExplanations() {
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading">Loading explanations...</p>';
        
        if (!explanationData) {
              try {
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_explanations');
                const data = await response.json();
                
                if (data.success) {
                    explanationData = data.questions;
                    displayExplanations();
                } else {
                    explanationContainer.innerHTML = '<div class="error">Failed to load explanation data.</div>';
                }
            } catch (error) {
                explanationContainer.innerHTML = '<div class="error">Error loading explanations: ' + error.message + '</div>';
            }
        } else {
            displayExplanations();
        }
    }

    function displayExplanations() {
        const explanationContainer = document.getElementById('explanation-container');
        let html = '<div class="explanation-view">';

        explanationData.forEach((qObj, index) => {
            const questionNumber = index + 1; // 1-based index is needed for looking up user answer
            const userAnswer = answers[questionNumber]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + safeHtml(qObj.sectionName) + '</span>';
            html += '</div>';
            
            // Image display
            if (qObj.image) {
                 html += `<div style="text-align: center; margin-bottom: 20px;">
                            <img src="${safeHtml(qObj.image)}" alt="Question Image" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc;">
                        </div>`;
            }
            
            // *** FIX APPLIED HERE: Remove htmlEscape() around qObj.question ***
            html += '<div class="question-text">' + qObj.question + '</div>';
            
            // Options and highlighting
            qObj.options.forEach(opt => {
                let labelClass = '';
                if (opt.optionId === qObj.correctAnswer) {
                    labelClass = 'correct-answer-label';
                } else if (opt.optionId === userAnswer && opt.optionId !== qObj.correctAnswer) {
                    labelClass = 'user-answer-label';
                }

                html += '<label class="option-label ' + labelClass + '">';
                // *** FIX APPLIED HERE: Remove htmlEscape() around opt.text ***
                html += '<span>' + opt.optionId + '. ' + opt.text + '</span>';
                html += '</label>';
            });

            // Explanation box
            // Note: The explanation text might also contain math symbols, so we avoid escaping here as well.
            if (qObj.explanation) {
                html += '<div class="explanation-box"><strong>Explanation:</strong> ' + qObj.explanation + '</div>';
            }

            html += '</div>';
        });

        html += '</div>';
        
        // --- Navigation Group for Explanation View ---
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        html += '<button class="nav-btn" onclick="retakeExam()" style="width: auto;">🔄 Retake Exam</button>';
        html += '</div>';
        // ---------------------------------------------
        
        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }
    
    function displayResults(result) {
        document.querySelector('.question-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const container = document.querySelector('.container');
        let resultBox = document.getElementById('results-view');
        
        if (resultBox) resultBox.remove();
        
        resultBox = document.createElement('div');
        resultBox.className = 'question-container';
        resultBox.id = 'results-view'; 
        resultBox.style.textAlign = 'center';
        resultBox.innerHTML = '<div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Results</div>';
        resultBox.innerHTML += '<div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">' + result.score + '/' + result.total + '</div>';
        resultBox.innerHTML += '<div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">' + result.percentage + '%</div>';
        resultBox.innerHTML += '<div style="font-size: 16px; color: #555; margin-bottom: 30px;">You answered ' + result.score + ' out of ' + result.total + ' questions correctly.</div>';
        
        // --- Navigation Group for Results View ---
        resultBox.innerHTML += '<div class="nav-button-group">';
        resultBox.innerHTML += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        resultBox.innerHTML += '<button class="nav-btn explanation-btn" onclick="viewExplanations()">View Explanation</button>';
        resultBox.innerHTML += '<button class="nav-btn" onclick="retakeExam()">🔄 Retake Exam</button>';
        resultBox.innerHTML += '</div>';
        // ----------------------------------------
        
        container.appendChild(resultBox);
    }

    // Initialize application
    loadQuestions();
    
    <?php endif; ?>
</script>



<?php include 'footer.php'; ?></body>
</html>