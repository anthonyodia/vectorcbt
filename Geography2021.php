<?php
// geography2021.php - Standalone PHP Page for Geography CBT
// NOTE: Ensure there are absolutely NO spaces or characters before this opening tag.

// Set the correct JSON file path
$jsonFile = __DIR__ . '/geography2021.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Helper function to load and decode JSON data
function loadQuestionData($jsonFile) {
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Question file not found at ' . $jsonFile]);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (!$data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON format or empty file.']);
        exit();
    }
    return $data;
}

// Helper function to flatten the question structure
function flattenQuestions($data, $includeDetails = false) {
    $questions = [];
    $rawList = $data['assessment'] ?? [];
    
    foreach ($rawList as $index => $q) {
        $item = [
            'questionId' => $q['id'],
            'question' => $q['question'],
            'instruction' => $q['instruction'] ?? null,
            'table_data' => $q['table_data'] ?? null,
            'sectionName' => 'WAEC 2021 Geography',
            'sectionId' => 1
        ];

        // Format options into array for JS
        $optionsArray = [];
        foreach ($q['options'] as $id => $text) {
            $optionsArray[] = ['optionId' => $id, 'text' => $text];
        }
        $item['options'] = $optionsArray;

        if ($includeDetails) {
            $item['correctAnswer'] = strtoupper((string)$q['answer']);
            $item['explanation'] = $q['explanation'] ?? '';
        }
        
        $questions[] = $item;
    }
    return $questions;
}

// --- AJAX ACTIONS ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $data = loadQuestionData($jsonFile);
    $questions = flattenQuestions($data, false);
    echo json_encode(['success' => true, 'totalQuestions' => count($questions), 'questions' => $questions]);
    exit();
}

if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $data = loadQuestionData($jsonFile);
    $questions = flattenQuestions($data, true);
    echo json_encode(['success' => true, 'questions' => $questions]);
    exit();
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswers = $input['answers'] ?? [];
    $data = loadQuestionData($jsonFile);
    $allQuestions = flattenQuestions($data, true);
    
    $score = 0;
    $total = count($allQuestions);
    
    foreach ($allQuestions as $index => $q) {
        $qNum = $index + 1;
        $submitted = isset($userAnswers[$qNum]) ? strtoupper((string)$userAnswers[$qNum]) : '';
        if ($submitted === $q['correctAnswer']) {
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
    <title>Vector Learn — Geography 2021</title>
    <style>
        /* UI Consistency Styles */
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; min-height: 100vh; }
        .container { max-width: 900px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 14px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 85%; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-bottom: 20px; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 15px; font-weight: 600; margin: 0 25px 20px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 25px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 250px; }
        .instruction-box { background: #fffde7; border-left: 5px solid #fbc02d; padding: 12px; margin-bottom: 15px; font-size: 14px; font-style: italic; color: #555; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; font-size: 13px; text-align: center; }
        .data-table td, .data-table th { border: 1px solid #ddd; padding: 8px; }
        .question-text { font-size: 18px; font-weight: 600; color: #243246; margin-bottom: 20px; }
        label.option-label { display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #e0e0e0; cursor: pointer; transition: 0.2s; }
        label.option-label:hover { background: #f0f7ff; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; padding: 12px 25px; border-radius: 8px; cursor: pointer; flex: 1; }
        .nav-btn:disabled { background: #ccc; cursor: not-allowed; }
        .question-nav { text-align: center; margin: 20px 0; padding: 0 25px; }
        .question-nav a { display: inline-block; width: 32px; height: 32px; line-height: 32px; margin: 3px; background: #f0f0f0; color: #007aff; text-decoration: none; border-radius: 50%; font-weight: 600; font-size: 12px; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
        .correct-answer-label { border: 2px solid #4CAF50 !important; background: #e8f5e9 !important; }
        .user-answer-label { border: 2px solid #FFC107 !important; background: #fff8e1 !important; }
        .explanation-box { margin-top: 15px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196F3; font-size: 14px; }
    </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Details</span>
            <span>Step 2: Subject</span>
            <span class="active">Step 3: WAEC Geography 2021</span>
        </div>

        <div class="title">
            <h1>Vector - Geography CBT</h1>
            <p>Objective Questions Section</p>
        </div>

        <div class="form-box">Time Remaining: <span id="timer">--:--</span></div>

        <div class="question-container" id="q-area">
            <p style="text-align:center">Loading questions...</p>
        </div>

        <div style="display:flex; gap:10px; margin: 0 25px;" id="nav-controls">
            <button class="nav-btn" id="prev-btn" onclick="changeQ(-1)">← Previous</button>
            <button class="nav-btn" id="next-btn" onclick="changeQ(1)">Next →</button>
        </div>

        <div class="question-nav" id="q-dots"></div>
        <div id="explanation-area" style="display:none; margin: 0 25px;"></div>
    </div>

<script>
    let current = 1;
    let questions = [];
    let userAnswers = {};
    let timeLeft = 60 * 60; // 60 mins

    async function init() {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if(data.success) {
            questions = data.questions;
            render();
            startTimer();
        }
    }

    function render() {
        const q = questions[current-1];
        const container = document.getElementById('q-area');
        
        let html = `<div style="font-size:12px; color:#007aff; margin-bottom:10px;">Question ${current} of ${questions.length}</div>`;
        
        if(q.instruction) html += `<div class="instruction-box">${q.instruction}</div>`;
        
        if(q.table_data) {
            html += `<table class="data-table">`;
            q.table_data.rows.forEach((row, i) => {
                html += `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`;
            });
            html += `</table>`;
        }

        html += `<div class="question-text">${q.question}</div><form id="qForm">`;
        q.options.forEach(opt => {
            const checked = userAnswers[current] === opt.optionId ? 'checked' : '';
            html += `<label class="option-label">
                <input type="radio" name="opt" value="${opt.optionId}" ${checked} onchange="saveAnswer('${opt.optionId}')">
                ${opt.optionId}. ${opt.text}
            </label>`;
        });
        html += `</form>`;
        container.innerHTML = html;

        document.getElementById('prev-btn').disabled = current === 1;
        const nextBtn = document.getElementById('next-btn');
        if(current === questions.length) {
            nextBtn.innerHTML = "Submit Exam";
            nextBtn.style.background = "#ff6b6b";
            nextBtn.onclick = submitExam;
        } else {
            nextBtn.innerHTML = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => changeQ(1);
        }
        renderDots();
    }

    function renderDots() {
        let html = '';
        for(let i=1; i<=questions.length; i++) {
            let cls = i === current ? 'active' : (userAnswers[i] ? 'answered' : '');
            html += `<a href="javascript:void(0)" onclick="goTo(${i})" class="${cls}">${i}</a>`;
        }
        document.getElementById('q-dots').innerHTML = html;
    }

    function saveAnswer(val) { userAnswers[current] = val; renderDots(); }
    function goTo(n) { current = n; render(); }
    function changeQ(dir) { current += dir; render(); }

    function startTimer() {
        const display = document.getElementById('timer');
        const interval = setInterval(() => {
            let m = Math.floor(timeLeft / 60);
            let s = timeLeft % 60;
            display.textContent = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
            if(timeLeft <= 0) { clearInterval(interval); submitExam(); }
            timeLeft--;
        }, 1000);
    }

    async function submitExam() {
        if(!confirm("Are you sure you want to submit?")) return;
        const res = await fetch('?action=submit', {
            method: 'POST',
            body: JSON.stringify({ answers: userAnswers })
        });
        const result = await res.json();
        showResults(result);
    }

    function showResults(res) {
        document.getElementById('q-area').style.display = 'none';
        document.getElementById('nav-controls').style.display = 'none';
        document.getElementById('q-dots').style.display = 'none';
        
        const area = document.getElementById('explanation-area');
        area.style.display = 'block';
        area.innerHTML = `<div class="question-container" style="text-align:center">
            <h2>Final Score: ${res.score} / ${res.total}</h2>
            <h1 style="color:#43e97b">${res.percentage}%</h1>
            <button class="nav-btn" onclick="loadExplanations()">View Detailed Explanations</button>
        </div>`;
    }

    async function loadExplanations() {
        const res = await fetch('?action=get_explanations');
        const data = await res.json();
        let html = '<h3>Review Answers</h3>';
        data.questions.forEach((q, i) => {
            const uAns = userAnswers[i+1];
            const isCorrect = uAns === q.correctAnswer;
            html += `<div class="question-container" style="background:white; margin-bottom:15px;">
                <div style="font-weight:bold; color:${isCorrect ? 'green' : 'red'}">Q${i+1}: ${isCorrect ? 'Correct' : 'Incorrect'}</div>
                <p>${q.question}</p>
                ${q.options.map(o => {
                    let cls = (o.optionId === q.correctAnswer) ? 'correct-answer-label' : (o.optionId === uAns ? 'user-answer-label' : '');
                    return `<div class="option-label ${cls}">${o.optionId}. ${o.text}</div>`;
                }).join('')}
                <div class="explanation-box"><b>Explanation:</b> ${q.explanation}</div>
            </div>`;
        });
        document.getElementById('explanation-area').innerHTML = html + `<button class="nav-btn" onclick="location.reload()">Restart Exam</button>`;
    }

    init();
</script>


<?php include 'footer.php'; ?></body>
</html>