<?php
// economics2022.php - Vector Learn CBT Page (Server-Side Logic)

// Define the JSON file name
$jsonFile = __DIR__ . '/economics2022.json'; // CHANGED: Filename updated (no gap)

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- Helper Functions to Process JSON ---
function flattenQuestions($data, $includeAnswers = false) {
    $questions = [];
    
    // Check for the 'question_set' key (assuming the question array is nested)
    $source_questions = $data['question_set'] ?? $data['questions'] ?? $data; 
    
    // Ensure $source_questions is an array
    if (!is_array($source_questions)) {
        return [];
    }

    foreach ($source_questions as $question) {
        
        // Use 'question_number' 
        $qId = $question['question_number'] ?? $question['questionId'];

        $options = [];
        $options_raw = $question['options'] ?? []; 
        
        // *** FIX 1: Handle options as an array of objects with 'label' and 'value' keys ***
        if (is_array($options_raw)) {
             foreach ($options_raw as $opt) {
                 if (isset($opt['label']) && isset($opt['value'])) {
                     $options[] = [
                         'optionId' => $opt['label'],
                         'text' => $opt['value']
                     ];
                 }
             }
        }
        
        // *** FIX 2: Handle data_table as an array of objects with 'value' key ***
        $data_table_structured = null;
        if (isset($question['data_table']) && is_array($question['data_table'])) {
            $table_items = [];
            foreach ($question['data_table'] as $item) {
                // Convert 'value' to 'amount' for display compatibility in HTML template
                $table_items[] = [
                   'item' => $item['item'] ?? '',
                   'amount' => $item['value'] ?? $item['amount'] ?? '', // Prioritize 'value'
                   'type' => $item['type'] ?? ''
                ];
            }
            // Re-wrap the items array into the expected structure for the JS rendering logic
            $data_table_structured = [
                'title' => $question['context'] ?? 'Data Table',
                'items' => $table_items
            ];
        }


        $q = [
            'questionId' => $qId, 
            'sectionName' => $data['subject'] ?? ($question['sectionName'] ?? 'General Subject'),
            'sectionId' => $question['sectionId'] ?? 1,
            'question' => $question['question'],
            'instruction' => $question['instruction'] ?? null,
            'data_table' => $data_table_structured,
            'options' => $options, 
        ];

        if ($includeAnswers) {
            $correctAnswerKey = isset($question['answer']) ? 'answer' : 'correctAnswer';
            $q['correctAnswer'] = isset($question[$correctAnswerKey]) ? (string)$question[$correctAnswerKey] : null;
            $q['explanation'] = $question['explanation'] ?? 'No explanation provided.';
        }
        $questions[] = $q;
    }
    return $questions;
}

// --- ACTION 1: Get Questions for the Exam View (No Answers) ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        // CHANGED: File reference updated (no gap)
        echo json_encode(['error' => 'Question file not found at: ' . $jsonFile]);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty file']);
        exit();
    }
    
    $questions = flattenQuestions($data, false);
    
    echo json_encode([
        'success' => true,
        // CHANGED: Default Exam/Subject Name updated
        'examName' => $data['exam'] ?? 'Economics CBT',
        'subjectName' => $data['subject'] ?? 'Economics 2022',
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get All Question Details for Explanation View (Includes Answers) ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format']);
        exit();
    }

    $questionsWithDetails = flattenQuestions($data, true);
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = file_get_contents('php://input');
    $data_in = json_decode($input, true);

    $answers = $data_in['answers'] ?? [];
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    $questionMap = [];
    foreach (flattenQuestions($data, true) as $question) {
        // Use the actual question ID (question_number) as the map key
        $questionMap[(int)$question['questionId']] = $question;
    }
    
    $score = 0;
    $total = count($questionMap);
    
    // --- SCORING LOOP ---
    foreach ($answers as $qId => $answer) {
        $qId = (int)$qId; // Cast QID to integer for lookup
        $answer = (string)$answer; // Ensure answer is a string
        
        if (isset($questionMap[$qId]) && $answer == $questionMap[$qId]['correctAnswer']) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Economics 2022 CBT</title> <style>
        /* CSS omitted for brevity */
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
        
        .question-instruction {
            font-size: 14px;
            font-style: italic;
            color: #777;
            margin-bottom: 10px;
        }

        /* Table Styling for Data */
        .question-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .question-data-table th, .question-data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .question-data-table th {
            background: #f0f0f0;
            font-weight: 600;
        }
        /* End Table Styling */


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
        }
        
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
            <h1>Vector Learn — <span id="exam-title">Economics 2022</span></h1>
            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

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
    </div>

<script>
    // --- START: Absolute URL Handling (Most Robust) ---
    let scriptBaseUrl = window.location.href;
    // Remove any existing query parameters to get a clean base URL for AJAX calls
    scriptBaseUrl = scriptBaseUrl.split('?')[0]; 

    // The home URL is assumed to be index.php in the same directory
    const homeUrl = scriptBaseUrl.substring(0, scriptBaseUrl.lastIndexOf('/') + 1) + 'index.php'; 
    // --- END: Absolute URL Handling ---

    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60; 
    
    let allQuestions = [];
    let answers = {}; 
    let currentSection = null;
    let explanationData = null; 
    let totalQuestions = 0;


    async function loadQuestions() {
        try {
            // Note: The script uses its own filename (e.g., economics2022.php) for AJAX calls
            const url = scriptBaseUrl + '?action=get_questions';
            const response = await fetch(url); 
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                totalQuestions = data.questions.length;
                document.getElementById('exam-title').textContent = data.subjectName; 
                renderQuestion();
                renderNav();
                startTimer();
            } else {
                // CHANGED: File reference updated in error message (no gap)
                showError('Failed to load questions. Check that **economics2022.json** exists, is valid, and contains questions. (Server responded: ' + JSON.stringify(data) + ')');
            }
        } catch (error) {
            console.error('AJAX Load Error:', error);
            showError('CRITICAL Network Error: Failed to complete AJAX request. Check your browser console for security/network errors.');
        }
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Subject: ' + htmlEscape(qObj.sectionName) + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Q' + qObj.questionId + '</span>';
        html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
        html += '</div>';
        
        if (qObj.instruction) {
             html += '<div class="question-instruction">' + htmlEscape(qObj.instruction) + '</div>';
        }
        
        // Render the Data Table if present 
        if (qObj.data_table && qObj.data_table.items) {
            html += '<table class="question-data-table">';
            // Use qObj.data_table.title as the header
            html += `<tr><th colspan="2">${htmlEscape(qObj.data_table.title || 'Data Table')}</th></tr>`;
            qObj.data_table.items.forEach(item => {
                // The PHP logic converted 'value' back to 'amount' for this part of the script
                html += `<tr>
                    <td>${htmlEscape(item.item || item.partner || '')}</td>
                    <td>${htmlEscape(item.amount || '')}</td>
                </tr>`;
            });
            html += '</table>';
        }
        
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => { 
            const lookupId = qObj.questionId; 
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            
            // Check against the actual questionId key
            if (answers[lookupId] === opt.optionId) html += 'checked';
            
            html += ' />';
            html += '<span>' + htmlEscape(opt.optionId) + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = (current >= totalQuestions);

        const nav = document.getElementById('nav-buttons');
        const existingSubmit = document.getElementById('btn-submit');
        
        if (current === totalQuestions) {
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                nav.appendChild(submitBtn);
            }
            btnNext.style.display = 'none';
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
        }
    }

    function renderNav() {
        let html = '';
        for (let i = 1; i <= totalQuestions; i++) {
            const qId = allQuestions[i-1].questionId;
            // Lookup answered state using the actual question ID
            const answered = answers[qId] ? 'answered' : '';
            const active = i === current ? 'active' : '';
            html += '<a href="#" onclick="navigate(' + i + '); return false;" class="' + active + ' ' + answered + '">' + i + '</a>';
        }
        document.getElementById('q-nav').innerHTML = html;
    }
    
    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

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
        if (current < totalQuestions) {
            current++;
            renderQuestion();
            renderNav();
        }
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form) return;
        const selected = form.answer.value || null;
        
        // Store the answer using the questionId as the key
        if (selected) {
             const qObj = allQuestions[current - 1]; 
             const qId = qObj.questionId;          
             answers[qId] = selected;
        }
    }

    let timerInterval;

    function updateTimer() {
        // Calculate total seconds based on the duration parameter (default 60 minutes)
        let totalSeconds = duration * 60; 
        const countdownElement = document.getElementById('countdown');

        // Check if a timer has already started
        if (countdownElement.dataset.startTime) {
            // Calculate remaining seconds if restarting after a refresh/navigation
            const elapsed = Math.floor((Date.now() - parseInt(countdownElement.dataset.startTime)) / 1000);
            totalSeconds = totalSeconds - elapsed;
        } else {
            // Set start time for the first time
            countdownElement.dataset.startTime = Date.now();
        }

        if (totalSeconds <= 0) {
            countdownElement.textContent = '00:00';
            submitAnswers();
            return;
        }

        timerInterval = setInterval(() => {
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                countdownElement.textContent = '00:00';
                submitAnswers();
                return;
            }
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            countdownElement.textContent = str;
            totalSeconds--;
        }, 1000);
    }

    function startTimer() {
        updateTimer();
    }

    async function submitAnswers() {
        saveAnswer();
        if(timerInterval) clearInterval(timerInterval);

        // Display loading
        document.getElementById('q-container').innerHTML = '<p class="loading">Submitting and calculating score...</p>';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';

        try {
            const url = scriptBaseUrl + '?action=submit';
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers: answers })
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                showError('Submission failed: ' + (result.error || 'Server error.'));
            }
        } catch (error) {
            showError('Error submitting answers: ' + error.message);
        }
    }

    function displayResults(result) {
        const percentage = result.percentage.toFixed(2);
        const passed = percentage >= 50; // Simple pass/fail based on 50%
        const message = passed ? 'Congratulations! You passed.' : 'Keep practicing! You did not pass.';
        const color = passed ? '#4CAF50' : '#f44336';

        let html = `
            <div id="results-view" style="text-align: center; padding: 40px 25px;">
                <h2 style="color: #1e2a3a; margin-bottom: 20px;">Exam Complete</h2>
                <div style="background: ${color}; color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <h3>${message}</h3>
                    <p style="font-size: 18px; margin: 10px 0;">Your Score: <strong>${result.score}</strong> out of <strong>${result.total}</strong></p>
                    <p style="font-size: 24px; font-weight: bold;">${percentage}%</p>
                </div>
                
                <div class="nav-button-group">
                    <button class="nav-btn explanation-btn" onclick="viewExplanations()">View Explanations</button>
                    <button class="nav-btn" onclick="retakeExam()" style="background: #FF9800;">Retake Exam</button>
                    <button class="nav-btn home-btn" onclick="goToHome()">Go to Home</button>
                </div>
            </div>
        `;

        document.getElementById('q-container').innerHTML = html;
        document.getElementById('info-box').style.display = 'none';
    }


    function goToHome() {
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        window.location.href = scriptBaseUrl; 
    }

    async function viewExplanations() {
        // Hide result view and show loading in explanation container
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        document.getElementById('info-box').style.display = 'block';
        document.getElementById('q-container').style.display = 'none';

        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading">Loading explanations...</p>';
        
        if (!explanationData) {
             try {
                 const url = scriptBaseUrl + '?action=get_explanations';
                 const response = await fetch(url);
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

        explanationData.forEach((qObj) => {
            const userSelection = answers[qObj.questionId];
            const correctAnswer = qObj.correctAnswer;
            
            // Build the question card
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + '</span>';
            html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
            html += '</div>';

            if (qObj.instruction) {
                html += '<div class="question-instruction">' + htmlEscape(qObj.instruction) + '</div>';
            }

            // Render data table if available (same logic as renderQuestion)
            if (qObj.data_table && qObj.data_table.items) {
                html += '<table class="question-data-table">';
                html += `<tr><th colspan="2">${htmlEscape(qObj.data_table.title || 'Data Table')}</th></tr>`;
                qObj.data_table.items.forEach(item => {
                    html += `<tr>
                        <td>${htmlEscape(item.item || item.partner || '')}</td>
                        <td>${htmlEscape(item.amount || '')}</td>
                    </tr>`;
                });
                html += '</table>';
            }
            
            html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
            
            // Render options
            qObj.options.forEach(opt => {
                let labelClass = 'option-label';
                let isCorrect = (opt.optionId === correctAnswer);
                let isUserAnswer = (opt.optionId === userSelection);
                
                if (isCorrect) {
                    labelClass += ' correct-answer-label';
                } else if (isUserAnswer) {
                    labelClass += ' user-answer-label';
                }

                html += '<label class="' + labelClass + '">';
                html += '<span style="pointer-events: none;">' + htmlEscape(opt.optionId) + '. ' + htmlEscape(opt.text) + '</span>';
                html += '</label>';
            });

            // Explanation box
            if (qObj.explanation) {
                html += '<div class="explanation-box">';
                html += '<h4 style="color: #2196F3; margin-top: 0; margin-bottom: 5px; font-size: 15px;">Explanation</h4>';
                html += '<p>' + htmlEscape(qObj.explanation) + '</p>';
                html += '</div>';
            }

            html += '</div>'; // End question-container
        });

        html += '</div>'; // End explanation-view
        
        // Add button group to the bottom of the explanations
        html += `
            <div class="nav-button-group">
                <button class="nav-btn" onclick="retakeExam()" style="background: #FF9800;">Retake Exam</button>
                <button class="nav-btn home-btn" onclick="goToHome()">Go to Home</button>
            </div>
        `;


        explanationContainer.innerHTML = html;
    }

    // Load questions when the page is ready
    document.addEventListener('DOMContentLoaded', loadQuestions);
</script>


<?php include 'footer.php'; ?></body>
</html>