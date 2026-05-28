<?php
// PHYSICS 2025 CBT - Standalone PHP Page
// Data source: physics2025.json

// --- Configuration ---
$jsonFile = __DIR__ . '/physics2025.json';
$subjectTitle = 'Physics 2025'; 

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
    
    if (!is_array($data) || empty($data)) {
        $json_error = json_last_error_msg();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format. PHP JSON Error: ' . $json_error]);
        exit();
    }
    return $data; 
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
            'sectionName' => $q['sectionName'] ?? 'Physics', 
            'sectionId' => $q['sectionId'] ?? 1
        ];
        
        foreach ($q['options'] as $optionId => $text) {
            $question['options'][] = ['optionId' => $optionId, 'text' => $text];
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
            'sectionName' => $q['sectionName'] ?? 'Physics', 
            'sectionId' => $q['sectionId'] ?? 1,
            'options' => []
        ];
        
        foreach ($q['options'] as $optionId => $text) {
            $question['options'][] = ['optionId' => $optionId, 'text' => $text];
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
        foreach ($answers as $qId => $userAnswer) {
            if (isset($correct_answers_map[(int)$qId]) && $userAnswer === $correct_answers_map[(int)$qId]) { $score++; }
        }
        $total = count($correct_answers_map);
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
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 20px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 300px; }
        .explanation-view .question-container { background: #ffffff; border: 1px solid #cceeff; margin-bottom: 20px; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .question-id { font-size: 14px; color: #007aff; font-weight: 600; }
        .question-section { font-size: 12px; color: #999; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; }
        .question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
        .question-text img { max-width: 100%; height: auto; display: block; margin: 15px 0; border-radius: 8px; border: 1px solid #e0e0e0; }
        .question-text table { width: 100%; border-collapse: collapse; margin: 15px 0; background: #fff; border: 1px solid #ddd; }
        .question-text th, .question-text td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .question-text th { background-color: #f2f2f2; }
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:hover:not(.disabled) { background: #38c172; }
        .nav-btn:disabled { background: #ccc; cursor: not-allowed; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; margin: 0 4px 8px 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; text-decoration: none; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-weight: 600; }
        .export-btn { background: #6f42c1 !important; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin: 30px 0; }
        .loading { text-align: center; padding: 40px; font-size: 18px; color: #666; }
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
            <p>Mastering physics, one concept at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <div id="section-display"></div>
        <div class="question-container" id="q-container"><p class="loading">Loading physics questions...</p></div>
        
        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev">← Previous</button>
            <button class="nav-btn" id="btn-next">Next →</button>
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
    let explanationData = null;

    function htmlEscape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderTable(tableData) {
        if (!tableData || typeof tableData !== 'object') return '';
        let html = '<table><thead><tr>';
        const headers = Object.keys(tableData);
        headers.forEach(key => html += `<th>${key.replace(/_/g, ' ').toUpperCase()}</th>`);
        html += '</tr></thead><tbody><tr>';
        headers.forEach(key => html += `<td>${htmlEscape(tableData[key])}</td>`);
        html += '</tr></tbody></table>';
        return html;
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        document.getElementById('section-display').innerHTML = `<div class="section-divider">Section: ${htmlEscape(qObj.sectionName)}</div>`;
        const qc = document.getElementById('q-container');
        let html = `<div class="question-header"><span class="question-id">Q${qObj.questionId}</span><span class="question-section">${htmlEscape(qObj.sectionName)}</span></div>`;
        html += `<div class="question-text">${htmlEscape(qObj.question)}`;
        if (qObj.image) html += `<img src="${htmlEscape(qObj.image)}" alt="Physics Diagram"/>`;
        if (qObj.table) html += renderTable(qObj.table);
        html += `</div><form id="qForm">`;
        
        qObj.options.forEach(opt => {
            html += `<label class="option-label"><input type="radio" name="answer" value="${opt.optionId}" ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''} onchange="saveChoice('${qObj.questionId}', '${opt.optionId}')"><span>${opt.optionId}. ${htmlEscape(opt.text)}</span></label>`;
        });
        qc.innerHTML = html + `</form>`;

        document.getElementById('btn-prev').disabled = (current <= 1);
        const navButtons = document.getElementById('nav-buttons');
        let btnNext = document.getElementById('btn-next');
        let btnSubmit = document.getElementById('btn-submit');

        if (current === allQuestions.length) {
            btnNext.style.display = 'none';
            if (!btnSubmit) {
                btnSubmit = document.createElement('button');
                btnSubmit.id = 'btn-submit';
                btnSubmit.className = 'nav-btn';
                btnSubmit.style.background = '#ff6b6b';
                btnSubmit.textContent = 'Submit Exam';
                btnSubmit.onclick = submitAnswers;
                navButtons.appendChild(btnSubmit);
            }
        } else {
            btnNext.style.display = 'inline-flex';
            if (btnSubmit) btnSubmit.remove();
        }
    }

    function saveChoice(qId, val) {
        answers[qId] = val;
        renderNav();
    }

    function renderNav() {
        let html = '';
        allQuestions.forEach((q, i) => {
            const num = i + 1;
            const cls = (num === current ? 'active ' : '') + (answers[q.questionId] ? 'answered' : '');
            html += `<a href="#" onclick="navigate(${num}); return false;" class="${cls}">${num}</a>`;
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
            let mins = Math.floor(totalSeconds / 60), secs = totalSeconds % 60;
            document.getElementById('countdown').textContent = String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');
            if (totalSeconds <= 0) { clearInterval(timerInterval); submitAnswers(); }
            totalSeconds--;
        }, 1000);
    }

    async function loadQuestions() {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if (data.success) { allQuestions = data.questions; renderQuestion(); renderNav(); startTimer(); }
    }

    async function submitAnswers() {
        clearInterval(timerInterval);
        const res = await fetch('?action=submit', { method: 'POST', body: JSON.stringify({ answers: answers }) });
        const result = await res.json();
        displayResults(result);
    }

    function displayResults(result) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const resView = document.createElement('div');
        resView.className = 'question-container';
        resView.style.textAlign = 'center';
        resView.innerHTML = `<div style="font-size: 32px; font-weight: bold;">Physics Results</div><div style="font-size: 48px; color: #007aff; margin: 20px 0;">${result.score}/${result.total}</div><div style="font-size: 24px; color: #43e97b; margin-bottom: 20px;">${result.percentage}%</div><div class="nav-button-group"><button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">View Explanations</button><button class="nav-btn" onclick="location.reload()">Retake Exam</button></div>`;
        document.querySelector('.container').appendChild(resView);
    }

    async function viewExplanations() {
        const resultContainers = document.querySelectorAll('.question-container:not(#q-container)');
        resultContainers.forEach(el => el.remove());
        
        const expContainer = document.getElementById('explanation-container');
        expContainer.style.display = 'block';
        const res = await fetch('?action=get_explanations');
        const data = await res.json();
        explanationData = data.questions;

        let html = '<div class="explanation-view">';
        explanationData.forEach((q, i) => {
            const isCorrect = answers[q.questionId] === q.correctAnswer;
            html += `<div class="question-container"><div class="question-header"><span class="question-id">Q${i+1} — ${isCorrect ? '✅' : '❌'}</span></div><div class="question-text">${htmlEscape(q.question)}</div>${q.options.map(opt => {
                let cls = (opt.optionId === q.correctAnswer) ? 'correct-answer-label' : (opt.optionId === answers[q.questionId] ? 'user-answer-label' : '');
                return `<label class="option-label ${cls}"><span>${opt.optionId}. ${htmlEscape(opt.text)}</span></label>`;
            }).join('')}<div class="explanation-box"><strong>Explanation:</strong> <p>${htmlEscape(q.explanation)}</p></div></div>`;
        });

        html += `<div class="nav-button-group"><button class="nav-btn export-btn" onclick="exportPerformance()">Export for Forensic Audit</button><button class="nav-btn" style="background:#6c757d" onclick="location.reload()">Finish</button></div></div>`;
        expContainer.innerHTML = html;
        window.scrollTo(0,0);
    }

    function exportPerformance() {
        if (!explanationData) return;
        const exportData = explanationData.map(q => ({
            questionId: q.questionId,
            question: q.question,
            options: q.options,
            userAnswer: answers[q.questionId] || null,
            correctAnswer: q.correctAnswer,
            explanation: q.explanation
        }));
        const blob = new Blob([JSON.stringify(exportData, null, 2)], {type: 'application/json'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'Physics2025_Performance.json';
        a.click();
    }

    window.onload = loadQuestions;
</script>
</body>
</html>