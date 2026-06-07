<?php
// Chemistry 2021 CBT - Standalone PHP Page

// JSON file path
$jsonFile = __DIR__ . '/chemistry2021.json';

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

    // Check for both array structure and object wrapper structure
    if (is_array($data) && isset($data[0]['id']) || (isset($data['questions']) && is_array($data['questions']))) {
        $questions = $data['questions'] ?? $data;
    } else {
        return ['error' => 'Invalid JSON format. Expected an array or {"questions":[...]} with valid question objects.'];
    }

    if (empty($questions)) {
        return ['error' => 'No questions found in the JSON file. The array is empty.'];
    }

    // Normalize structure (ensures 'sectionName' and 'sectionId' exist)
    $final = [];
    foreach ($questions as $q) {
        $q['sectionName'] = $q['sectionName'] ?? "Core Chemistry";
        $q['sectionId']  = $q['sectionId']    ?? "CHEM";
        $final[] = $q;
    }

    return ['questions' => $final];
}

// Handle actions
$action = $_GET['action'] ?? $_POST['action'] ?? null;
$jsonResult = loadJsonData($jsonFile);
$allQuestions = $jsonResult['questions'] ?? [];

// If there's an error and we are explicitly fetching data, return the error as JSON
if (isset($jsonResult['error']) && $action) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $jsonResult['error']]);
    exit();
}

// --- ACTION: get questions ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');

    $out = [];
    foreach ($allQuestions as $q) {
        $questionText = $q['text'] ?? $q['question'] ?? 'Missing Question Text'; 
        
        $row = [
            'questionId'  => $q['id'],
            'question'    => $questionText,
            'image'       => $q['image_name'] ?? $q['image'] ?? null,
            'sectionName' => $q['sectionName'],
            'sectionId'   => $q['sectionId'],
            'options'     => []
        ];
        // Populate options array
        foreach ($q['options'] as $k => $v) {
            $row['options'][] = ['optionId' => $k, 'text' => $v];
        }
        $out[] = $row;
    }

    echo json_encode([
        'success' => true,
        'totalQuestions' => count($out),
        'questions' => $out
    ]);
    exit();
}

// --- ACTION: get explanations ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');

    $out = [];
    foreach ($allQuestions as $q) {
        $questionText = $q['text'] ?? $q['question'] ?? 'Missing Question Text'; 
        $row = [
            'questionId'     => $q['id'],
            'question'        => $questionText,
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

// --- ACTION: submit ---
if ($action === 'submit') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];

    $map = [];
    foreach ($allQuestions as $i => $q) {
        $id = $q['id'] ?? ($i + 1);
        $map[$id] = $q;
    }

    $score = 0;
    $total = count($map);

    foreach ($answers as $id => $ans) {
        if (isset($map[$id]) && $map[$id]['answer'] === $ans) {
            $score++;
        }
    }

    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => $total ? round(($score/$total)*100,2) : 0
    ]);
    exit();
}

$initialError = $jsonResult['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Chemistry 2021</title>

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
.explanation-view .question-container { background: #ffffff; border: 1px solid #cceeff; margin-bottom: 20px; }
.question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
.question-id { font-size: 14px; color: #007aff; font-weight: 600; }
.question-section { font-size: 12px; color: #999; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; }
.question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
label.option-label input[type="radio"]:checked + span { color: #007aff; font-weight: 600; }
label.option-label input[type="radio"] { margin-right: 10px; cursor: pointer; accent-color: #007aff; }
.explanation-view input[type="radio"] { display: none; }
.explanation-view label.option-label { cursor: default; }
.explanation-view label.option-label:hover { background: initial; border-color: #e0e0e0; }
.correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
.user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
.explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
.explanation-box p { margin: 0; font-size: 14px; line-height: 1.6; }
.navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
.nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
.nav-btn:hover:not(.disabled) { background: #38c172; }
.nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
.nav-button-group { display: flex; justify-content: center; gap: 10px; margin-top: 30px; margin-bottom: 30px; }
.question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: auto; }
.question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; user-select: none; transition: background-color 0.3s, color 0.3s; cursor: pointer; }
.question-nav a.active { background: #007aff; color: white; }
.question-nav a:hover { background: #e6f0ff; }
.question-nav a.answered { background: #4caf50; color: white; }
.section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600; color: #243246; }
.loading { text-align: center; padding: 40px; font-size: 18px; color: #666; }
.error { background: #ffebee; color: #c62828; padding: 20px; margin: 20px 25px; border-radius: 12px; border-left: 4px solid #c62828; }
.question-image { max-width: 100%; height: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e0e0e0; }
@media (max-width: 768px) { .container { margin: 20px; } .nav-button-group .nav-btn { flex: 0 0 auto; } }
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
            <h1>Vector Learn — Chemistry 2021</h1>
            <p>Growing in knowledge, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <?php if ($initialError): ?>
        <div class="question-container" id="q-container">
            <div class="error">
                <strong>CRITICAL JSON ERROR:</strong> The quiz cannot load.
                <p><?php echo $initialError; ?></p>
                <p>Fix your <code>chemistry2021.json</code> file.</p>
                <button class="nav-btn" onclick="window.location.reload()" style="background:#007aff; margin-top: 15px;">Attempt Reload</button>
            </div>
        </div>
        <?php else: ?>

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
        <?php endif; ?>
    </div>

<script>
    const baseUrl = '<?php echo $_SERVER['PHP_SELF']; ?>';
    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60; 

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
            if (!data.success) {
                throw new Error(data.error || 'Unknown API error.');
            }
            return data;
        } catch (error) {
            showError(`Data Fetch Error (${action}): ${error.message}`);
            return null;
        }
    }

    async function loadQuestions() {
        const data = await fetchJson('get_questions');
        if (data && data.questions.length > 0) {
            allQuestions = data.questions;
            if (current > allQuestions.length) current = 1;

            document.getElementById('q-container').innerHTML = '';
            document.getElementById('section-display').innerHTML = 
                `<div class="section-divider">Subject: ${allQuestions[0].sectionName}</div>`;
            renderQuestion();
            renderNav();
            startTimer();
        } else if (data) {
            showError(data.error || 'The file loaded, but contains no questions.');
        }
    }

    function showError(message) {
        document.getElementById('q-container').innerHTML = '<div class="error">' + message + '</div>';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Q' + qObj.questionId + '</span>';
        html += '<span class="question-section">' + qObj.sectionName + '</span>';
        html += '</div>';
        html += `<div class="question-text">${htmlEscape(qObj.question)}</div>`;
        
        if (qObj.image) {
            html += `<img src="${htmlEscape(qObj.image)}" alt="Diagram for question ${qObj.questionId}" class="question-image" />`;
        }

        html += '<form id="qForm">';
        
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            if (answers[qObj.questionId] === opt.optionId) html += 'checked';
            html += ' /><span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        
        html += '</form>';
        qc.innerHTML = html;

        updateNavButtons();
    }

    function updateNavButtons() {
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const nav = document.getElementById('nav-buttons');
        const existingSubmit = document.getElementById('btn-submit');

        btnPrev.disabled = (current <= 1);
        btnNext.disabled = false;
        
        if (current === allQuestions.length) {
            btnNext.style.display = 'none';
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                nav.appendChild(submitBtn);
            }
        } else {
            btnNext.style.display = 'inline-flex';
            if (existingSubmit) existingSubmit.remove();
        }
    }


    function renderNav() {
        let html = '';
        for (let i = 1; i <= allQuestions.length; i++) {
            const qObj = allQuestions[i-1];
            const answered = answers[qObj.questionId] ? 'answered' : ''; 
            const active = i === current ? 'active' : '';
            html += `<a href="#" onclick="navigate(${i}); return false;" class="${active} ${answered}">${i}</a>`;
        }
        document.getElementById('q-nav').innerHTML = html;
    }
    
    function navigate(num) {
        if (!isReviewMode) saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').addEventListener('click', () => {
        if (!isReviewMode) saveAnswer();
        if (current > 1) {
            current--;
            renderQuestion();
            renderNav();
        }
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        if (!isReviewMode) saveAnswer();
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
        
        if (selected) answers[qObj.questionId] = selected; 
        else delete answers[qObj.questionId]; 
        
        renderNav();
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
        if (!confirm('Are you sure you want to submit the exam?')) return;
        
        saveAnswer();
        clearInterval(timerInterval);
        
        const inputAnswers = {};
        for (let i = 0; i < allQuestions.length; i++) {
            const id = allQuestions[i].questionId;
            inputAnswers[id] = answers[id];
        }

        const data = await fetchJson('submit', 'POST', { answers: inputAnswers });

        if (data) {
            document.getElementById('q-container').style.display = 'none';
            document.getElementById('nav-buttons').style.display = 'none';
            document.getElementById('q-nav').style.display = 'none';
            document.getElementById('section-display').style.display = 'none';
            document.getElementById('info-box').innerHTML = 'Exam Completed!';

            displayResults(data.score, data.total, data.percentage);
        }
    }
    
    function displayResults(score, total, percentage) {
        const container = document.querySelector('.container');
        const resultBox = document.createElement('div');
        resultBox.className = 'question-container';
        resultBox.id = 'results-view';
        resultBox.style.textAlign = 'center';

        resultBox.innerHTML = `
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Results</div>
            <div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">
                ${score} / ${total}
            </div>
            <div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">
                ${percentage}%
            </div>
            <div style="font-size: 16px; color: #555; margin-bottom: 30px;">
                You answered ${score} out of ${total} questions correctly.
            </div>
            <div class="nav-button-group">
                <button class="nav-btn" style="background:#007aff;" onclick="viewExplanations()">View Explanation</button>
                <button class="nav-btn" onclick="location.reload()">🔄 Retake Exam</button>
            </div>
        `;
        container.appendChild(resultBox);
    }

    async function viewExplanations() {
        const data = await fetchJson('get_explanations');
        if (!data) return;

        isReviewMode = true;
        document.getElementById('results-view').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Explanations';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';

        let html = '<div class="explanation-view">';

        data.questions.forEach((qObj) => {
            const questionId = qObj.questionId; 
            const userAnswer = answers[questionId]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += '<span class="question-id">Q' + qObj.questionId + ' — ' + (isCorrect ? 'Correct ✅' : 'Incorrect ❌') + '</span>';
            html += '<span class="question-section">' + qObj.sectionName + '</span>';
            html += '</div>';
            html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
            
            if (qObj.image) {
                html += `<img src="${htmlEscape(qObj.image)}" alt="Diagram for question ${qObj.questionId}" class="question-image" />`;
            }

            qObj.options.forEach(opt => {
                let labelClass = '';
                if (opt.optionId === qObj.correctAnswer) {
                    labelClass = 'correct-answer-label';
                } else if (opt.optionId === userAnswer && !isCorrect) {
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
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn" onclick="location.reload()" style="background:#007aff;">🔄 Retake Exam</button>';
        html += '</div>';

        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }

    loadQuestions();
</script>


<?php include 'footer.php'; ?></body>
</html>