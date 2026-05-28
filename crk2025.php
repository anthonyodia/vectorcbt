<?php
// CRK 2025 CBT - Standalone PHP Page
// Data source: crk2025.json

// --- Configuration ---
$jsonFile = __DIR__ . '/crk2025.json'; 
$subjectTitle = 'CRK 2025'; 

// --- Functions to process the JSON file ---

function load_json_data($file) {
    if (!file_exists($file)) {
        http_response_code(404);
        echo "Error: Question file not found: " . basename($file);
        exit();
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    
    // Support 'questions' key or top-level array
    if (is_array($data) && isset($data['questions']) && is_array($data['questions'])) {
        $questions = $data['questions'];
    } elseif (is_array($data)) {
        $questions = $data;
    } else {
        $json_error = json_last_error_msg();
        http_response_code(500);
        echo "Error: Invalid JSON format. PHP JSON Error: " . $json_error;
        exit();
    }
    
    if (empty($questions)) {
        http_response_code(500);
        echo "Error: Question bank is empty.";
        exit();
    }
    
    return $questions; 
}

function prepare_exam_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        if (!isset($q['id']) || !isset($q['question']) || !isset($q['options'])) continue; 
        
        $question = [
            'questionId' => (int) $q['id'],
            'question' => $q['question'],
            'image' => $q['image'] ?? null,
            'table' => $q['table'] ?? null,
            'options' => [],
            'sectionName' => $q['sectionName'] ?? 'General CRK',
            'sectionId' => $q['sectionId'] ?? 1
        ];
        
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

function prepare_explanation_questions($raw_questions) {
    $questions = [];
    foreach ($raw_questions as $q) {
        if (!isset($q['id']) || !isset($q['question']) || !isset($q['options'])) continue;

        $question = [
            'questionId' => (int) $q['id'],
            'question' => $q['question'],
            'correctAnswer' => $q['answer'] ?? null,
            'explanation' => $q['explanation'] ?? 'No explanation provided.',
            'image' => $q['image'] ?? null,
            'table' => $q['table'] ?? null,
            'sectionName' => $q['sectionName'] ?? 'General CRK',
            'sectionId' => $q['sectionId'] ?? 1,
            'options' => []
        ];
        
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

if (in_array($action, ['get_questions', 'get_explanations', 'submit'])) {
    header('Content-Type: application/json');
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
        $correct_answers_map = [];
        foreach ($raw_questions as $question) {
            if (isset($question['id']) && isset($question['answer'])) {
                $correct_answers_map[(int)$question['id']] = $question['answer'];
            }
        }
        $score = 0;
        $total = count($correct_answers_map);
        foreach ($answers as $qId => $userAnswer) {
            $qId = (int)$qId; 
            if (isset($correct_answers_map[$qId]) && $userAnswer === $correct_answers_map[$qId]) {
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
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow-x: hidden; padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; padding: 6px; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; margin: 0; color: #1e2a3a; }
        .title p { margin: 8px 0 22px 0; font-size: 15px; color: #555; }
        .form-box { background: linear-gradient(90deg, #4facfe, #00f2fe); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 20px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 300px; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .question-id { font-size: 14px; color: #007aff; font-weight: 600; }
        .question-section { font-size: 12px; color: #999; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; }
        .question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        label.option-label input[type="radio"] { margin-right: 10px; accent-color: #007aff; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #4facfe; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:hover:not(.disabled) { background: #007aff; }
        .nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; margin: 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; transition: 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; font-size: 14px; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
        .export-btn { background: #6f42c1 !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="steps">
            <span>Step 1: Your Details</span>
            <span>Step 2: Pick Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — <?php echo htmlspecialchars($subjectTitle); ?></h1>
            <p>Walking in the light of the Word, one question at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <div id="section-display"></div>

        <div class="question-container" id="q-container">
            <p class="loading" style="text-align:center;">Loading questions...</p>
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
    
    function htmlEscape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    let current = 1;
    let duration = 45; 
    let allQuestions = [];
    let answers = {};
    let currentSection = null;
    let explanationData = null; 
    let totalSeconds = duration * 60;
    let timerInterval;

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        if (currentSection !== qObj.sectionId) {
            currentSection = qObj.sectionId;
            document.getElementById('section-display').innerHTML = 
                '<div style="margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600;">Section: ' + htmlEscape(qObj.sectionName) + '</div>';
        }

        const qc = document.getElementById('q-container');
        let html = '<div class="question-header"><span class="question-id">Q' + qObj.questionId + '</span><span class="question-section">' + htmlEscape(qObj.sectionName) + '</span></div>';
        html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
        html += '<form id="qForm">';
        qObj.options.forEach(opt => {
            html += '<label class="option-label">';
            html += '<input type="radio" name="answer" value="' + opt.optionId + '" ' + (answers[qObj.questionId] === opt.optionId ? 'checked' : '') + ' />';
            html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
            html += '</label>';
        });
        html += '</form>';
        qc.innerHTML = html;

        document.getElementById('btn-prev').disabled = (current <= 1);
        const btnNext = document.getElementById('btn-next');
        const navButtons = document.getElementById('nav-buttons');
        let existingSubmit = document.getElementById('btn-submit');

        btnNext.disabled = false; 
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
        }
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

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form || !allQuestions[current - 1]) return;
        const selected = form.answer.value || null;
        if (selected) answers[allQuestions[current - 1].questionId] = selected;
    }

    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').addEventListener('click', () => navigate(current - 1));
    document.getElementById('btn-next').addEventListener('click', () => navigate(current + 1));
    document.addEventListener('change', (e) => { if (e.target.name === 'answer') { saveAnswer(); renderNav(); } });

    function updateTimer() {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        document.getElementById('countdown').textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        if (totalSeconds <= 0) { clearInterval(timerInterval); submitAnswers(); } else { totalSeconds--; }
    }

    async function loadQuestions() {
        try {
            const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_questions');
            const data = await response.json();
            if (data.success) {
                allQuestions = data.questions;
                renderQuestion(); renderNav();
                timerInterval = setInterval(updateTimer, 1000);
            }
        } catch (e) { console.error("Load error", e); }
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
            if (result.success) displayResults(result);
        } catch (e) { alert("Submission failed."); }
    }

    function displayResults(result) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';
        
        let rb = document.createElement('div');
        rb.className = 'question-container';
        rb.style.textAlign = 'center';
        rb.innerHTML = '<div style="font-size: 32px; font-weight: bold;">Results</div>' +
                       '<div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">' + result.score + '/' + result.total + '</div>' +
                       '<div class="nav-button-group">' +
                       '<button class="nav-btn" style="background:#6c757d" onclick="location.reload()">🔄 Retake</button>' +
                       '<button class="nav-btn" onclick="viewExplanations()">View Explanations</button></div>';
        document.querySelector('.container').appendChild(rb);
    }

    async function viewExplanations() {
        document.getElementById('info-box').innerHTML = 'Explanations';
        const container = document.getElementById('explanation-container');
        container.style.display = 'block';
        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_explanations');
        const data = await response.json();
        explanationData = data.questions;
        
        let html = '<div class="explanation-view">';
        explanationData.forEach(q => {
            const isCorrect = answers[q.questionId] === q.correctAnswer;
            html += '<div class="question-container"><strong>Q' + q.questionId + ' — ' + (isCorrect ? '✅' : '❌') + '</strong><p>' + htmlEscape(q.question) + '</p>';
            q.options.forEach(opt => {
                let cls = (opt.optionId === q.correctAnswer) ? 'correct-answer-label' : (opt.optionId === answers[q.questionId] ? 'user-answer-label' : '');
                html += '<div class="option-label ' + cls + '">' + opt.optionId + '. ' + htmlEscape(opt.text) + '</div>';
            });
            html += '<div class="explanation-box"><strong>Explanation:</strong> ' + htmlEscape(q.explanation) + '</div></div>';
        });
        html += '</div>';
        html += '<div class="nav-button-group">' +
                '<button class="nav-btn export-btn" onclick="exportJson()">Export for Forensic Audit</button>' +
                '<button class="nav-btn" style="background:#6c757d" onclick="location.href=\'index.php\'">🏠 Home</button></div>';
        
        container.innerHTML = html;
        window.scrollTo(0,0);
    }

    function exportJson() {
        if (!explanationData) return;
        const exportData = explanationData.map(q => ({
            questionId: q.questionId,
            question: q.question,
            options: q.options,
            userAnswer: answers[q.questionId] || null,
            correctAnswer: q.correctAnswer,
            explanation: q.explanation
        }));
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'CRK2025_Performance.json';
        a.click();
    }

    window.onload = loadQuestions;
</script>
</body>
</html>