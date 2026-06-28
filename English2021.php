<?php
/**
 * Vector Learn - CBT Interface
 * File: english2021.php
 * Requirements: english2021.json (The 137 questions provided previously)
 */

$jsonFile = __DIR__ . '/english2021.json';
$action = $_GET['action'] ?? null;

// --- PHP Action: Get Questions ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found: ' . $jsonFile]);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $decodedData = json_decode($jsonContent, true); 
    
    if (!is_array($decodedData)) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format. Expected an array of questions.']);
        exit();
    }
    
    $questions = [];
    $sectionName = 'English Language 2021'; 

    foreach ($decodedData as $questionData) {
        // Map our JSON structure to the UI requirements
        $question = [
            'questionId' => $questionData['id'] ?? null, 
            'instruction' => $questionData['pre_question'] ?? '',
            // Combine Instruction + Question for the UI
            'question' => ($questionData['pre_question'] ?? '') . "\n\n" . ($questionData['question'] ?? ''),
            'options' => [],
            'sectionName' => $sectionName,
            'answer' => $questionData['answer'] ?? '',
            'explanation' => $questionData['explanation'] ?? 'No explanation provided.',
            'imageUrl' => $questionData['image'] ?? null, 
        ];

        // Process options from our key-value pairs (A, B, C, D)
        if (isset($questionData['options']) && is_array($questionData['options'])) {
            foreach ($questionData['options'] as $key => $val) {
                // Handle both simple arrays and associative arrays (A=>val)
                $optId = is_numeric($key) ? chr(65 + $key) : $key; 
                $question['options'][] = [
                    'optionId' => $optId, 
                    'text' => $val
                ];
            }
        }
        $questions[] = $question;
    }
    
    echo json_encode([
        'success' => true,
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — English 2021 CBT</title>
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
        .question-text { font-size: 17px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; white-space: pre-wrap; }
        label.option-label { display: block; font-size: 15px; color: #1e2a3a; margin-bottom: 12px; cursor: pointer; user-select: none; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.3s ease; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        label.option-label input[type="radio"] { margin-right: 10px; accent-color: #007aff; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
        .user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
        .explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; flex: 1; justify-content: center; }
        .nav-btn:hover:not(:disabled) { background: #38c172; }
        .nav-btn:disabled { background: #b2d6be; cursor: default; }
        .question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; margin: 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; text-decoration: none; transition: 0.3s; cursor: pointer; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-weight: 600; }
        .error { background: #ffebee; color: #c62828; padding: 20px; margin: 20px 25px; border-radius: 12px; }
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
            <h1>Vector Learn — English</h1>
            <p>2021 Practice Examination</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>
        
        <div id="exam-ui">
            <div class="section-divider">Subject: English Language</div>
            <div class="question-container" id="q-container">
                <p style="text-align:center;">Loading questions...</p>
            </div>

            <div class="navigation" id="nav-buttons">
                <button class="nav-btn" id="btn-prev">← Previous</button>
                <button class="nav-btn" id="btn-next">Next →</button>
            </div>
            <div class="question-nav" id="q-nav"></div>
        </div>

        <div id="results-container" style="display: none; margin: 0 25px;"></div>
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>
    </div>

<script>
    const baseUrl = '<?php echo $_SERVER['PHP_SELF']; ?>';
    let current = 1;
    let duration = 90; // Minutes for 137 questions
    let allQuestions = [];
    let answers = {};
    let timerInterval;

    async function loadQuestions() {
        try {
            const response = await fetch(baseUrl + '?action=get_questions');
            const data = await response.json();
            if (data.success) {
                allQuestions = data.questions;
                renderQuestion();
                renderNav();
                startTimer();
            } else {
                showError(data.error);
            }
        } catch (e) { showError("Failed to load: " + e.message); }
    }

    function renderQuestion() {
        const qObj = allQuestions[current - 1];
        const container = document.getElementById('q-container');
        
        let html = `<div class="question-header">
            <span class="question-id">Question ${current} of ${allQuestions.length}</span>
            <span class="question-section">${qObj.sectionName}</span>
        </div>
        <div class="question-text">${qObj.question}</div>
        <form id="qForm">`;

        qObj.options.forEach(opt => {
            const isChecked = answers[qObj.questionId] === opt.optionId ? 'checked' : '';
            html += `<label class="option-label">
                <input type="radio" name="answer" value="${opt.optionId}" ${isChecked} onchange="saveAnswer('${qObj.questionId}', '${opt.optionId}')">
                <span>${opt.optionId}. ${opt.text}</span>
            </label>`;
        });

        html += `</form>`;
        container.innerHTML = html;
        
        document.getElementById('btn-prev').disabled = current === 1;
        const nextBtn = document.getElementById('btn-next');
        
        if (current === allQuestions.length) {
            nextBtn.textContent = "Submit Exam";
            nextBtn.style.background = "#ff6b6b";
            nextBtn.onclick = submitAnswers;
        } else {
            nextBtn.textContent = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => { current++; renderQuestion(); renderNav(); window.scrollTo(0,0); };
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
            html += `<a href="javascript:void(0)" onclick="goTo(${num})" class="${cls}">${num}</a>`;
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    function goTo(n) { current = n; renderQuestion(); renderNav(); }

    document.getElementById('btn-prev').onclick = () => { if(current > 1) { current--; renderQuestion(); renderNav(); } };

    function startTimer() {
        let seconds = duration * 60;
        timerInterval = setInterval(() => {
            let m = Math.floor(seconds / 60);
            let s = seconds % 60;
            document.getElementById('countdown').textContent = `${m}:${s < 10 ? '0'+s : s}`;
            if (seconds <= 0) { clearInterval(timerInterval); submitAnswers(); }
            seconds--;
        }, 1000);
    }

    function submitAnswers() {
        if(!confirm("Are you sure you want to submit?")) return;
        clearInterval(timerInterval);
        document.getElementById('exam-ui').style.display = 'none';
        document.getElementById('info-box').textContent = "Exam Completed";

        let score = 0;
        allQuestions.forEach(q => { if(answers[q.questionId] === q.answer) score++; });
        
        const results = document.getElementById('results-container');
        results.style.display = 'block';
        results.innerHTML = `
            <div class="question-container" style="text-align:center">
                <h2>Your Score</h2>
                <h1 style="font-size:60px; color:#007aff">${score} / ${allQuestions.length}</h1>
                <p>${((score/allQuestions.length)*100).toFixed(1)}% Correct</p>
                <button class="nav-btn" onclick="showExplanations()">View Corrections</button>
            </div>`;
    }

    function showExplanations() {
        document.getElementById('results-container').style.display = 'none';
        const exp = document.getElementById('explanation-container');
        exp.style.display = 'block';
        
        let html = '<h2>Corrections & Explanations</h2>';
        allQuestions.forEach((q, i) => {
            const userAns = answers[q.questionId];
            const isCorrect = userAns === q.answer;
            html += `<div class="question-container" style="border-left: 5px solid ${isCorrect ? '#4caf50' : '#ff6b6b'}">
                <p><strong>Q${i+1}:</strong> ${q.question}</p>
                <p>Your Answer: <span style="color:${isCorrect?'green':'red'}">${userAns || 'None'}</span> | Correct: <strong>${q.answer}</strong></p>
                <div class="explanation-box">${q.explanation}</div>
            </div>`;
        });
        html += `<button class="nav-btn" onclick="location.reload()">Restart Practice</button>`;
        exp.innerHTML = html;
        window.scrollTo(0,0);
    }

    function showError(msg) { document.getElementById('q-container').innerHTML = `<div class="error">${msg}</div>`; }

    loadQuestions();
</script>


<?php include 'footer2.php'; ?></body>
</html>