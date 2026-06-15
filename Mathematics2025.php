
<?php
// MATHS 2025 CBT - Standalone PHP Page
// Data source: maths2025.json

// --- Configuration ---
$jsonFile = __DIR__ . '/maths2025.json';
$subjectTitle = 'Mathematics 2025';

// --- Functions to process the JSON file ---
function load_json_data($file) {
    if (!file_exists($file)) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . basename($file)]);
        exit();
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    
    if (!isset($data['questions']) || !is_array($data['questions'])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format. Expected a "questions" key.']);
        exit();
    }
    return $data['questions']; 
}

function prepare_exam_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        $questions[] = [
            'questionId' => (int) $q['id'],
            'question' => $q['question'],
            'image' => $q['image'] ?? null,
            'options' => $q['options'],
            'sectionName' => $q['sectionName'] ?? 'Mathematics', 
            'sectionId' => $q['sectionId'] ?? 1
        ];
    }
    return $questions;
}

function prepare_explanation_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        $questions[] = [
            'questionId' => (int) $q['id'],
            'question' => $q['question'],
            'correctAnswer' => $q['answer'] ?? null,
            'explanation' => $q['explanation'] ?? 'No explanation provided.',
            'image' => $q['image'] ?? null,
            'sectionName' => $q['sectionName'] ?? 'Mathematics', 
            'sectionId' => $q['sectionId'] ?? 1,
            'options' => $q['options']
        ];
    }
    return $questions;
}

// --- API ACTIONS ---
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (in_array($action, ['get_questions', 'get_explanations', 'submit', 'get_ai_help'])) {
    header('Content-Type: application/json');
    
    // AI Integration Bridge
    if ($action === 'get_ai_help') {
        $input = json_decode(file_get_contents('php://input'), true);
        $apiKey = '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'; 
        
        $prompt = "The student failed this math question. Question: {$input['question']}. Correct Answer: {$input['correctAnswer']}. Standard Explanation: {$input['explanation']}. Please explain, step-by-step, the logic to arrive at the correct answer and identify the fundamental topic the student must know.";

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]));
        echo curl_exec($ch);
        curl_close($ch);
        exit();
    }

    $raw_questions = load_json_data($jsonFile);
    
    if ($action === 'get_questions') {
        $questions = prepare_exam_questions($raw_questions);
        echo json_encode(['success' => true, 'totalQuestions' => count($questions), 'questions' => $questions]);
        exit();
    }

    if ($action === 'get_explanations') {
        $questionsWithDetails = prepare_explanation_questions($raw_questions);
        echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
        exit();
    }

    if ($action === 'submit') {
        $input = json_decode(file_get_contents('php://input'), true);
        $answers = $input['answers'] ?? [];
        $score = 0;
        $total = count($raw_questions);
        foreach ($raw_questions as $q) {
            $qId = (int)$q['id'];
            if (isset($answers[$qId]) && $answers[$qId] === $q['answer']) {
                $score++;
            }
        }
        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
        echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'percentage' => $percentage]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — <?php echo htmlspecialchars($subjectTitle); ?></title>
    <style>
        .container { max-width:1000px; width:100%; margin:40px auto; background:white; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.08); overflow-x:hidden; padding-bottom:20px; }
        .steps { display:flex; justify-content:space-between; padding:12px 20px; background:#f7f7f7; font-size:15px; border-bottom:1px solid #eaeaea; border-radius:40px; margin:20px auto; width:90%; }
        .steps span { flex:1; text-align:center; padding:6px; color:#aaa; }
        .steps .active { color:#007aff; font-weight:600; }
        .title { text-align:center; margin-top:10px; }
        .title h1 { font-size:30px; margin:0; color:#1e2a3a; }
        .title p { margin:8px 0 22px 0; font-size:15px; color:#555; }
        .form-box { background:linear-gradient(90deg, #4facfe, #43e97b); color:white; text-align:center; padding:18px 15px; font-size:18px; font-weight:600; margin:20px 25px 30px 25px; border-radius:12px; }
        .question-container { margin:0 25px 25px 25px; padding:20px; border:1px solid #e6eaf0; border-radius:12px; background:#fafafa; min-height:300px; }
        .explanation-view .question-container { background:#fff; border:1px solid #cceeff; margin-bottom:20px; }
        .question-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #e0e0e0; }
        .question-id { font-size:14px; color:#007aff; font-weight:600; }
        .question-section { font-size:12px; color:#999; background:#f0f0f0; padding:4px 8px; border-radius:6px; }
        .question-text { font-size:17px; font-weight:600; color:#243246; margin-bottom:20px; line-height:1.5; }
        .question-text img { max-width:100%; height:auto; display:block; margin:15px 0; border-radius:8px; border:1px solid #e0e0e0; }
        label.option-label { display:block; font-size:15px; color:#1e2a3a; margin-bottom:12px; cursor:pointer; user-select:none; padding:12px; border-radius:8px; border:1px solid #e0e0e0; transition:all 0.3s ease; }
        .explanation-view input[type="radio"] { display:none; }
        .explanation-view label.option-label { cursor:default; }
        label.option-label:hover { background:#f0f7ff; border-color:#007aff; }
        label.option-label input[type="radio"]:checked + span { color:#007aff; font-weight:600; }
        .navigation { display:flex; justify-content:space-between; margin:30px 25px; gap:10px; }
        .nav-btn { background:#43e97b; border:none; color:white; font-weight:bold; font-size:16px; padding:12px 28px; border-radius:12px; cursor:pointer; transition:background 0.3s ease; text-decoration:none; display:inline-flex; align-items:center; flex:1; justify-content:center; }
        .nav-btn:hover:not(.disabled) { background:#38c172; }
        .nav-btn.disabled { background:#b2d6be; cursor:default; pointer-events:none; }
        .question-nav { text-align:center; margin:30px 0 40px 0; padding:0 25px; }
        .question-nav a { display:inline-block; margin:0 4px 8px 4px; min-width:34px; height:34px; line-height:34px; border-radius:50%; background:#f7f7f7; color:#007aff; font-weight:600; font-size:14px; text-decoration:none; transition:0.3s; cursor:pointer; }
        .question-nav a.active { background:#007aff; color:white; }
        .question-nav a.answered { background:#4caf50; color:white; }
        .section-divider { margin:30px 25px 0 25px; padding:15px 20px; background:#f0f0f0; border-left:4px solid #007aff; border-radius:6px; font-size:14px; font-weight:600; color:#243246; }
        .error { background:#ffebee; color:#c62828; padding:20px; margin:20px 25px; border-radius:12px; border-left:4px solid #c62828; }
        .correct-answer-label { border:2px solid #4CAF50 !important; background-color:#e8f5e9 !important; font-weight:bold; }
        .user-answer-label { border:2px solid #FFC107 !important; background-color:#fff8e1 !important; font-weight:bold; }
        .explanation-box { margin-top:20px; padding:15px; border-radius:8px; background:#e3f2fd; border-left:5px solid #2196F3; }
        .ai-box { margin-top:15px; padding:15px; border-radius:8px; background:#fff3cd; border-left:5px solid #ffc107; font-size: 14px; }
        .subject-link { display:block; text-align:center; margin:30px; font-weight:bold; color:#007aff; text-decoration:none; }
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
        <h1>Vector Learn — <?php echo htmlspecialchars($subjectTitle); ?></h1>
        <p>Mastering mathematics, one step at a time.</p>
    </div>

    <div class="form-box" id="info-box">
        Time Remaining: <span id="countdown">--:--</span>
    </div>

    <div id="section-display"></div>

    <div class="question-container" id="q-container">
        <p style="text-align:center;">Loading questions...</p>
    </div>

    <div class="navigation" id="nav-buttons">
        <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
        <button class="nav-btn" id="btn-next" disabled>Next →</button>
    </div>

    <div class="question-nav" id="q-nav"></div>
    <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
</div>

<script>
let current = 1;
let allQuestions = [];
let answers = {};
let totalSeconds = 3600;
let timerInterval;

function htmlEscape(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function getAIPersonalizedHelp(qId, qText, correct, expl) {
    const box = document.getElementById('ai-box-' + qId);
    box.innerHTML = '<em>Thinking...</em>';
    try {
        const res = await fetch('?action=get_ai_help', {
            method: 'POST',
            body: JSON.stringify({ question: qText, correctAnswer: correct, explanation: expl })
        });
        const data = await res.json();
        const content = data.choices[0].message.content;
        box.innerHTML = '<strong>AI Tutor:</strong><br>' + content.replace(/\n/g, '<br>');
    } catch (e) {
        box.innerHTML = 'AI assistance currently unavailable.';
    }
}

function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;

    document.getElementById('section-display').innerHTML = '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';

    const qc = document.getElementById('q-container');
    let html = '<div class="question-header">';
    html += '<span class="question-id">Question ' + current + '</span>';
    html += '<span class="question-section">' + htmlEscape(qObj.sectionName) + '</span>';
    html += '</div>';
    
    html += '<div class="question-text">' + htmlEscape(qObj.question); 
    if (qObj.image) html += '<img src="' + htmlEscape(qObj.image) + '" alt="Diagram"/>';
    html += '</div><form id="qForm">';
    
    qObj.options.forEach(opt => {
        html += '<label class="option-label">';
        html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
        if (answers[qObj.questionId] === opt.optionId) html += 'checked'; 
        html += ' onchange="saveAnswer(\'' + qObj.questionId + '\', \'' + opt.optionId + '\')"/>';
        html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
        html += '</label>';
    });
    html += '</form>';
    qc.innerHTML = html;

    document.getElementById('btn-prev').disabled = (current <= 1);
    const btnNext = document.getElementById('btn-next');
    const navButtons = document.getElementById('nav-buttons');
    let existingSubmit = document.getElementById('btn-submit');

    if (current === allQuestions.length) {
        if (!existingSubmit) {
            const submitBtn = document.createElement('button');
            submitBtn.textContent = 'Submit Exam';
            submitBtn.id = 'btn-submit';
            submitBtn.className = 'nav-btn';
            submitBtn.style.background = '#ff6b6b';
            submitBtn.onclick = submitAnswers;
            navButtons.appendChild(submitBtn);
            btnNext.style.display = 'none';
        }
    } else {
        if (existingSubmit) existingSubmit.remove();
        btnNext.style.display = 'inline-flex';
        btnNext.disabled = false;
    }
}

function saveAnswer(qId, val) {
    answers[qId] = val;
    renderNav();
}

function renderNav() {
    let html = '';
    allQuestions.forEach((q, i) => {
        const num = i + 1;
        const cls = (num === current ? 'active ' : '') + (answers[q.questionId] ? 'answered' : '');
        html += '<a href="#" onclick="navigate(' + num + '); return false;" class="' + cls + '">' + num + '</a>';
    });
    document.getElementById('q-nav').innerHTML = html;
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

document.getElementById('btn-prev').onclick = () => { if(current > 1) navigate(current - 1); };
document.getElementById('btn-next').onclick = () => { if(current < allQuestions.length) navigate(current + 1); };

function startTimer() {
    timerInterval = setInterval(() => {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        document.getElementById('countdown').textContent = String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');
        if (totalSeconds <= 0) { clearInterval(timerInterval); submitAnswers(); }
        totalSeconds--;
    }, 1000);
}

async function loadQuestions() {
    const res = await fetch('?action=get_questions');
    const data = await res.json();
    if (data.success) {
        allQuestions = data.questions;
        renderQuestion();
        renderNav();
        startTimer();
    }
}

async function submitAnswers() {
    clearInterval(timerInterval);
    await fetch('?action=submit', { method: 'POST', body: JSON.stringify({ answers: answers }) });
    viewExplanations();
}

async function viewExplanations() {
    document.getElementById('q-container').style.display = 'none';
    document.getElementById('nav-buttons').style.display = 'none';
    document.getElementById('q-nav').style.display = 'none';
    document.getElementById('section-display').style.display = 'none';
    document.getElementById('info-box').innerHTML = 'Exam Review';

    const expContainer = document.getElementById('explanation-container');
    expContainer.style.display = 'block';
    
    const res = await fetch('?action=get_explanations');
    const data = await res.json();

    let html = '<div class="explanation-view">';
    data.questions.forEach((q, i) => {
        const isCorrect = answers[q.questionId] === q.correctAnswer;
        html += `
            <div class="question-container">
                <div class="question-header">
                    <span class="question-id">Q${i+1} — ${isCorrect ? '✅' : '❌'}</span>
                </div>
                <div class="question-text">${htmlEscape(q.question)}</div>
                ${q.options.map(opt => {
                    let cls = '';
                    if(opt.optionId === q.correctAnswer) cls = 'correct-answer-label';
                    else if(opt.optionId === answers[q.questionId]) cls = 'user-answer-label';
                    return `<label class="option-label ${cls}"><span>${opt.optionId}. ${htmlEscape(opt.text)}</span></label>`;
                }).join('')}
                <div class="explanation-box"><strong>Explanation:</strong> <p>${htmlEscape(q.explanation)}</p></div>
                ${!isCorrect ? `<div id="ai-box-${q.questionId}" class="ai-box"></div>` : ''}
            </div>`;
    });

    html += '<a href="https://vectorcbt.onrender.com/choose_subject.php" class="subject-link">← Back to Subjects</a>';
    html += '</div>';
    expContainer.innerHTML = html;

    data.questions.forEach(q => {
        if (answers[q.questionId] !== q.correctAnswer) {
            getAIPersonalizedHelp(q.questionId, q.question, q.correctAnswer, q.explanation);
        }
    });
    window.scrollTo(0,0);
}

window.onload = loadQuestions;
</script>

<?php include 'footer2.php'; ?>
</body>
</html>
