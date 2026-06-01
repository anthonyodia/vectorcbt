<?php
// Commerce 2022 CBT - Standalone PHP Page
// This file handles both loading and serving the Commerce CBT

// *** FILE NAME UPDATED TO COMMERCE2022.JSON ***
$jsonFile = __DIR__ . '/commerce2022.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions for the Exam View ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: commerce2022.json']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format in commerce2022.json']);
        exit();
    }
    
    // Flatten questions, but remove answers/explanations for exam security
    $questions = [];
    foreach ($data as $question) {
        // Map the user's JSON structure to the expected internal format
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'sectionName' => $question['topic'], // Using 'topic' as 'sectionName'
            'sectionId' => str_replace(' ', '_', $question['topic']), // Create a unique ID from topic
            // Transform A, B, C, D object into an array of options
            'options' => array_map(function($key, $value) {
                return ['optionId' => $key, 'text' => $value];
            }, array_keys($question['options']), array_values($question['options']))
            // Note: The 'data' field for calculations is ignored in this view for simplicity
        ];
        
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
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: commerce2022.json']);
        exit();
    }
    
    // 🌟 TYPO FIX APPLIED HERE: Changed file_file_get_contents to file_get_contents
    $jsonContent = file_get_contents($jsonFile); 

    $data = json_decode($jsonContent, true);

    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format in commerce2022.json']);
        exit();
    }

    // Flatten all questions with all details
    $questionsWithDetails = [];
    foreach ($data as $question) {
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'sectionName' => $question['topic'], 
            'sectionId' => str_replace(' ', '_', $question['topic']), 
            'correctAnswer' => $question['answer'], // Use 'answer' as 'correctAnswer'
            'explanation' => $question['explanation'],
            'options' => array_map(function($key, $value) {
                return ['optionId' => $key, 'text' => $value];
            }, array_keys($question['options']), array_values($question['options']))
        ];
        // Check for 'data' field used in calculation questions (Q13, Q14)
        if (isset($question['data']) && is_array($question['data'])) {
            // Format the data array into HTML table or list for display in explanation view
            $dataHtml = '<p><strong>Data Provided:</strong></p><ul>';
            foreach ($question['data'] as $item) {
                $dataHtml .= '<li><strong>' . htmlspecialchars($item[0]) . ':</strong> ' . htmlspecialchars($item[1]) . '</li>';
            }
            $dataHtml .= '</ul>';
            $q['explanation'] = $dataHtml . $q['explanation']; // Prepend data to explanation
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
    $answers = $input['answers'] ?? [];
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: commerce2022.json']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    // Create question map
    $questionMap = [];
    foreach ($data as $question) {
        // Use 'id' from JSON as the key, and 'answer' as the correct answer
        $questionMap[$question['id']] = $question['answer'];
    }
    
    $score = 0;
    $total = count($questionMap);
    
    foreach ($answers as $qId => $answer) {
        $qId = intval($qId);
        // Compare user answer to the stored correct answer
        if (isset($questionMap[$qId]) && $answer === $questionMap[$qId]) {
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
    <title>Vector Learn — Commerce 2022 CBT</title> 
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
            white-space: nowrap; /* Prevent topic name wrapping */
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
        
        /* Style for data list inside explanation box */
        .explanation-box ul {
            list-style: none;
            padding: 0;
            margin: 10px 0;
            font-size: 13px;
        }
        .explanation-box ul li {
            background: #d0e8ff;
            padding: 5px 10px;
            margin-bottom: 3px;
            border-radius: 4px;
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
                 flex: 0 0 auto; /* Stop them from stretching */
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
            <h1>Vector Learn — Commerce 2022</h1>
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
    // Get the path of the current script (e.g., /folder/quiz.php)
    const scriptPath = window.location.pathname;
    // Get the base URL without query parameters
    const baseUrl = window.location.protocol + "//" + window.location.host + scriptPath;
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
                showError('Failed to load questions. ' + (data.error || 'Check commerce2022.json format/existence.'));
            }
        } catch (error) {
            showError('Error loading questions: ' + error.message);
        }
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

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
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            // Use questionId for answer storage, which matches the JSON 'id'
            if (answers[qObj.questionId] === opt.optionId) html += 'checked'; 
            html += ' />';
            html += '<span>' + htmlEscape(opt.optionId) + '. ' + htmlEscape(opt.text) + '</span>';
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
        for (let i = 1; i <= allQuestions.length; i++) {
            const qId = allQuestions[i-1].questionId; // Get the actual question ID
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
        if (current < allQuestions.length) {
            current++;
            renderQuestion();
            renderNav();
        }
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form) return;
        const selected = form.answer.value || null;
        const qObj = allQuestions[current - 1];
        if (qObj && selected) answers[qObj.questionId] = selected; // Save answer using questionId
        renderNav(); // Update navigation status immediately
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
                body: JSON.stringify({ answers: answers })
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                 showError('Submission failed: ' + (result.error || 'Server error.'));
            }
        } catch (error) {
            showError('Error during submission: ' + error.message);
        }
    }

    function goToHome() {
        // Navigates to index.php in the current directory
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        // Navigates to the current PHP file without query parameters, resetting the quiz
        window.location.href = baseUrl; 
    }

    async function viewExplanations() {
        // Hide exam view elements
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading">Loading explanations...</p>';
        
        // Fetch explanation data if not already done
        if (!explanationData) {
              try {
                  // Pass question IDs as answers are keyed by them
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
            const questionId = qObj.questionId;
            const userAnswer = answers[questionId]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
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
                html += '<span>' + htmlEscape(opt.optionId) + '. ' + htmlEscape(opt.text) + '</span>';
                html += '</label>';
            });

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
    
    // Original displayResults function modified to integrate explanation view and new buttons
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



<?php include 'footer.php'; ?></body>
</html>