<?php
// marketing2023.php - Standalone PHP Page for Marketing CBT

// UPDATED: Set the correct JSON file path for Marketing
$jsonFile = __DIR__ . '/marketing2023.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Helper function to load and decode JSON data from the file
function loadQuestionData($jsonFile) {
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => "Question file not found: " . basename($jsonFile)]);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty file.']);
        exit();
    }
    return $data;
}

// Helper function to flatten the question structure and map fields for Marketing JSON
function flattenQuestions($data, $includeDetails = false) {
    $questions = [];
    
    if (is_array($data)) {
         // Handle the flat array of questions provided by the user
         foreach ($data as $index => $question) {
            
            // Reformat options from associative array (A, B, C, D) to indexed array
            $optionsArray = [];
            if (isset($question['options']) && is_array($question['options']) && 
                array_keys($question['options']) !== range(0, count($question['options']) - 1)) {
                foreach ($question['options'] as $id => $text) {
                    $optionsArray[] = ['optionId' => $id, 'text' => $text];
                }
            } else {
                $optionsArray = $question['options'] ?? [];
            }
            
            // Map user's fields to the UI's expected fields
            $newQuestion = [];
            $newQuestion['questionId'] = $question['number'] ?? ($index + 1); // Use number or index
            $newQuestion['question'] = $question['instruction'] ?? 'No Question Text';
            $newQuestion['options'] = $optionsArray;
            $newQuestion['sectionName'] = 'General Marketing'; // Fixed subject name
            $newQuestion['sectionId'] = 1; 

            if (isset($question['image'])) {
                $newQuestion['image'] = $question['image'];
            }
            
            if ($includeDetails) {
                $newQuestion['correctAnswer'] = $question['answer'] ?? null;
                $newQuestion['explanation'] = $question['explanation'] ?? 'No explanation provided.';
            }
            
            // Remove sensitive fields if not needed
            if (!$includeDetails) {
                unset($newQuestion['correctAnswer']);
                unset($newQuestion['explanation']);
            }
            
            if ($newQuestion['questionId'] !== null) {
                $questions[] = $newQuestion;
            }
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Marketing JSON structure is unexpected.']);
        exit();
    }
    
    return $questions;
}


// --- ACTION 1: Get Questions for the Exam View (Without Answers) ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $data = loadQuestionData($jsonFile);
    
    $questions = flattenQuestions($data, false);
    
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
    $data = loadQuestionData($jsonFile);

    $questionsWithDetails = flattenQuestions($data, true);
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? []; // User answers keyed by question index (1-based)
    
    $data = loadQuestionData($jsonFile);

    // Get all questions with details to build the answer map
    $allQuestionsWithDetails = flattenQuestions($data, true);
    
    // Map question index (1-based) to the correct answer for efficient lookup
    $correctAnswersMap = [];
    foreach ($allQuestionsWithDetails as $index => $qObj) {
        $questionIndex = $index + 1;
        $correctAnswersMap[$questionIndex] = $qObj['correctAnswer'];
    }
    
    $score = 0;
    $total = count($correctAnswersMap); // Total questions processed
    
    // Compare user answers against correct answers
    foreach ($answers as $qIndex => $userAnswer) {
        // $qIndex is the 1-based question number (1, 2, 3...)
        if (isset($correctAnswersMap[$qIndex]) && $userAnswer === $correctAnswersMap[$qIndex]) {
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
    <title>Vector Learn — Marketing CBT</title>
    <style>
        /* === UI/UX Consistency CSS (Unchanged from provided pattern) === */
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
            <h1>Vector Learn — **Marketing 2023**</h1>
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
    // --- Configuration ---
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    const scriptPath = window.location.pathname;
    // Assuming 'index.php' or similar is the home page
    const homeUrl = scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1) + 'index.php'; 

    // --- State ---
    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60; // 60 minutes default

    let allQuestions = []; // Array of questions (without answers/explanations)
    let answers = {}; // User answers, keyed by question index (1-based)
    let currentSection = null;
    let explanationData = null; 
    let totalSeconds = duration * 60;
    let timerInterval;

    // --- Helpers ---
    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
    }

    // --- Data Loading (AJAX) ---
    async function loadQuestions() {
        try {
            // Use PHP_SELF to call back to this same script with the 'get_questions' action
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer();
            } else {
                showError('Failed to load questions or no questions found.');
            }
        } catch (error) {
            showError('Error loading initial data: ' + error.message);
        }
    }

    // --- Rendering Functions ---
    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // Check and display Section Divider
        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        // Use question number (current) for display alongside questionId from data
        html += '<span class="question-id">Q' + current + ' (' + qObj.questionId + ')</span>'; 
        html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
        html += '</div>';

        // Check for image
        if (qObj.image) {
             html += `<div style="text-align: center; margin-bottom: 20px;">
                        <img src="${htmlEscape(qObj.image)}" alt="Question Image" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc;">
                    </div>`;
        }
        
        // Use htmlEscape on instruction text in case it contains special characters
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            // Answer key is the 1-based index (current)
            if (answers[current] === opt.optionId) html += 'checked'; 
            html += ' />';
            html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        // Navigation button logic
        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        const nav = document.getElementById('nav-buttons');
        let existingSubmit = document.getElementById('btn-submit');
        
        // Final Question = Submit Button
        if (current === allQuestions.length) {
            if (!existingSubmit) {
                btnNext.style.display = 'none';
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                nav.appendChild(submitBtn);
            } else {
                 existingSubmit.style.display = 'inline-flex';
                 btnNext.style.display = 'none';
            }
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
        }
    }

    function renderNav() {
        let html = '';
        for (let i = 1; i <= allQuestions.length; i++) {
            const answered = answers[i] ? 'answered' : '';
            const active = i === current ? 'active' : '';
            html += '<a href="#" onclick="navigate(' + i + '); return false;" class="' + active + ' ' + answered + '">' + i + '</a>';
        }
        document.getElementById('q-nav').innerHTML = html;
    }

    // --- Navigation & Answer Saving ---
    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').addEventListener('click', () => {
        saveAnswer();
        if (current > 1) navigate(current - 1);
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        saveAnswer();
        if (current < allQuestions.length) navigate(current + 1);
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form || !form.answer) return;
        const selected = form.answer.value || null;
        if (selected) answers[current] = selected; // Store answer keyed by question index (1-based)
        renderNav();
    }

    // --- Timer Logic ---
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

    // --- Submission & Results Logic (AJAX) ---
    async function submitAnswers() {
        saveAnswer();
        if(timerInterval) clearInterval(timerInterval);

        try {
            // Send user answers (keyed by question index) to the PHP script
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
            showError('Error submitting answers: ' + error.message);
        }
    }
    
    // --- Navigation Functions for Results View ---
    function goToHome() {
        window.location.href = homeUrl; 
    }
    
    function retakeExam() {
        window.location.href = baseUrl; 
    }

    // --- Explanation Loading & Rendering ---
    async function viewExplanations() {
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading">Loading explanations...</p>';
        
        if (!explanationData) {
              try {
                 // Use PHP_SELF to call back to this same script with the 'get_explanations' action
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
            const questionNumber = index + 1;
            const userAnswer = answers[questionNumber]; // Get user answer using 1-based index
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + questionNumber + ' (' + qObj.questionId + ') — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
            html += '</div>';
             // Check for image
            if (qObj.image) {
                 html += `<div style="text-align: center; margin-bottom: 20px;">
                            <img src="${htmlEscape(qObj.image)}" alt="Question Image" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc;">
                        </div>`;
            }

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
        // Navigation Group for Explanation View
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        html += '<button class="nav-btn" onclick="retakeExam()" style="width: auto;">🔄 Retake Exam</button>';
        html += '</div>';

        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }
    
    // --- Display Results ---
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
        
        // Navigation Group for Results View
        resultBox.innerHTML += '<div class="nav-button-group">';
        resultBox.innerHTML += '<button class="nav-btn home-btn" onclick="goToHome()">🏠 Go to Home</button>';
        resultBox.innerHTML += '<button class="nav-btn explanation-btn" onclick="viewExplanations()">View Explanation</button>';
        resultBox.innerHTML += '<button class="nav-btn" onclick="retakeExam()">🔄 Retake Exam</button>';
        resultBox.innerHTML += '</div>';
        
        // Remove existing result box if any, and append the new one
        if (document.getElementById('results-view')) document.getElementById('results-view').remove();
        container.appendChild(resultBox);
    }

    // Initialize application
    loadQuestions();
</script>



<?php include 'footer.php'; ?></body>
</html>