<?php
// Computer Science 2024 CBT - Standalone PHP Page
// This file handles both loading and serving the CBT

// *** CRITICAL CHANGE 1: Specify JSON file name ***
$jsonFile = __DIR__ . '/computer2024.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions for the Exam View (Removes answers/explanations) ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . $jsonFile]);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true); 
    
    if (!$data || !is_array($data)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty array received.']);
        exit();
    }
    
    $questions = [];
    $sectionName = 'Computer Science'; // Default section name

    // *** CORRECTED LOGIC for Array-based JSON ***
    foreach ($data as $questionData) {
        // Skip if it's not a valid question object
        if (!isset($questionData['id'], $questionData['question'], $questionData['options'])) {
            continue;
        }

        $question = [
            'questionId' => $questionData['id'],
            'question' => $questionData['question'],
            'options' => [],
            'sectionName' => $sectionName,
            'sectionId' => 1
        ];

        // Reformat associative options array (A: text, B: text) into indexed array
        foreach ($questionData['options'] as $optionId => $optionText) {
            $question['options'][] = [
                'optionId' => $optionId,
                'text' => $optionText
            ];
        }
        
        // Note: 'answer' and 'explanation' keys from the JSON are naturally excluded here 
        // because they were not explicitly included in the $question array.
        
        $questions[] = $question;
    }
    
    // Sort questions by ID after extraction
    usort($questions, function($a, $b) {
        return $a['questionId'] <=> $b['questionId'];
    });

    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get All Question Details for Explanation View (Includes answer/explanation) ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . $jsonFile]);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    if (!$data || !is_array($data)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty array received.']);
        exit();
    }

    $questionsWithDetails = [];
    $sectionName = 'Computer Science';

    // *** CORRECTED LOGIC for Array-based JSON ***
    foreach ($data as $questionData) {
        if (!isset($questionData['id'], $questionData['question'], $questionData['options'], $questionData['answer'])) {
            continue;
        }

        $question = [
            'questionId' => $questionData['id'],
            'question' => $questionData['question'],
            'options' => [],
            // Keys must match the JSON file keys
            'correctAnswer' => $questionData['answer'], 
            'explanation' => $questionData['explanation'] ?? '', // Use null coalescing for optional explanation
            'sectionName' => $sectionName,
            'sectionId' => 1
        ];

        // Reformat associative options array into indexed array
        foreach ($questionData['options'] as $optionId => $optionText) {
            $question['options'][] = [
                'optionId' => $optionId,
                'text' => $optionText
            ];
        }
        
        $questionsWithDetails[] = $question;
    }
    
    // Sort questions by ID after extraction
    usort($questionsWithDetails, function($a, $b) {
        return $a['questionId'] <=> $b['questionId'];
    });
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (!$data || !is_array($data)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty array received.']);
        exit();
    }
    
    // Create question map
    $questionMap = [];
    $total = 0;
    
    // *** CORRECTED LOGIC for Array-based JSON ***
    foreach ($data as $questionData) {
        if (isset($questionData['id'], $questionData['answer'])) {
            // Map the question ID to the correct answer
            $questionMap[$questionData['id']] = $questionData['answer']; 
            $total++;
        }
    }
    
    $score = 0;
    
    foreach ($answers as $qId => $submittedAnswer) {
        $qId = intval($qId);
        // Compare the submitted answer to the correct answer in the map
        if (isset($questionMap[$qId]) && $submittedAnswer === $questionMap[$qId]) {
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
    <title>Vector Learn — Computer Science CBT</title>
    <style>
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
            background: #6c757d; /* Grey color for home */
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
            <h1>Vector Learn — Computer Science 2024</h1>
            <p>Mastering algorithms and hardware concepts.</p>
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
    // Get the base URL (e.g., http://example.com/quiz.php) without query parameters
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    
    // Get the path of the current script (e.g., /folder/quiz.php)
    const scriptPath = window.location.pathname;
    // Calculate the home URL by replacing the script name (e.g., quiz.php) with index.php
    const homeUrl = scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1) + 'index.php';


    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60;

    let allQuestions = [];
    let answers = {};
    let currentSection = null;
    let explanationData = null; // Store fetched explanation data

    async function loadQuestions() {
        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer();
            } else {
                showError('Failed to load questions: ' + (data.error || 'Unknown error. Check console.'));
            }
        } catch (error) {
            showError('Network Error: ' + error.message);
        }
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // Display the section (Subject Name)
        if (currentSection !== qObj.sectionName) { 
            currentSection = qObj.sectionName;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Subject: ' + qObj.sectionName + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Q' + qObj.questionId + '</span>';
        html += '<span class="question-section">' + qObj.sectionName + '</span>';
        html += '</div>';
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            if (answers[qObj.questionId] === opt.optionId) html += 'checked'; // Use questionId here
            html += ' />';
            html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = (current >= allQuestions.length);

        const nav = document.getElementById('nav-buttons');
        const existingSubmit = document.getElementById('btn-submit');
        
        if (current === allQuestions.length) {
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
        // Use the index (i+1) for navigation buttons
        for (let i = 1; i <= allQuestions.length; i++) {
            const qObj = allQuestions[i-1];
            // Use the actual question ID from the object for checking the answer map
            const answered = answers[qObj.questionId] ? 'answered' : ''; 
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
        if (current < allQuestions.length) {
            current++;
            renderQuestion();
            renderNav();
        }
    });

    function saveAnswer() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        const form = document.getElementById('qForm');
        if (!form) return;
        
        const selected = form.querySelector('input[name="answer"]:checked')?.value || null; 
        
        // Store the answer using the question's unique ID
        if (selected) answers[qObj.questionId] = selected; 
        else delete answers[qObj.questionId]; // If deselected, remove it.
        
        renderNav(); // Re-render navigation to update 'answered' status
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    let totalSeconds = duration * 60;
    let timerInterval;

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

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);

        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                // The 'answers' object already uses the correct question ID as the key
                body: JSON.stringify({ answers: answers }) 
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                showError('Submission failed: ' + (result.error || 'Unknown error.'));
            }
        } catch (error) {
            showError('Submission Error: ' + error.message);
        }
    }

    function goToHome() {
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        window.location.href = baseUrl; 
    }

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

        explanationData.forEach((qObj) => {
            const questionId = qObj.questionId; 
            const userAnswer = answers[questionId]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + qObj.sectionName + '</span>';
            html += '</div>';
            html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
            
            qObj.options.forEach(opt => {
                let labelClass = '';
                if (opt.optionId === qObj.correctAnswer) {
                    labelClass = 'correct-answer-label';
                } else if (opt.optionId === userAnswer && opt.optionId !== qObj.correctAnswer) {
                    labelClass = 'user-answer-label';
                }

                html += '<label class="option-label ' + labelClass + '">';
                html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
                html += '</label>';
            });

            if (qObj.explanation) {
                html += '<div class="explanation-box"><strong>Explanation:</strong> ' + htmlEscape(qObj.explanation) + '</div>';
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
        const resultBox = document.createElement('div');
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

    loadQuestions();
</script>



<?php include 'footer2.php'; ?></body>
</html>