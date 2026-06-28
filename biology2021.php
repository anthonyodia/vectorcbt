<?php
// Biology 2021 CBT - Standalone PHP Page

// JSON file path
$jsonFile = __DIR__ . '/biology2021.json';

// --- Load & Validate JSON ---
function loadJsonData($file) {
    if (!file_exists($file)) {
        return ['error' => 'Question file (' . basename($file) . ') not found. Check if the file name is correct and in the right folder.'];
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => "JSON Syntax Error: " . json_last_error_msg()];
    }

    if (is_array($data) && isset($data[0]['id']) || (isset($data['questions']) && is_array($data['questions']))) {
        $questions = $data['questions'] ?? $data;
    } else {
        return ['error' => 'Invalid JSON format. Expected an array or {"questions":[...]} with valid question objects.'];
    }

    if (empty($questions)) {
        return ['error' => 'No questions found in the JSON file.'];
    }

    $final = [];
    foreach ($questions as $q) {
        $q['sectionName'] = $q['sectionName'] ?? "WAEC Biology 2021";
        $q['sectionId']  = $q['sectionId']    ?? "BIO";
        $final[] = $q;
    }

    return ['questions' => $final];
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$jsonResult = loadJsonData($jsonFile);
$allQuestions = $jsonResult['questions'] ?? [];

if (isset($jsonResult['error']) && $action) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $jsonResult['error']]);
    exit();
}

if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $out = [];
    foreach ($allQuestions as $q) {
        $row = [
            'questionId'  => $q['id'],
            'question'    => $q['question'] ?? $q['text'] ?? 'Missing Question Text',
            'image'       => $q['image_name'] ?? $q['image'] ?? null,
            'sectionName' => $q['sectionName'],
            'sectionId'   => $q['sectionId'],
            'options'     => []
        ];
        foreach ($q['options'] as $k => $v) {
            $row['options'][] = ['optionId' => $k, 'text' => $v];
        }
        $out[] = $row;
    }
    echo json_encode(['success' => true, 'totalQuestions' => count($out), 'questions' => $out]);
    exit();
}

if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $out = [];
    foreach ($allQuestions as $q) {
        $row = [
            'questionId'     => $q['id'],
            'question'        => $q['question'] ?? $q['text'] ?? '',
            'image'          => $q['image_name'] ?? $q['image'] ?? null,
            'sectionName'    => $q['sectionName'],
            'sectionId'      => $q['sectionId'],
            'correctAnswer'  => $q['answer'],
            'explanation'    => $q['explanation'] ?? '',
            'options'         => []
        ];
        foreach ($q['options'] as $k => $v) {
            $row['options'][] = ['optionId' => $k, 'text' => $v];
        }
        $out[] = $row;
    }
    echo json_encode(['success' => true, 'questions' => $out]);
    exit();
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];
    $map = [];
    foreach ($allQuestions as $i => $q) { $id = $q['id'] ?? ($i + 1); $map[$id] = $q; }
    $score = 0;
    $total = count($map);
    foreach ($answers as $id => $ans) {
        if (isset($map[$id]) && $map[$id]['answer'] === $ans) { $score++; }
    }
    echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'percentage' => $total ? round(($score/$total)*100,2) : 0]);
    exit();
}
$initialError = $jsonResult['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Biology 2021</title>
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
.question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; white-space: pre-line; }
label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
label.option-label input[type="radio"]:checked + span { color: #007aff; font-weight: 600; }
label.option-label input[type="radio"] { margin-right: 10px; cursor: pointer; accent-color: #007aff; }
.correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
.user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
.explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
.navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
.nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
.nav-btn:hover:not(.disabled) { background: #38c172; }
.nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
.question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; }
.question-nav a { display: inline-block; margin: 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; }
.question-nav a.active { background: #007aff; color: white; }
.question-nav a.answered { background: #4caf50; color: white; }
.section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600; }
.question-image { max-width: 100%; height: auto; margin: 15px 0; border-radius: 8px; border: 1px solid #ddd; }
.error { background: #ffebee; color: #c62828; padding: 20px; margin: 20px 25px; border-radius: 12px; border-left: 4px solid #c62828; }
</style>
</head>
<body>

<?php include 'header.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — Biology 2021</h1>
            <p>Mastering biological concepts, one cell at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <?php if ($initialError): ?>
        <div class="error">
            <strong>ERROR:</strong> <?php echo $initialError; ?>
        </div>
        <?php else: ?>

        <div id="section-display"></div>
        <div class="question-container" id="q-container">
            <p style="text-align:center; padding:40px;">Loading Biology Questions...</p>
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
    const baseUrl = '<?php echo $_SERVER['PHP_SELF']; ?>';
    let current = 1;
    let duration = 50; 
    let allQuestions = [];
    let answers = {};
    let isReviewMode = false;

    async function fetchJson(action, method = 'GET', body = null) {
        const options = { method };
        if (body) {
            options.headers = { 'Content-Type': 'application/json' };
            options.body = JSON.stringify(body);
        }
        try {
            const response = await fetch(baseUrl + `?action=${action}`, options);
            const data = await response.json();
            if (!data.success) throw new Error(data.error);
            return data;
        } catch (error) {
            document.getElementById('q-container').innerHTML = `<div class="error">${error.message}</div>`;
            return null;
        }
    }

    async function loadQuestions() {
        const data = await fetchJson('get_questions');
        if (data && data.questions.length > 0) {
            allQuestions = data.questions;
            document.getElementById('section-display').innerHTML = `<div class="section-divider">Topic: ${allQuestions[0].sectionName}</div>`;
            renderQuestion();
            renderNav();
            startTimer();
        }
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        const qc = document.getElementById('q-container');
        let html = `<div class="question-header">
                        <span class="question-id">Question ${qObj.questionId}</span>
                        <span class="question-section">${qObj.sectionName}</span>
                    </div>
                    <div class="question-text">${qObj.question}</div>`;
        
        if (qObj.image) {
            html += `<img src="${qObj.image}" class="question-image" alt="Biology Diagram" />`;
        }

        html += '<form id="qForm">';
        qObj.options.forEach(opt => {
            html += `<label class="option-label">
                        <input type="radio" name="answer" value="${opt.optionId}" ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''}>
                        <span>${opt.optionId}. ${opt.text}</span>
                    </label>`;
        });
        html += '</form>';
        qc.innerHTML = html;
        updateButtons();
    }

    function updateButtons() {
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const existingSubmit = document.getElementById('btn-submit');
        
        btnPrev.disabled = (current <= 1);
        btnPrev.classList.toggle('disabled', current <= 1);

        if (current === allQuestions.length) {
            btnNext.style.display = 'none';
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Biology Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                document.getElementById('nav-buttons').appendChild(submitBtn);
            }
        } else {
            btnNext.style.display = 'inline-flex';
            btnNext.disabled = false;
            if (existingSubmit) existingSubmit.remove();
        }
    }

    function renderNav() {
        let html = '';
        allQuestions.forEach((q, i) => {
            const num = i + 1;
            const cls = (num === current ? 'active' : '') + (answers[q.questionId] ? ' answered' : '');
            html += `<a onclick="navigate(${num})" class="${cls}">${num}</a>`;
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    function navigate(num) {
        if (num < 1 || num > allQuestions.length) return;
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0,0);
    }

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form) return;
        const selected = form.querySelector('input[name="answer"]:checked')?.value;
        if (selected) answers[allQuestions[current-1].questionId] = selected;
    }

    // Attach listeners directly to avoid closure issues
    document.getElementById('btn-prev').onclick = () => {
        if (current > 1) navigate(current - 1);
    };

    document.getElementById('btn-next').onclick = () => {
        if (current < allQuestions.length) navigate(current + 1);
    };

    let totalSeconds = duration * 60;
    function startTimer() {
        const timer = setInterval(() => {
            if (totalSeconds <= 0) { clearInterval(timer); submitAnswers(); }
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            document.getElementById('countdown').textContent = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
            totalSeconds--;
        }, 1000);
    }

    async function submitAnswers() {
        if (!isReviewMode && !confirm('Submit your Biology responses?')) return;
        saveAnswer();
        const data = await fetchJson('submit', 'POST', { answers });
        if (data) {
            document.getElementById('q-container').style.display = 'none';
            document.getElementById('nav-buttons').style.display = 'none';
            document.getElementById('q-nav').style.display = 'none';
            document.getElementById('section-display').style.display = 'none';
            
            const res = document.createElement('div');
            res.className = 'question-container';
            res.style.textAlign = 'center';
            res.innerHTML = `<h2>Exam Results</h2>
                             <div style="font-size:48px; color:#007aff; font-weight:bold;">${data.score} / ${data.total}</div>
                             <p>${data.percentage}% Score</p>
                             <button class="nav-btn" onclick="viewExplanations()" style="background:#007aff; width:auto; margin:10px;">Review Explanations</button>
                             <button class="nav-btn" onclick="location.reload()" style="width:auto; margin:10px;">Retake</button>`;
            document.querySelector('.container').appendChild(res);
        }
    }

    async function viewExplanations() {
        const data = await fetchJson('get_explanations');
        if (!data) return;
        isReviewMode = true;
        document.querySelector('.container > div:last-child').style.display = 'none';
        const container = document.getElementById('explanation-container');
        container.style.display = 'block';
        
        let html = '';
        data.questions.forEach(q => {
            const userAns = answers[q.questionId];
            const correct = userAns === q.correctAnswer;
            html += `<div class="question-container">
                        <div class="question-header">
                            <span class="question-id">Q${q.questionId} - ${correct ? '✅' : '❌'}</span>
                        </div>
                        <div class="question-text">${q.question}</div>`;
            if (q.image) html += `<img src="${q.image}" class="question-image" />`;
            q.options.forEach(opt => {
                let cls = opt.optionId === q.correctAnswer ? 'correct-answer-label' : (opt.optionId === userAns ? 'user-answer-label' : '');
                html += `<label class="option-label ${cls}"><span>${opt.optionId}. ${opt.text}</span></label>`;
            });
            html += `<div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div></div>`;
        });
        container.innerHTML = html + `<button class="nav-btn" onclick="location.reload()" style="margin-bottom:20px;">Return Home</button>`;
    }

    loadQuestions();
</script>


<?php include 'footer2.php'; ?></body>
</html>