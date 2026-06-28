<?php
// Computer Studies 2021 CBT - Standalone PHP Page
$jsonFile = __DIR__ . '/computer2021.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions for the Exam View ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    // Adapt flat structure to the UI's expected format
    $questions = [];
    foreach ($data['questions'] as $q) {
        $cleanQ = $q;
        unset($cleanQ['answer']);
        unset($cleanQ['explanation']);
        // Map keys to match UI expectations
        $cleanQ['questionId'] = $q['id'];
        $cleanQ['sectionName'] = "Objective"; 
        $cleanQ['sectionId'] = 1;
        
        // Convert simple array of options to the UI's expected object format
        $opts = [];
        $labels = ['A', 'B', 'C', 'D'];
        foreach ($q['options'] as $index => $text) {
            $opts[] = ['optionId' => $index, 'label' => $labels[$index], 'text' => $text];
        }
        $cleanQ['options'] = $opts;
        $questions[] = $cleanQ;
    }
    
    echo json_encode(['success' => true, 'totalQuestions' => count($questions), 'questions' => $questions]);
    exit();
}

// --- ACTION 2: Review/Explanations ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents($jsonFile), true);
    $questionsWithDetails = [];
    $labels = ['A', 'B', 'C', 'D'];
    foreach ($data['questions'] as $q) {
        $q['questionId'] = $q['id'];
        $q['sectionName'] = "Objective";
        $q['correctAnswer'] = $q['answer'];
        $opts = [];
        foreach ($q['options'] as $index => $text) {
            $opts[] = ['optionId' => $index, 'text' => $text];
        }
        $q['options'] = $opts;
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
    foreach ($data['questions'] as $q) {
        if (isset($userAnswers[$q['id']]) && (int)$userAnswers[$q['id']] === (int)$q['answer']) {
            $score++;
        }
    }
    $total = count($data['questions']);
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => round(($score / $total) * 100, 2)
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Computer Studies 2021</title>
    <style>
        /* [Keeping your exact CSS styles from the template provided] */
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
        .q-img { max-width: 100%; height: auto; margin-bottom: 15px; border-radius: 8px; border: 1px solid #ddd; }
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
        .section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600; color: #243246; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin-top: 30px; margin-bottom: 30px; }
        .loading { text-align: center; padding: 40px; color: #666; }
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
            <h1>Vector Learn — Computer 2021</h1>
            <p>WAEC Practice Module</p>
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
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    let current = 1;
    let allQuestions = [];
    let answers = {};
    let timerInterval;
    let explanationData = null;

    async function loadQuestions() {
        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            if (data.success) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer(40);
            }
        } catch (error) { console.error(error); }
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        // Display section divider
        document.getElementById('section-display').innerHTML = 
            '<div class="section-divider">Section: ' + qObj.sectionName + '</div>';

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header">';
        html += '<span class="question-id">Question ' + current + '</span>';
        html += '<span class="question-section">' + qObj.sectionName + '</span>';
        html += '</div>';
        
        if(qObj.instruction) html += '<p style="font-style:italic; color:#666">' + qObj.instruction + '</p>';
        html += '<div class="question-text">' + qObj.question + '</div>';

        // Fix for Images
        if(qObj.image_name) {
            html += '<img src="' + qObj.image_name + '" class="q-img" />';
        }

        html += '<form id="qForm">';
        const labels = ['A', 'B', 'C', 'D'];
        qObj.options.forEach((opt, idx) => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ';
            // Store by question id to persist across navigation
            if (answers[qObj.questionId] == opt.optionId) html += 'checked';
            html += ' />';
            html += '<span>' + labels[idx] + '. ' + opt.text + '</span>';
            html += '</label>';
        });
        html += '</form>';
        qc.innerHTML = html;

        // Handle Navigation States
        document.getElementById('btn-prev').disabled = (current <= 1);
        document.getElementById('btn-prev').classList.toggle('disabled', current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        const existingSubmit = document.getElementById('btn-submit');
        
        if (current === allQuestions.length) {
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                document.getElementById('nav-buttons').appendChild(submitBtn);
            }
            btnNext.style.display = 'none';
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
            btnNext.disabled = false;
        }
    }

    function saveAnswer() {
        const form = document.getElementById('qForm');
        const qObj = allQuestions[current - 1];
        if (form && form.answer.value !== "") {
            answers[qObj.questionId] = form.answer.value;
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
        if (current > 1) navigate(current - 1);
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        if (current < allQuestions.length) navigate(current + 1);
    });

    function renderNav() {
        let html = '';
        allQuestions.forEach((q, i) => {
            const answered = answers[q.questionId] !== undefined ? 'answered' : '';
            const active = (i + 1) === current ? 'active' : '';
            html += '<a onclick="navigate(' + (i + 1) + ')" class="' + active + ' ' + answered + '">' + (i + 1) + '</a>';
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    function startTimer(mins) {
        let totalSeconds = mins * 60;
        timerInterval = setInterval(() => {
            let m = Math.floor(totalSeconds / 60);
            let s = totalSeconds % 60;
            document.getElementById('countdown').textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            if (totalSeconds-- <= 0) { clearInterval(timerInterval); submitAnswers(); }
        }, 1000);
    }

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);
        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=submit', {
            method: 'POST',
            body: JSON.stringify({ answers: answers })
        });
        const result = await response.json();
        displayResults(result);
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
        resultBox.innerHTML = '<div style="font-size: 32px; font-weight: bold;">Results</div>' +
            '<div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">' + result.score + '/' + result.total + '</div>' +
            '<div style="font-size: 24px; color: #43e97b;">' + result.percentage + '%</div>' +
            '<div class="nav-button-group">' +
            '<button class="nav-btn" style="background:#6c757d" onclick="location.reload()">🏠 Home</button>' +
            '<button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">View Explanation</button>' +
            '</div>';
        container.appendChild(resultBox);
    }

    async function viewExplanations() {
        document.getElementById('results-view').style.display = 'none';
        const expContainer = document.getElementById('explanation-container');
        expContainer.style.display = 'block';
        expContainer.innerHTML = '<p class="loading">Loading Review...</p>';

        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_explanations');
        const data = await response.json();
        
        let html = '<div class="explanation-view">';
        const labels = ['A', 'B', 'C', 'D'];
        data.questions.forEach((q, idx) => {
            const userAns = answers[q.questionId];
            const isCorrect = userAns == q.correctAnswer;
            html += '<div class="question-container">';
            html += '<div class="question-header"><span class="question-id">Q' + (idx+1) + ' — ' + (isCorrect ? '✅' : '❌') + '</span></div>';
            html += '<div class="question-text">' + q.question + '</div>';
            
            q.options.forEach((opt, oIdx) => {
                let cls = '';
                if (oIdx == q.correctAnswer) cls = 'correct-answer-label';
                else if (oIdx == userAns) cls = 'user-answer-label';
                html += '<div class="option-label ' + cls + '">' + labels[oIdx] + '. ' + opt.text + '</div>';
            });
            html += '<div class="explanation-box"><strong>Explanation:</strong> ' + q.explanation + '</div></div>';
        });
        html += '<button class="nav-btn" onclick="location.reload()">Finish Review</button></div>';
        expContainer.innerHTML = html;
        window.scrollTo(0,0);
    }

    loadQuestions();
</script>


<?php include 'footer2.php'; ?></body>
</html>