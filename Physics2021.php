<?php
// Vector Learn - Physics 2021 CBT
// This file handles both loading and serving the CBT

// Path to the JSON data file as requested
$jsonFile = __DIR__ . '/physics2021.json'; 

// --- Core Function to Load and Validate JSON Data ---
function loadJsonData($file) {
    if (!file_exists($file)) {
        return ['error' => 'Question file (' . basename($file) . ') not found. Please ensure physics2021.json is in the same directory.'];
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = json_last_error_msg();
        return ['error' => "JSON Decoding Error: **{$errorMsg}**."];
    }
    
    if (isset($data['questions']) && is_array($data['questions'])) {
        $questionsArray = $data['questions'];
    } elseif (is_array($data) && isset($data[0]['id'])) {
         $questionsArray = $data;
    } else {
        return ['error' => 'JSON format is invalid. Ensure it contains a "questions" key with an array.'];
    }
    
    $standardizedQuestions = [];
    foreach ($questionsArray as $q) {
        $standardizedQuestions[] = [
            'id' => $q['id'],
            'question' => $q['question'],
            'options' => $q['options'],
            'answer' => $q['answer'],
            'explanation' => $q['explanation'] ?? '',
            'image' => $q['image_name'] ?? null, 
            'sectionName' => $q['sectionName'] ?? "WAEC Physics 2021",
            'sectionId' => $q['sectionId'] ?? "PHY2021"
        ];
    }
    
    return ['questions' => $standardizedQuestions];
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$jsonResult = loadJsonData($jsonFile);
$allQuestions = $jsonResult['questions'] ?? [];

if (isset($jsonResult['error']) && $action) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $jsonResult['error']]);
    exit();
}

if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $questions = [];
    foreach ($allQuestions as $question) {
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'image' => $question['image'],
            'sectionName' => $question['sectionName'],
            'sectionId' => $question['sectionId'],
            'options' => []
        ];
        foreach ($question['options'] as $optionId => $text) {
            $q['options'][] = ['optionId' => $optionId, 'text' => $text];
        }
        $questions[] = $q;
    }
    echo json_encode(['success' => true, 'totalQuestions' => count($questions), 'questions' => $questions]);
    exit();
}

if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $questionsWithDetails = [];
    foreach ($allQuestions as $question) {
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'image' => $question['image'],
            'sectionName' => $question['sectionName'],
            'sectionId' => $question['sectionId'],
            'correctAnswer' => $question['answer'],
            'explanation' => $question['explanation'],
            'options' => []
        ];
        foreach ($question['options'] as $optionId => $text) {
            $q['options'][] = ['optionId' => $optionId, 'text' => $text];
        }
        $questionsWithDetails[] = $q;
    }
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswers = $input['answers'] ?? [];
    $score = 0;
    $total = count($allQuestions);
    
    foreach ($allQuestions as $q) {
        $id = $q['id'];
        if (isset($userAnswers[$id]) && $userAnswers[$id] === $q['answer']) {
            $score++;
        }
    }
    $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
    echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'percentage' => $percentage]);
    exit();
}

$initialError = isset($jsonResult['error']) ? $jsonResult['error'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Physics 2021</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; padding: 6px; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; margin: 0; color: #1e2a3a; }
        .title p { margin: 8px 0 22px 0; font-size: 15px; color: #555; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 20px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 300px; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .question-id { font-size: 14px; color: #007aff; font-weight: 600; }
        .question-section { font-size: 12px; color: #999; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; }
        .question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:disabled { background: #ccc !important; cursor: not-allowed; }
        #btn-submit { background: #ff6b6b; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; margin: 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; text-decoration: none; transition: 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; font-size: 14px; }
        .question-image-wrapper { text-align: center; margin-bottom: 20px; }
        .question-image-wrapper img { max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ccc; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin: 30px; }
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
            <h1>Vector Learn — Physics 2021</h1>
            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <?php if ($initialError): ?>
        <div class="question-container">
            <div class="error">
                <strong>CRITICAL ERROR:</strong>
                <p><?php echo $initialError; ?></p>
            </div>
        </div>
        <?php else: ?>

        <div class="question-container" id="q-container">
            <p style="text-align:center">Loading questions...</p>
        </div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev">← Previous</button>
            <button class="nav-btn" id="btn-next">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
        <?php endif; ?>
    </div>

<script>
    <?php if (!$initialError): ?>
    let current = 1;
    let duration = 60; 
    let allQuestions = [];
    let answers = {};
    let timerInterval;

    async function loadQuestions() {
        const response = await fetch('?action=get_questions');
        const data = await response.json();
        if (data.success) {
            allQuestions = data.questions;
            renderQuestion();
            renderNav();
            startTimer();
        }
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        const qc = document.getElementById('q-container');
        
        let html = `<div class="question-header">
                        <span class="question-id">Question ${current} of ${allQuestions.length}</span>
                        <span class="question-section">${qObj.sectionName}</span>
                    </div>`;

        if (qObj.image) html += `<div class="question-image-wrapper"><img src="${qObj.image}" alt="Diagram"></div>`;
        html += `<div class="question-text">${qObj.question}</div><form id="qForm">`;
        
        qObj.options.forEach(opt => {
            html += `<label class="option-label">
                <input type="radio" name="answer" value="${opt.optionId}" ${answers[current] === opt.optionId ? 'checked' : ''}>
                <span>${opt.optionId}. ${opt.text}</span>
            </label>`;
        });
        qc.innerHTML = html + `</form>`;
        updateButtons();
    }

    function renderNav() {
        let html = '';
        for (let i = 1; i <= allQuestions.length; i++) {
            let qId = allQuestions[i-1].questionId;
            html += `<a href="#" onclick="navigate(${i}); return false;" class="${i === current ? 'active' : ''} ${answers[i] ? 'answered' : ''}">${i}</a>`;
        }
        document.getElementById('q-nav').innerHTML = html;
    }

    function navigate(num) { saveAnswer(); current = num; renderQuestion(); renderNav(); }

    function saveAnswer() {
        const selected = document.querySelector('input[name="answer"]:checked');
        if (selected) answers[current] = selected.value;
    }

    function updateButtons() {
        document.getElementById('btn-prev').disabled = current === 1;
        const nextBtn = document.getElementById('btn-next');
        
        if (current === allQuestions.length) {
            nextBtn.innerHTML = "Submit Exam";
            nextBtn.style.background = "#ff6b6b";
            nextBtn.onclick = submitAnswers;
        } else {
            nextBtn.innerHTML = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => { saveAnswer(); current++; renderQuestion(); renderNav(); };
        }
    }

    document.getElementById('btn-prev').onclick = () => { saveAnswer(); current--; renderQuestion(); renderNav(); };

    function startTimer() {
        let time = duration * 60;
        timerInterval = setInterval(() => {
            let m = Math.floor(time / 60);
            let s = time % 60;
            document.getElementById('countdown').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if (time-- <= 0) submitAnswers();
        }, 1000);
    }

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);
        const response = await fetch('?action=submit', {
            method: 'POST',
            body: JSON.stringify({ answers: answers })
        });
        const result = await response.json();
        showResults(result);
    }

    function showResults(result) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        
        const resHtml = `<div class="question-container" style="text-align:center">
            <h2>Results</h2>
            <h1 style="font-size:48px; color:#007aff">${result.score} / ${result.total}</h1>
            <p>${result.percentage}%</p>
            <div class="nav-button-group">
                <button class="nav-btn" onclick="location.reload()">Try Again</button>
                <button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">View Explanations</button>
            </div>
        </div>`;
        document.querySelector('.container').insertAdjacentHTML('beforeend', resHtml);
    }

    async function viewExplanations() {
        const response = await fetch('?action=get_explanations');
        const data = await response.json();
        document.getElementById('info-box').textContent = "Explanations";
        document.querySelector('.question-container:last-child').style.display = 'none';
        const container = document.getElementById('explanation-container');
        container.style.display = 'block';
        
        let html = '';
        data.questions.forEach((q, i) => {
            const isCorrect = answers[i+1] === q.correctAnswer;
            html += `<div class="question-container" style="background:#fff; border:1px solid #eee">
                <div class="question-header">Q${q.questionId} - ${isCorrect ? '✅' : '❌'}</div>
                <div class="question-text">${q.question}</div>`;
            q.options.forEach(opt => {
                let cls = opt.optionId === q.correctAnswer ? 'correct-answer-label' : (opt.optionId === answers[i+1] ? 'user-answer-label' : '');
                html += `<label class="option-label ${cls}">${opt.optionId}. ${opt.text}</label>`;
            });
            html += `<div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div></div>`;
        });
        container.innerHTML = html + `<button class="nav-btn" onclick="location.reload()">Back to Start</button>`;
    }

    loadQuestions();
    <?php endif; ?>
</script>


<?php include 'footer2.php'; ?></body>
</html>