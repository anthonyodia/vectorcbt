<?php
// accounting.php - Vector Learn CBT (With Explanation Logic)

$jsonFile = __DIR__ . '/accounting2021.json';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

function flattenQuestions($data, $includeAnswers = false) {
    $questions = [];
    $source_questions = $data['assessment'] ?? [];
    
    foreach ($source_questions as $question) {
        $options_raw = $question['options'] ?? [];
        $formattedOptions = [];

        if (is_array($options_raw)) {
            foreach ($options_raw as $id => $text) {
                $formattedOptions[] = [
                    'optionId' => (string)$id,
                    'text' => (string)$text
                ];
            }
        }

        $q = [
            'questionId' => $question['id'] ?? null,
            'question' => $question['question'] ?? '',
            'instruction' => $question['instruction'] ?? null,
            'options' => $formattedOptions,
            'sectionName' => $data['exam_metadata']['subject'] ?? 'Accounting'
        ];

        if ($includeAnswers) {
            $q['correctAnswer'] = (string)($question['answer'] ?? '');
            $q['explanation'] = $question['explanation'] ?? 'No explanation provided.';
        }
        $questions[] = $q;
    }
    return $questions;
}

// --- AJAX Handlers ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents($jsonFile), true);
    echo json_encode([
        'success' => true,
        'subjectName' => $data['exam_metadata']['subject'] ?? 'Financial Accounting',
        'questions' => flattenQuestions($data, false)
    ]);
    exit;
}

// New handler to get answers for explanation tab
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents($jsonFile), true);
    echo json_encode([
        'success' => true,
        'questions' => flattenQuestions($data, true)
    ]);
    exit;
}

if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $data = json_decode(file_get_contents($jsonFile), true);
    $questions = flattenQuestions($data, true);
    $userAnswers = $input['answers'] ?? [];

    $score = 0;
    foreach ($questions as $q) {
        if (isset($userAnswers[$q['questionId']]) && (string)$userAnswers[$q['questionId']] === (string)$q['correctAnswer']) {
            $score++;
        }
    }
    echo json_encode([
        'success' => true, 
        'score' => $score, 
        'total' => count($questions), 
        'percentage' => count($questions) > 0 ? round(($score/count($questions))*100, 2) : 0
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Accounting CBT</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; display: flex; justify-content: center; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }
        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; margin: 0; color: #1e2a3a; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 25px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; }
        .question-meta { font-size: 14px; color: #007aff; font-weight: 600; margin-bottom: 15px; display: block; }
        .instruction { font-size: 14px; font-style: italic; color: #777; background: #fff9c4; padding: 10px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #fbc02d; }
        .question-text { font-size: 18px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }
        .option-card { display: block; padding: 12px; margin-bottom: 12px; border: 1px solid #e0e0e0; border-radius: 8px; background: white; }
        .option-card.correct { border: 2px solid #43e97b; background: #f0fff4; }
        .option-card.wrong { border: 2px solid #ff6b6b; background: #fff5f5; }
        .explanation-box { background: #eef2ff; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 5px solid #4facfe; font-size: 15px; }
        .controls { display: flex; justify-content: space-between; margin: 30px 25px; gap: 15px; }
        .btn { padding: 12px 28px; border: none; border-radius: 12px; cursor: pointer; font-weight: bold; font-size: 16px; flex: 1; text-align: center; text-decoration: none; }
        .btn-sec { background: #6c757d; color: white; }
        .btn-primary { background: #43e97b; color: white; }
        .btn-info { background: #4facfe; color: white; }
        #q-dots { text-align: center; margin: 20px 25px 40px 25px; display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .dot { width: 35px; height: 35px; line-height: 35px; background: #f0f0f0; border-radius: 50%; cursor: pointer; font-size: 13px; font-weight: bold; color: #007aff; }
        .dot.active { background: #007aff; color: white; }
        .dot.done { background: #4caf50; color: white; }
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
        <h1>Vector Learn — <span id="sub-title">CBT</span></h1>
    </div>

    <div class="form-box" id="mode-text">Exam Mode: Standard Assessment</div>

    <div id="quiz-app">
        <div class="question-container" id="question-area">Loading questions...</div>
        <div class="controls" id="quiz-controls">
            <button class="btn btn-sec" onclick="changeQ(-1)">← Previous</button>
            <button class="btn btn-primary" id="next-action" onclick="changeQ(1)">Next →</button>
        </div>
        <div id="q-dots"></div>
    </div>
</div>

<script>
    let questions = [];
    let currentIdx = 0;
    let answers = {};

    async function init() {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if (data.success) {
            questions = data.questions;
            document.getElementById('sub-title').innerText = data.subjectName;
            render();
        }
    }

    function render() {
        const q = questions[currentIdx];
        const area = document.getElementById('question-area');
        let html = `<span class="question-meta">Question ${currentIdx + 1} of ${questions.length}</span>`;
        if (q.instruction) html += `<div class="instruction">${q.instruction}</div>`;
        html += `<div class="question-text">${q.question}</div>`;
        q.options.forEach(opt => {
            const isChecked = answers[q.questionId] === opt.optionId ? 'checked' : '';
            html += `<label class="option-card" style="cursor:pointer"><input type="radio" name="quiz_opt" value="${opt.optionId}" ${isChecked} onchange="pick('${q.questionId}', '${opt.optionId}')"> <strong>${opt.optionId}.</strong> ${opt.text}</label>`;
        });
        area.innerHTML = html;
        const btn = document.getElementById('next-action');
        if (currentIdx === questions.length - 1) {
            btn.innerText = "Finish & Submit";
            btn.style.background = "#ff6b6b";
            btn.onclick = submit;
        } else {
            btn.innerText = "Next →";
            btn.style.background = "#43e97b";
            btn.onclick = () => changeQ(1);
        }
        updateDots();
    }

    function pick(qid, val) { answers[qid] = val; updateDots(); }
    function changeQ(step) {
        let target = currentIdx + step;
        if (target >= 0 && target < questions.length) { currentIdx = target; render(); }
    }
    function updateDots() {
        let html = '';
        questions.forEach((q, i) => {
            let cls = 'dot' + (i === currentIdx ? ' active' : '') + (answers[q.questionId] ? ' done' : '');
            html += `<div class="${cls}" onclick="jump(${i})">${i+1}</div>`;
        });
        document.getElementById('q-dots').innerHTML = html;
    }
    function jump(i) { currentIdx = i; render(); }

    async function submit() {
        if(!confirm("Are you sure you want to submit?")) return;
        const res = await fetch('?action=submit', { method: 'POST', body: JSON.stringify({answers}) });
        const result = await res.json();
        showResults(result);
    }

    function showResults(result) {
        document.getElementById('mode-text').innerText = "Exam Result Summary";
        document.getElementById('quiz-app').innerHTML = `
            <div style="text-align:center; padding: 40px;">
                <div style="font-size: 60px; font-weight: 800; color: #007aff;">${result.score} / ${result.total}</div>
                <div style="font-size: 24px; color: #43e97b; font-weight:bold; margin-bottom: 30px;">${result.percentage}% Score</div>
                <div class="controls">
                    <button class="btn btn-info" onclick="viewExplanations()">Show Explanations</button>
                    <button class="btn btn-primary" onclick="location.reload()">Retake Exam</button>
                </div>
            </div>`;
    }

    async function viewExplanations() {
        const res = await fetch('?action=get_explanations');
        const data = await res.json();
        let html = `<h2 style="margin-left:25px">Review & Explanations</h2>`;
        data.questions.forEach((q, i) => {
            const userAns = answers[q.questionId] || "Not Answered";
            html += `<div class="question-container">
                <span class="question-meta">Question ${i+1}</span>
                <div class="question-text">${q.question}</div>`;
            q.options.forEach(opt => {
                let statusClass = "";
                if (opt.optionId === q.correctAnswer) statusClass = "correct";
                else if (opt.optionId === userAns) statusClass = "wrong";
                html += `<div class="option-card ${statusClass}"><strong>${opt.optionId}.</strong> ${opt.text} 
                    ${opt.optionId === q.correctAnswer ? ' (Correct)' : ''} 
                    ${opt.optionId === userAns && userAns !== q.correctAnswer ? ' (Your Choice)' : ''}
                </div>`;
            });
            html += `<div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div></div>`;
        });
        html += `<div class="controls"><button class="btn btn-primary" onclick="location.reload()">Back to Start</button></div>`;
        document.getElementById('quiz-app').innerHTML = html;
        window.scrollTo(0,0);
    }

    init();
</script>


<?php include 'footer2.php'; ?></body>
</html>