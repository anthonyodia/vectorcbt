<?php
// Economics 2021 CBT - Standalone PHP Page
// Design based on CRK CBT template

$jsonFile = __DIR__ . '/economics2021.json';

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    $data = json_decode(file_get_contents($jsonFile), true);
    
    // Process flat array from Economics JSON
    $questions = [];
    foreach ($data['questions'] as $q) {
        $processed = $q;
        unset($processed['answer']);
        unset($processed['explanation']);
        // Add a default section name since the new structure doesn't use sections
        $processed['sectionName'] = "Objective Questions"; 
        $processed['questionId'] = $q['id']; // Map 'id' to 'questionId' for frontend compatibility
        $questions[] = $processed;
    }
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get Explanations ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents($jsonFile), true);
    
    $questionsWithDetails = [];
    foreach ($data['questions'] as $q) {
        $q['sectionName'] = "Objective Questions";
        $q['questionId'] = $q['id'];
        $q['correctAnswer'] = $q['answer']; // Map answer to correctAnswer
        $questionsWithDetails[] = $q;
    }
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswers = $input['answers'] ?? [];
    $data = json_decode(file_get_contents($jsonFile), true);
    
    $score = 0;
    $total = count($data['questions']);
    
    foreach ($data['questions'] as $q) {
        $qId = $q['id'];
        // Note: JSON answer is 0-indexed integer, user key is question number string
        if (isset($userAnswers[$qId]) && (int)$userAnswers[$qId] === (int)$q['answer']) {
            $score++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => $total > 0 ? round(($score / $total) * 100, 2) : 0
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Economics 2021</title>
    <style>
        /* CSS design remains identical to your CRK template */
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
        .nav-btn:hover:not(.disabled) { background: #38c172; }
        .nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: auto; }
        .question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; transition: 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .instruction-box { font-style: italic; background: #eee; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin: 30px; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span><span>Step 2: Pick Subject</span><span class="active">Step 3: Begin!</span>
        </div>
        <div class="title">
            <h1>Vector Learn — Economics 2021</h1>
            <p>Every resource managed well leads to growth.</p>
        </div>
        <div class="form-box" id="info-box">Time Remaining: <span id="countdown">--:--</span></div>
        <div id="section-display"></div>
        <div class="question-container" id="q-container"><p class="loading">Loading...</p></div>
        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
            <button class="nav-btn" id="btn-next" disabled>Next →</button>
        </div>
        <div class="question-nav" id="q-nav"></div>
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script>
    const baseUrl = window.location.pathname;
    let current = 1, allQuestions = [], answers = {}, explanationData = null;
    let totalSeconds = 60 * 60, timerInterval;

    async function loadQuestions() {
        const res = await fetch(baseUrl + '?action=get_questions');
        const data = await res.json();
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
            <span class="question-id">Q${qObj.questionId}</span>
            <span class="question-section">Economics 2021</span>
        </div>`;
        
        if(qObj.instruction) html += `<div class="instruction-box">${qObj.instruction}</div>`;
        
        html += `<div class="question-text">${qObj.question}</div><form id="qForm">`;
        qObj.options.forEach((opt, i) => {
            html += `<label class="option-label">
                <input type="radio" name="answer" value="${i}" ${answers[qObj.questionId] == i ? 'checked' : ''} onchange="saveAnswer(${qObj.questionId}, ${i})">
                <span>${opt}</span>
            </label>`;
        });
        qc.innerHTML = html + `</form>`;

        document.getElementById('btn-prev').disabled = current === 1;
        const nextBtn = document.getElementById('btn-next');
        
        if (current === allQuestions.length) {
            nextBtn.innerHTML = "Submit Exam";
            nextBtn.style.background = "#ff6b6b";
            nextBtn.onclick = submitAnswers;
        } else {
            nextBtn.innerHTML = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => navigate(current + 1);
        }
        nextBtn.disabled = false;
    }

    function saveAnswer(qid, val) {
        answers[qid] = val;
        renderNav();
    }

    function navigate(num) {
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').onclick = () => navigate(current - 1);

    function renderNav() {
        let html = '';
        allQuestions.forEach((q, i) => {
            const num = i + 1;
            const cls = (num === current ? 'active' : '') + (answers[q.questionId] !== undefined ? ' answered' : '');
            html += `<a onclick="navigate(${num})" class="${cls}">${num}</a>`;
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    function startTimer() {
        timerInterval = setInterval(() => {
            if (totalSeconds <= 0) submitAnswers();
            totalSeconds--;
            let mins = Math.floor(totalSeconds / 60), secs = totalSeconds % 60;
            document.getElementById('countdown').textContent = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
        }, 1000);
    }

    async function submitAnswers() {
        clearInterval(timerInterval);
        const res = await fetch(baseUrl + '?action=submit', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ answers: answers })
        });
        const result = await res.json();
        displayResults(result);
    }

    function displayResults(result) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const resView = document.createElement('div');
        resView.className = 'question-container';
        resView.style.textAlign = 'center';
        resView.innerHTML = `
            <div style="font-size: 32px; font-weight: bold;">Results</div>
            <div style="font-size: 48px; color: #007aff; margin: 20px 0;">${result.score}/${result.total}</div>
            <div style="font-size: 24px; color: #43e97b; margin-bottom: 20px;">${result.percentage}%</div>
            <div class="nav-button-group">
                <button class="nav-btn" onclick="location.reload()">🔄 Retake</button>
                <button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">View Explanations</button>
            </div>`;
        document.querySelector('.container').appendChild(resView);
    }

    async function viewExplanations() {
        const res = await fetch(baseUrl + '?action=get_explanations');
        const data = await res.json();
        document.querySelector('.container').lastElementChild.style.display = 'none';
        const container = document.getElementById('explanation-container');
        container.style.display = 'block';
        let html = '<h2>Explanations</h2>';
        
        data.questions.forEach((q, i) => {
            const userAns = answers[q.questionId];
            html += `<div class="question-container">
                <div class="question-text">Q${i+1}. ${q.question}</div>
                ${q.options.map((opt, oi) => {
                    let cls = '';
                    if (oi == q.correctAnswer) cls = 'correct-answer-label';
                    else if (oi == userAns) cls = 'user-answer-label';
                    return `<div class="option-label ${cls}">${opt}</div>`;
                }).join('')}
                <div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div>
            </div>`;
        });
        container.innerHTML = html + `<div class="nav-button-group"><button class="nav-btn" onclick="location.reload()">Finish</button></div>`;
        window.scrollTo(0,0);
    }

    loadQuestions();
</script>


<?php include 'footer2.php'; ?></body>
</html>