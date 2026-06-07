<?php
// Mathematics 2021 CBT - Vector Learn UI/UX
$jsonFile = __DIR__ . '/mathematics2021.json';

// --- Core Function to Load and Normalize JSON Data ---
function loadJsonData($file) {
    if (!file_exists($file)) {
        return ['error' => 'Question file not found'];
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => "JSON Error: " . json_last_error_msg()];
    }

    $questionsArray = [];
    // NORMALIZE: Convert "0":{}, "1":{} format into a flat array
    foreach ($data as $key => $q) {
        if (is_numeric($key) || (is_array($q) && isset($q['question']))) {
            $q['id'] = $q['id'] ?? ("id" . ($key + 1));
            $q['sectionName'] = $q['sectionName'] ?? "Mathematics 2021";
            $q['sectionId'] = $q['sectionId'] ?? "MATH2021";
            $questionsArray[] = $q;
        }
    }
    
    return ['questions' => $questionsArray];
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$jsonResult = loadJsonData($jsonFile);
$allQuestions = $jsonResult['questions'] ?? [];

// --- API ACTIONS ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $output = [];
    foreach ($allQuestions as $question) {
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'image' => $question['image'] ?? null,
            'sectionName' => $question['sectionName'],
            'sectionId' => $question['sectionId'],
            'options' => []
        ];
        foreach ($question['options'] as $id => $text) {
            $q['options'][] = ['optionId' => $id, 'text' => $text];
        }
        $output[] = $q;
    }
    echo json_encode(['success' => true, 'totalQuestions' => count($output), 'questions' => $output]);
    exit();
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswers = $input['answers'] ?? [];
    $score = 0;
    foreach ($allQuestions as $index => $q) {
        $qNum = $index + 1;
        if (isset($userAnswers[$qNum]) && $userAnswers[$qNum] === $q['answer']) {
            $score++;
        }
    }
    $total = count($allQuestions);
    echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'percentage' => round(($score/$total)*100, 2)]);
    exit();
}

if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $output = [];
    foreach ($allQuestions as $question) {
        $q = [
            'questionId' => $question['id'],
            'question' => $question['question'],
            'image' => $question['image'] ?? null,
            'sectionName' => $question['sectionName'],
            'correctAnswer' => $question['answer'],
            'explanation' => $question['explanation'] ?? '',
            'options' => []
        ];
        foreach ($question['options'] as $id => $text) {
            $q['options'][] = ['optionId' => $id, 'text' => $text];
        }
        $output[] = $q;
    }
    echo json_encode(['success' => true, 'questions' => $output]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Mathematics 2021</title>
    <style>
        /* CSS stays the same as your requested UI/UX */
        body { font-family: "Segoe UI", sans-serif; background-color: #fefdfc; margin: 0; padding: 0; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-top: 10px; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px; padding: 20px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 250px; }
        .question-header { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; }
        .question-id { color: #007aff; font-weight: 600; }
        .option-label { display: block; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.3s; }
        .option-label:hover { background: #f0f7ff; border-color: #007aff; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; padding: 12px 28px; border-radius: 12px; cursor: pointer; flex: 1; }
        .nav-btn:disabled { background: #b2d6be; cursor: default; }
        #btn-submit { background: #ff6b6b; }
        .question-nav { text-align: center; margin: 30px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; margin: 4px; width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; text-decoration: none; font-weight: bold; font-size: 13px; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .explanation-box { margin-top: 15px; padding: 15px; background: #e3f2fd; border-left: 5px solid #2196f3; border-radius: 4px; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; }
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
            <h1>Vector Learn — Mathematics 2021</h1>
            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">60:00</span>
        </div>

        <div id="exam-ui">
            <div class="question-container" id="q-container">
                <p style="text-align:center">Loading questions...</p>
            </div>

            <div class="navigation" id="nav-buttons">
                <button class="nav-btn" id="btn-prev" onclick="changeQ(-1)">← Previous</button>
                <button class="nav-btn" id="btn-next" onclick="changeQ(1)">Next →</button>
            </div>

            <div class="question-nav" id="q-nav"></div>
        </div>

        <div id="result-ui" style="display:none; padding: 25px;">
            <div class="question-container" style="text-align:center">
                <h2 id="res-score"></h2>
                <button class="nav-btn" onclick="showExplanations()" style="max-width:200px">View Explanations</button>
                <button class="nav-btn" onclick="location.reload()" style="max-width:200px; background:#6c757d">Retake Exam</button>
            </div>
            <div id="explanation-container"></div>
        </div>
    </div>

<script>
let questions = [];
let currentIdx = 0;
let answers = {};
let timeLeft = 60 * 60;

async function init() {
    const res = await fetch('?action=get_questions');
    const data = await res.json();
    if(data.success) {
        questions = data.questions;
        renderQuestion();
        renderNav();
        startTimer();
    }
}

function renderQuestion() {
    const q = questions[currentIdx];
    const qNum = currentIdx + 1;
    let html = `
        <div class="question-header">
            <span class="question-id">Question ${qNum}</span>
            <span class="question-section">${q.sectionName}</span>
        </div>
        <div class="question-text" style="font-weight:600; margin-bottom:20px; font-size:17px;">${q.question}</div>
        <form id="qForm">`;
    
    q.options.forEach(opt => {
        const checked = answers[qNum] === opt.optionId ? 'checked' : '';
        html += `
            <label class="option-label">
                <input type="radio" name="ans" value="${opt.optionId}" ${checked} onchange="saveAns('${opt.optionId}')">
                <span>${opt.optionId}. ${opt.text}</span>
            </label>`;
    });
    
    html += `</form>`;
    document.getElementById('q-container').innerHTML = html;
    
    const nextBtn = document.getElementById('btn-next');
    if (currentIdx === questions.length - 1) {
        nextBtn.innerHTML = "Submit Exam";
        nextBtn.id = "btn-submit";
        nextBtn.onclick = submitExam;
    } else {
        nextBtn.innerHTML = "Next →";
        nextBtn.id = "btn-next";
        nextBtn.onclick = () => changeQ(1);
    }
    document.getElementById('btn-prev').disabled = currentIdx === 0;
}

function saveAns(val) {
    answers[currentIdx + 1] = val;
    renderNav();
}

function renderNav() {
    let html = '';
    for(let i=0; i<questions.length; i++) {
        const active = i === currentIdx ? 'active' : '';
        const answered = answers[i+1] ? 'answered' : '';
        html += `<a href="javascript:void(0)" onclick="jumpTo(${i})" class="${active} ${answered}">${i+1}</a>`;
    }
    document.getElementById('q-nav').innerHTML = html;
}

function changeQ(dir) {
    currentIdx += dir;
    renderQuestion();
    renderNav();
}

function jumpTo(i) { currentIdx = i; renderQuestion(); renderNav(); }

function startTimer() {
    const timerEl = document.getElementById('countdown');
    const interval = setInterval(() => {
        timeLeft--;
        const m = Math.floor(timeLeft/60);
        const s = timeLeft%60;
        timerEl.innerHTML = `${m}:${s<10?'0':''}${s}`;
        if(timeLeft<=0) { clearInterval(interval); submitExam(); }
    }, 1000);
}

async function submitExam() {
    const res = await fetch('?action=submit', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers: answers})
    });
    const result = await res.json();
    document.getElementById('exam-ui').style.display = 'none';
    document.getElementById('result-ui').style.display = 'block';
    document.getElementById('info-box').innerHTML = "Exam Results";
    document.getElementById('res-score').innerHTML = `You Scored: ${result.score} / ${result.total} (${result.percentage}%)`;
}

async function showExplanations() {
    const res = await fetch('?action=get_explanations');
    const data = await res.json();
    let html = '<h3 style="margin:20px 25px">Corrections</h3>';
    
    data.questions.forEach((q, i) => {
        const uAns = answers[i+1] || "None";
        html += `
            <div class="question-container">
                <p><strong>Q${i+1}. ${q.question}</strong></p>
                <div class="option-label ${uAns === q.correctAnswer ? 'correct-answer-label' : 'user-answer-label'}">
                    Your Answer: ${uAns}
                </div>
                ${uAns !== q.correctAnswer ? `<div class="option-label correct-answer-label">Correct Answer: ${q.correctAnswer}</div>` : ''}
                <div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div>
            </div>`;
    });
    document.getElementById('explanation-container').innerHTML = html;
}

init();
</script>


<?php include 'footer.php'; ?></body>
</html>