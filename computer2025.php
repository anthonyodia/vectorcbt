<?php
// Computer Science 2025 CBT - Standalone PHP Page
// This file handles both loading and serving the CBT

// *** CONFIGURATION ***
$jsonFile = __DIR__ . '/computer2025.json';
$subjectTitle = 'Computer Science 2025';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- ACTION 1: Get Questions for the Exam View ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . $jsonFile]);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true); 
    
    if (!$data || !is_array($data)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format.']);
        exit();
    }
    
    $questions = [];
    foreach ($data as $qData) {
        if (!isset($qData['id'], $qData['question'], $qData['options'])) continue;

        $question = [
            'questionId' => $qData['id'],
            'question' => $qData['question'],
            'options' => [],
            'sectionName' => 'Computer Science',
            'sectionId' => 1
        ];

        // Old tweak: Reformatting associative options array (A: text, B: text)
        foreach ($qData['options'] as $optionId => $optionText) {
            $question['options'][] = [
                'optionId' => $optionId,
                'text' => $optionText
            ];
        }
        $questions[] = $question;
    }
    
    // Old tweak: Sort by ID
    usort($questions, function($a, $b) { return $a['questionId'] <=> $b['questionId']; });

    echo json_encode(['success' => true, 'totalQuestions' => count($questions), 'questions' => $questions]);
    exit();
}

// --- ACTION 2: Get All Details for Explanation View ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    $questionsWithDetails = [];
    foreach ($data as $qData) {
        $question = [
            'questionId' => $qData['id'],
            'question' => $qData['question'],
            'options' => [],
            'correctAnswer' => $qData['answer'], 
            'explanation' => $qData['explanation'] ?? 'No explanation available.',
            'sectionName' => 'Computer Science'
        ];
        foreach ($qData['options'] as $optionId => $optionText) {
            $question['options'][] = ['optionId' => $optionId, 'text' => $optionText];
        }
        $questionsWithDetails[] = $question;
    }
    usort($questionsWithDetails, function($a, $b) { return $a['questionId'] <=> $b['questionId']; });
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];
    $data = json_decode(file_get_contents($jsonFile), true);
    
    $questionMap = [];
    $total = 0;
    foreach ($data as $qData) {
        if (isset($qData['id'], $qData['answer'])) {
            $questionMap[$qData['id']] = $qData['answer']; 
            $total++;
        }
    }
    
    $score = 0;
    foreach ($answers as $qId => $submittedAnswer) {
        if (isset($questionMap[$qId]) && $submittedAnswer === $questionMap[$qId]) {
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
    <title>Vector Learn — <?php echo $subjectTitle; ?></title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; padding-bottom: 20px; }
        
        /* New Design Step Header */
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
        
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        label.option-label input[type="radio"] { margin-right: 10px; accent-color: #007aff; }

        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; flex: 1; justify-content: center; display: inline-flex; align-items: center; text-decoration: none; }
        .nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
        
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: auto; white-space: nowrap; }
        .question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; text-decoration: none; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }

        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; font-size: 14px; }
        .nav-button-group { display: flex; justify-content: center; gap: 10px; margin: 30px 0; }
        .home-btn { background: #6c757d; }
        .export-btn { background: #6f42c1 !important; }
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
            <h1>Vector Learn — Computer Science 2025</h1>
            <p>Mastering algorithms and hardware concepts.</p>
        </div>

        <div class="form-box" id="info-box">Time Remaining: <span id="countdown">--:--</span></div>
        <div id="section-display"></div>
        <div class="question-container" id="q-container"><p class="loading">Loading questions...</p></div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
            <button class="nav-btn" id="btn-next">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script>
    const baseUrl = window.location.pathname;
    const homeUrl = baseUrl.substring(0, baseUrl.lastIndexOf('/') + 1) + 'index.php';
    let current = 1, allQuestions = [], answers = {}, explanationData = null;
    let totalSeconds = 60 * 60, timerInterval;

    function htmlEscape(text) { 
        const div = document.createElement('div'); 
        div.textContent = text; 
        return div.innerHTML; 
    }

    async function loadQuestions() {
        try {
            const resp = await fetch(baseUrl + '?action=get_questions');
            const data = await resp.json();
            if (data.success) {
                allQuestions = data.questions;
                renderQuestion(); 
                renderNav(); 
                startTimer();
            }
        } catch (e) { console.error("Load failed", e); }
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        if (!qObj) return;

        document.getElementById('section-display').innerHTML = `<div class="section-divider" style="margin:20px 25px; padding:10px; background:#f0f0f0; border-left:4px solid #007aff; border-radius:6px; font-weight:600;">Subject: ${qObj.sectionName}</div>`;
        
        let html = `<div class="question-header"><span class="question-id">Q${qObj.questionId}</span><span class="question-section">${qObj.sectionName}</span></div>
                    <div class="question-text">${htmlEscape(qObj.question)}</div><form id="qForm">`;
        
        qObj.options.forEach(opt => {
            html += `<label class="option-label">
                        <input type="radio" name="answer" value="${opt.optionId}" ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''}>
                        <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
                     </label>`;
        });
        
        document.getElementById('q-container').innerHTML = html + `</form>`;
        
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        
        btnPrev.disabled = (current === 1);
        
        if (current === allQuestions.length) {
            btnNext.style.display = 'none';
            if (!document.getElementById('btn-submit')) {
                const btnSubmit = document.createElement('button');
                btnSubmit.id = 'btn-submit';
                btnSubmit.className = 'nav-btn';
                btnSubmit.style.background = '#ff6b6b';
                btnSubmit.textContent = 'Submit Exam';
                btnSubmit.onclick = submitAnswers;
                document.getElementById('nav-buttons').appendChild(btnSubmit);
            }
        } else {
            btnNext.style.display = 'inline-flex';
            const sub = document.getElementById('btn-submit');
            if (sub) sub.remove();
        }
    }

    function renderNav() {
        let html = '';
        allQuestions.forEach((q, i) => {
            let num = i + 1;
            let cls = (num === current) ? 'active' : (answers[q.questionId] ? 'answered' : '');
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
        window.scrollTo(0, 0);
    }

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (form) {
            const sel = form.querySelector('input[name="answer"]:checked');
            if (sel) {
                answers[allQuestions[current - 1].questionId] = sel.value;
            }
        }
    }

    document.getElementById('btn-prev').onclick = () => navigate(current - 1);
    document.getElementById('btn-next').onclick = () => navigate(current + 1);

    function startTimer() {
        timerInterval = setInterval(() => {
            let m = Math.floor(totalSeconds / 60), s = totalSeconds % 60;
            document.getElementById('countdown').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                submitAnswers();
            }
            totalSeconds--;
        }, 1000);
    }

    async function submitAnswers() {
        saveAnswer();
        clearInterval(timerInterval);
        try {
            const resp = await fetch(baseUrl + '?action=submit', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers }) 
            });
            const res = await resp.json();
            if (res.success) displayResults(res);
        } catch (e) { console.error("Submit failed", e); }
    }

    function displayResults(res) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';
        document.getElementById('info-box').textContent = 'Exam Completed!';
        
        const div = document.createElement('div');
        div.className = 'question-container';
        div.id = 'results-view';
        div.style.textAlign = 'center';
        div.innerHTML = `
            <h1 style="font-size:48px; color:#007aff; margin-bottom:10px;">${res.score}/${res.total}</h1>
            <h2 style="color:#43e97b; margin-bottom:20px;">${res.percentage}%</h2>
            <p style="margin-bottom:30px; color:#555;">Review your computer science performance below.</p>
            <div class="nav-button-group">
                <button class="nav-btn home-btn" onclick="location.href='${homeUrl}'">🏠 Home</button>
                <button class="nav-btn" style="background:#007aff" onclick="viewExplanations()">View Explanations</button>
                <button class="nav-btn" onclick="location.reload()">🔄 Retake</button>
            </div>`;
        document.querySelector('.container').appendChild(div);
    }

    async function viewExplanations() {
        const resp = await fetch(baseUrl + '?action=get_explanations');
        const data = await resp.json();
        explanationData = data.questions;
        document.getElementById('explanation-container').style.display = 'block';
        const resView = document.getElementById('results-view');
        if (resView) resView.style.display = 'none';
        
        let html = '<div class="explanation-view">';
        explanationData.forEach(q => {
            const userAns = answers[q.questionId];
            const isCorrect = userAns === q.correctAnswer;
            html += `
                <div class="question-container">
                    <div class="question-header"><span>Q${q.questionId} — ${isCorrect ? '✅' : '❌'}</span></div>
                    <div class="question-text">${htmlEscape(q.question)}</div>`;
            q.options.forEach(opt => {
                let cls = opt.optionId === q.correctAnswer ? 'correct-answer-label' : (opt.optionId === userAns ? 'user-answer-label' : '');
                html += `<div class="option-label ${cls}">${opt.optionId}. ${htmlEscape(opt.text)}</div>`;
            });
            html += `<div class="explanation-box"><strong>Explanation:</strong> ${htmlEscape(q.explanation)}</div></div>`;
        });
        html += `</div><div class="nav-button-group">
                    <button class="nav-btn export-btn" onclick="exportJson()">Export for Forensic Audit</button>
                    <button class="nav-btn home-btn" onclick="location.href='${homeUrl}'">🏠 Home</button>
                 </div>`;
        document.getElementById('explanation-container').innerHTML = html;
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
        a.download = 'Computer2025_Performance.json'; 
        a.click();
    }

    window.onload = loadQuestions;
</script>


<?php include 'footer.php'; ?></body>
</html>