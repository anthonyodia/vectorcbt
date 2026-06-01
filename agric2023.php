<?php
// AGRIC 2023 CBT - Standalone PHP Page

// IMPORTANT: The question bank file must be named agric2023.json
$jsonFile = __DIR__ . '/agric2023.json';

// --- Functions to process the JSON file ---

// Function to safely load and decode JSON data
function load_json_data($file) {
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    
    // Assuming a flat structure with a top-level 'questions' array for simplicity
    if (!$data || !isset($data['questions'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or missing "questions" key']);
        exit();
    }
    return $data['questions'];
}

// Function to structure data for secure exam mode (removes answers)
function prepare_exam_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        $question = [
            'questionId' => $q['id'],
            'question' => $q['question'],
            'image' => $q['image'] ?? null,
            'table' => $q['table'] ?? null,
            'options' => [],
            // Set dummy section info, as original JSON is flat
            'sectionName' => 'Agricultural Science', 
            'sectionId' => 1
        ];
        
        // Convert the 'options' object into an array of options for easy JS iteration
        foreach ($q['options'] as $optionId => $text) {
            $question['options'][] = [
                'optionId' => $optionId,
                'text' => $text
            ];
        }
        $questions[] = $question;
    }
    return $questions;
}

// Function to structure data for explanation mode (includes all details)
function prepare_explanation_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        $question = [
            'questionId' => $q['id'],
            'question' => $q['question'],
            'correctAnswer' => $q['answer'],
            'explanation' => $q['explanation'],
            'image' => $q['image'] ?? null,
            'table' => $q['table'] ?? null,
            'sectionName' => 'Agricultural Science', 
            'sectionId' => 1,
            'options' => []
        ];
        
        // Convert the 'options' object into an array of options for easy JS iteration
        foreach ($q['options'] as $optionId => $text) {
            $question['options'][] = [
                'optionId' => $optionId,
                'text' => $text
            ];
        }
        $questions[] = $question;
    }
    return $questions;
}


// --- API ACTIONS ---
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $raw_questions = load_json_data($jsonFile);
    $questions = prepare_exam_questions($raw_questions);
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $raw_questions = load_json_data($jsonFile);
    $questionsWithDetails = prepare_explanation_questions($raw_questions);
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];
    
    $raw_questions = load_json_data($jsonFile);
    
    // Create a map from question ID to correct answer
    $correct_answers_map = [];
    foreach ($raw_questions as $question) {
        // Use 'id' from JSON as the key for the map
        $correct_answers_map[$question['id']] = $question['answer'];
    }
    
    $score = 0;
    $total = count($correct_answers_map);
    
    foreach ($answers as $qId => $userAnswer) {
        // Ensure qId is an integer for lookup
        $qId = intval($qId); 
        if (isset($correct_answers_map[$qId]) && $userAnswer === $correct_answers_map[$qId]) {
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

// --- HTML INTERFACE (If no action is requested) ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Agric 2023 CBT</title>
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
            /* Keep this to prevent horizontal scrollbar on body */
            overflow-x: hidden; 
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
        
        .question-text img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .question-text table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #fff;
            border: 1px solid #ddd;
        }
        .question-text th, .question-text td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .question-text th {
            background-color: #f2f2f2;
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
            /* FIX 1: Allow content to wrap onto the next line */
            /* FIX 2: Keep overflow hidden to prevent scrollbar */
            overflow-x: hidden; 
            /* REMOVED: white-space: nowrap; <--- Removing this is the crucial fix */
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

<?php include 'topnavbar.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — Agric 2023 CBT</h1>
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
    // --- Helper Functions ---
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    const scriptPath = window.location.pathname;
    const homeUrl = scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1) + 'index.php'; 

    function htmlEscape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Function to render table data from the JSON
    function renderTable(tableData) {
        if (!tableData || typeof tableData !== 'object' || Array.isArray(tableData)) return '';
        let html = '<table><thead><tr>';
        
        // Use keys as table headers
        const headers = Object.keys(tableData);
        headers.forEach(key => {
            const headerText = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            html += '<th>' + headerText + '</th>';
        });
        html += '</tr></thead><tbody><tr>';
        
        // Use values as table row data
        headers.forEach(key => {
            let value = tableData[key];
            if (key.includes('price') || key.includes('value') || key.includes('depreciation')) {
                value = 'N ' + (typeof value === 'number' ? value.toLocaleString() : value);
            }
            html += '<td>' + htmlEscape(value) + '</td>';
        });
        html += '</tr></tbody></table>';
        return html;
    }

    // --- State Variables ---
    let current = 1; 
    let duration = 60; // Default 60 minutes
    let allQuestions = [];
    let answers = {};
    let currentSection = null;
    let explanationData = null; 
    let totalSeconds = duration * 60;
    let timerInterval;

    // --- Core UI Rendering ---

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // 1. Update Section Divider
        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Q' + qObj.questionId + '</span>';
        html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
        html += '</div>';
        
        // 2. Question Text and Media (Image/Table)
        html += '<div class="question-text">';
        html += htmlEscape(qObj.question);
        if (qObj.image) {
            html += '<img src="' + htmlEscape(qObj.image) + '" alt="Question Diagram"/>';
        }
        if (qObj.table) {
            html += renderTable(qObj.table);
        }
        html += '</div>';
        
        // 3. Options
        html += '<form id="qForm">';
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            if (answers[qObj.questionId] === opt.optionId) html += 'checked'; 
            html += ' />';
            html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        html += '</form>';
        qc.innerHTML = html;

        // 4. Update Navigation Buttons
        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        const existingSubmit = document.getElementById('btn-submit');
        
        if (current === allQuestions.length) {
            if (!existingSubmit) {
                const navButtons = document.getElementById('nav-buttons');
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                // Append it to navButtons, ensuring it's available next to the Prev button
                navButtons.appendChild(submitBtn);
                btnNext.style.display = 'none';
            }
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
        }
        
        document.getElementById('q-container').scrollIntoView({ behavior: 'smooth' });
    }

    function renderNav() {
        let html = '';
        allQuestions.forEach((qObj, index) => {
            const num = index + 1;
            const answered = answers[qObj.questionId] ? 'answered' : '';
            const active = num === current ? 'active' : '';
            html += '<a href="#" onclick="navigate(' + num + '); return false;" class="' + active + ' ' + answered + '">' + num + '</a>';
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    // --- Navigation and Answer Handling ---

    function saveAnswer() {
        const form = document.getElementById('qForm');
        const qObj = allQuestions[current - 1];
        if (!form || !qObj) return;
        
        const selected = form.answer.value || null;
        if (selected) {
            answers[qObj.questionId] = selected; 
        } else {
            delete answers[qObj.questionId];
        }
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

    // --- Timer Functions ---

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

    // --- API Calls and Views ---

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
                showError('Failed to load questions or question bank is empty.');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        }
    }

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);
        document.getElementById('q-container').innerHTML = '<p class="loading">Submitting and grading exam...</p>';


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
                 showError('Submission failed: ' + (result.error || 'Unknown error.'));
            }
        } catch (error) {
            showError('Network Error: Could not submit answers. ' + error.message);
        }
    }

    function displayResults(result) {
        // Hide Exam View elements
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const container = document.querySelector('.container');
        const resultBox = document.createElement('div');
        resultBox.className = 'question-container';
        resultBox.id = 'results-view'; 
        resultBox.style.textAlign = 'center';
        resultBox.innerHTML = '<div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Exam Results</div>';
        resultBox.innerHTML += '<div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">' + result.score + '/' + result.total + '</div>';
        resultBox.innerHTML += '<div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">' + result.percentage + '%</div>';
        resultBox.innerHTML += '<div style="font-size: 16px; color: #555; margin-bottom: 30px;">You answered ' + result.score + ' out of ' + result.total + ' questions correctly.</div>';
        
        // Navigation Group for Results View
        resultBox.innerHTML += '<div class="nav-button-group">';
        resultBox.innerHTML += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        resultBox.innerHTML += '<button class="nav-btn explanation-btn" onclick="viewExplanations()">View Explanation</button>';
        resultBox.innerHTML += '<button class="nav-btn" onclick="retakeExam()">🔄 Retake Exam</button>';
        resultBox.innerHTML += '</div>';
        
        container.appendChild(resultBox);
    }
    
    function goToHome() {
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        window.location.href = baseUrl; 
    }
    
    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + htmlEscape(message) + '</div>';
    }

    async function viewExplanations() {
        // Hide results view
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
                  explanationContainer.innerHTML = '<div class="error">Error loading explanations: ' + htmlEscape(error.message) + '</div>';
              }
        } else {
            displayExplanations();
        }
    }

    function displayExplanations() {
        const explanationContainer = document.getElementById('explanation-container');
        let html = '<div class="explanation-view">';

        explanationData.forEach((qObj) => {
            const userAnswer = answers[qObj.questionId]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
            html += '</div>';
            
            // Question Text and Media
            html += '<div class="question-text">';
            html += htmlEscape(qObj.question);
            if (qObj.image) {
                 html += '<img src="' + htmlEscape(qObj.image) + '" alt="Question Diagram"/>';
            }
            if (qObj.table) {
                html += renderTable(qObj.table);
            }
            html += '</div>';

            // Options and Highlighting
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

            // Explanation Box
            if (qObj.explanation) {
                html += '<div class="explanation-box"><strong>Explanation:</strong> <p>' + htmlEscape(qObj.explanation) + '</p></div>';
            }

            html += '</div>';
        });

        html += '</div>';
        // Navigation Group for Explanation View
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        html += '<button class="nav-btn" onclick="retakeExam()">🔄 Retake Exam</button>';
        html += '</div>';

        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }

    // --- Initialization ---
    loadQuestions();
</script>



<?php include 'footer.php'; ?></body>
</html>