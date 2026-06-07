<?php
// AGRIC 2021 CBT - Standalone PHP Page
// Data source: agric2021.json

// --- Configuration ---
$jsonFile = __DIR__ . '/agric2021.json';
$subjectTitle = 'Agric 2021'; 

// --- Functions to process the JSON file ---

function load_json_data($file) {
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . basename($file)]);
        exit();
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    
    if (!is_array($data) || empty($data)) {
        $json_error = json_last_error_msg();
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format. Expected a top-level array. Error: ' . $json_error]);
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
            'image' => $q['image_key'] ?? $q['image_name'] ?? null, 
            'table' => $q['table'] ?? $q['stimulus'] ?? null,
            'sectionName' => $q['sectionName'] ?? 'Agricultural Science', 
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
            'image' => $q['image_key'] ?? $q['image_name'] ?? null, 
            'table' => $q['table'] ?? $q['stimulus'] ?? null,
            'sectionName' => $q['sectionName'] ?? 'Agricultural Science', 
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
        echo json_encode([
            'success' => true,
            'totalQuestions' => count($questions),
            'questions' => $questions
        ]);
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
        echo json_encode([
            'success' => true,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage
        ]);
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
        label.option-label input[type="radio"] { margin-right: 10px; accent-color: #007aff; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:hover:not(.disabled) { background: #38c172; }
        .nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: hidden; }
        .question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; user-select: none; transition: background-color 0.3s, color 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin-top: 30px; margin-bottom: 30px; }
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
            <h1>Vector Learn — <?php echo htmlspecialchars($subjectTitle); ?></h1>
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
            <button class="nav-btn" id="btn-next">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script>
    let current = 1, allQuestions = [], answers = {}, totalSeconds = 60 * 60, timerInterval;

    function htmlEscape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function renderTable(stimulus) {
        if (!stimulus || !stimulus.columns) return '';
        let html = '<table><thead><tr>';
        stimulus.columns.forEach(col => html += '<th>' + htmlEscape(col) + '</th>');
        html += '</tr></thead><tbody>';
        stimulus.rows.forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(val => html += '<td>' + htmlEscape(val) + '</td>');
            html += '</tr>';
        });
        return html + '</tbody></table>';
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        const qc = document.getElementById('q-container');
        let html = `<div class="question-header"><span class="question-id">Q${qObj.questionId}</span><span class="question-section">${htmlEscape(qObj.sectionName)}</span></div>`;
        html += `<div class="question-text">${htmlEscape(qObj.question)}`;
        if (qObj.image) html += `<img src="${qObj.image}" alt="Diagram"/>`;
        if (qObj.table) html += renderTable(qObj.table);
        html += `</div><form id="qForm">`;
        
        qObj.options.forEach(opt => {
            html += `<label class="option-label">
                <input type="radio" name="answer" value="${opt.optionId}" ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''}>
                <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
            </label>`;
        });
        qc.innerHTML = html + `</form>`;

        document.getElementById('btn-prev').disabled = (current <= 1);
        const btnNext = document.getElementById('btn-next');
        
        if (current === allQuestions.length) {
            btnNext.textContent = "Submit Exam";
            btnNext.style.background = "#ff6b6b";
        } else {
            btnNext.textContent = "Next →";
            btnNext.style.background = "#43e97b";
        }
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

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (form && form.answer.value) {
            answers[allQuestions[current-1].questionId] = form.answer.value;
        }
    }

    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }

    document.getElementById('btn-prev').onclick = () => { if (current > 1) navigate(current - 1); };
    document.getElementById('btn-next').onclick = () => { 
        if (current < allQuestions.length) { navigate(current + 1); } 
        else { submitAnswers(); }
    };

    document.addEventListener('change', (e) => { if(e.target.name === 'answer') { saveAnswer(); renderNav(); } });

    function updateTimer() {
        const m = Math.floor(totalSeconds / 60), s = totalSeconds % 60;
        document.getElementById('countdown').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        if (totalSeconds-- <= 0) { clearInterval(timerInterval); submitAnswers(); }
    }

    async function loadQuestions() {
        const res = await fetch(`${window.location.pathname}?action=get_questions`);
        const data = await res.json();
        if (data.success) { 
            allQuestions = data.questions; 
            renderQuestion(); 
            renderNav(); 
            timerInterval = setInterval(updateTimer, 1000); 
        }
    }

    async function submitAnswers() {
        saveAnswer(); clearInterval(timerInterval);
        const res = await fetch(`${window.location.pathname}?action=submit`, { 
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({answers}) 
        });
        const result = await res.json();
        displayResults(result);
    }

    function displayResults(result) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        const container = document.querySelector('.container');
        const resView = document.createElement('div');
        resView.className = 'question-container';
        resView.style.textAlign = 'center';
        resView.innerHTML = `<h2>Exam Results</h2><div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">${result.score}/${result.total}</div>
            <div class="nav-button-group">
                <button class="nav-btn" onclick="location.reload()">🔄 Retake</button>
                <button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">Explanations</button>
            </div>`;
        container.appendChild(resView);
    }

    async function viewExplanations() {
        const res = await fetch(`${window.location.pathname}?action=get_explanations`);
        const data = await res.json();
        const container = document.getElementById('explanation-container');
        let html = '';
        data.questions.forEach(q => {
            const userAns = answers[q.questionId];
            const isCorrect = userAns === q.correctAnswer;
            html += `<div class="question-container">
                <div class="question-header"><span class="question-id">Q${q.questionId} - ${isCorrect ? '✅' : '❌'}</span></div>
                <div class="question-text">${htmlEscape(q.question)}</div>
                <div class="explanation-box"><strong>Correct: ${q.correctAnswer}</strong><p>${htmlEscape(q.explanation)}</p></div>
            </div>`;
        });
        container.innerHTML = html;
        container.style.display = 'block';
        window.scrollTo(0, 0);
    }

    window.onload = loadQuestions;
</script>


<?php include 'footer.php'; ?></body>
</html>