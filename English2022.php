<?php
// Set the path to your JSON files
$jsonFiles = [
    __DIR__ . '/english2022part1.json',
    __DIR__ . '/english2022part2.json',
    __DIR__ . '/english2022part3.json'
];

$action = $_GET['action'] ?? null;

if ($action === 'get_questions') {
    header('Content-Type: application/json');

    $allQuestions = [];

    // Load and merge questions from all files
    foreach ($jsonFiles as $file) {
        if (!file_exists($file)) {
            http_response_code(404);
            echo json_encode(['error' => 'Question file not found: ' . $file]);
            exit();
        }
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if (!is_array($data)) {
            http_response_code(500);
            echo json_encode(['error' => 'Invalid JSON in file: ' . $file]);
            exit();
        }
        // Append questions, optionally prefix questionId to keep unique if needed
        foreach ($data as $q) {
            // You could add a prefix or offset if IDs overlap
            $allQuestions[] = $q;
        }
    }

    // Prepare questions for frontend
    $questions = [];
    $sectionName = 'English Language'; // or customize per file if needed

    foreach ($allQuestions as $questionData) {
        $question = [
            'questionId' => $questionData['id'] ?? null,
            'question' => ($questionData['instruction'] ?? '') . ' ' . ($questionData['question'] ?? ''),
            'options' => [],
            'sectionName' => $sectionName,
            'answer' => $questionData['answer'] ?? '',
            'explanation' => $questionData['explanation'] ?? 'No explanation provided.',
            'imageUrl' => $questionData['imageUrl'] ?? null
        ];

        foreach ($questionData['options'] ?? [] as $optId => $optText) {
            $question['options'][] = [
                'optionId' => $optId,
                'text' => $optText
            ];
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
<title>Vector Learn — English CBT Demo (2022 Exam)</title>
<style>
/* Your CSS styles from the model (unchanged) */

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
.correct-answer-label { border: 2px solid #4CAF50 !important; background-color: #e8f5e9 !important; font-weight: bold; }
.user-answer-label { border: 2px solid #FFC107 !important; background-color: #fff8e1 !important; font-weight: bold; }
.explanation-box { margin-top: 20px; padding: 15px; border-radius: 8px; background: #e3f2fd; border-left: 5px solid #2196F3; }
.explanation-box p { margin: 0; font-size: 14px; line-height: 1.6; }
.navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
.nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; font-size: 16px; padding: 12px 28px; border-radius: 12px; cursor: pointer; transition: background 0.3s ease; }
.nav-btn:hover:not(.disabled) { background: #38c172; }
.nav-btn.disabled { background: #b2d6be; cursor: default; pointer-events: none; }
.question-nav { text-align: center; margin: 30px 0 40px 0; padding: 0 25px; overflow-x: auto; }
.question-nav a { display: inline-block; margin: 0 4px; min-width: 34px; height: 34px; line-height: 34px; border-radius: 50%; background: #f7f7f7; color: #007aff; font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; }
.question-nav a.active { background: #007aff; color: white; }
.section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-size: 14px; font-weight: 600; color: #243246; }
.loading { text-align: center; padding: 40px; font-size: 18px; color: #666; }
.error { background: #ffebee; color: #c62828; padding: 20px; margin: 20px 25px; border-radius: 12px; border-left: 4px solid #c62828; }
.question-image { max-width: 100%; height: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e0e0e0; }
@media(max-width:768px){ .container { margin:20px; } .nav-button-group .nav-btn { flex:0 0 auto; } }
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
<p>Learn faster - one step at a time</p>
</div>
<div class="form-box" id="info-box">Time Remaining: <span id="countdown">--:--</span></div>
<div id="section-display">
<div class="section-divider">Subject: English Language</div>
</div>
<div class="question-container" role="main" aria-live="polite" id="q-container">
<p class="loading">Loading questions...</p>
</div>
<div class="navigation" id="nav-buttons">
<button class="nav-btn" id="btn-prev">← Previous</button>
<button class="nav-btn" id="btn-next">Next →</button>
</div>
<div class="question-nav" id="q-nav"></div>
<div id="explanation-container" style="display: none; margin: 0 25px;"></div>
</div>
<script>
const baseUrl = '<?php echo $_SERVER['PHP_SELF']; ?>';
const urlParams = new URLSearchParams(window.location.search);
let current = parseInt(urlParams.get('q')) || 1;
let duration = parseInt(urlParams.get('duration')) || 75; // 75 min or adjust as needed
let allQuestions = [];
let answers = {};

async function loadQuestions() {
    try {
        const response = await fetch(baseUrl + '?action=get_questions');
        const data = await response.json();
        if (data.success && data.questions.length > 0) {
            allQuestions = data.questions;
            if (current > allQuestions.length) current = 1;
            document.getElementById('q-container').innerHTML = '';
            renderQuestion();
            renderNav();
            startTimer();
        } else {
            showError('Failed to load questions: ' + (data.error || ''));
        }
    } catch (e) {
        showError('Network error: ' + e.message);
    }
}
function showError(msg) {
    document.getElementById('q-container').innerHTML = '<div class="error">'+msg+'</div>';
}
function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;
    const qc = document.getElementById('q-container');
    let html = '<div class="question-header">';
    html += '<span class="question-id">Q' + qObj.questionId + '</span>';
    html += '<span class="question-section">' + qObj.sectionName + '</span>';
    html += '</div>';
    html += '<div class="question-text">' + htmlEscape(qObj.question) + '</div>';
    if (qObj.imageUrl) {
        html += '<img src="'+htmlEscape(qObj.imageUrl)+'" class="question-image" />';
    }
    html += '<form id="qForm">';
    qObj.options.forEach(opt => {
        html += '<label class="option-label">';
        html += '<input type="radio" name="answer" value="'+opt.optionId+'" '+(answers[qObj.questionId]===opt.optionId?'checked':'')+' />';
        html += '<span>' + opt.optionId + '. ' + htmlEscape(opt.text) + '</span>';
        html += '</label>';
    });
    html += '</form>';
    qc.innerHTML = html;
    document.getElementById('btn-prev').disabled = (current <= 1);
    const btnNext = document.getElementById('btn-next');
    if (current === allQuestions.length) {
        btnNext.style.display='none';
        if (!document.getElementById('btn-submit')) {
            const submitBtn = document.createElement('button');
            submitBtn.textContent='Submit Exam';
            submitBtn.id='btn-submit';
            submitBtn.className='nav-btn';
            submitBtn.style.background='#ff6b6b';
            submitBtn.onclick=submitAnswers;
            document.getElementById('nav-buttons').appendChild(submitBtn);
        }
    } else {
        btnNext.style.display='inline-flex';
        if (document.getElementById('btn-submit')) document.getElementById('btn-submit').remove();
    }
}
function renderNav() {
    let html='';
    for(let i=1;i<=allQuestions.length;i++){
        const q = allQuestions[i-1];
        const answered = answers[q.questionId]? 'answered':'';
        const active = i===current? 'active':'';
        html += `<a href="#" onclick="navigate(${i});return false;" class="${active} ${answered}">${i}</a>`;
    }
    document.getElementById('q-nav').innerHTML=html;
}
function navigate(num) {
    saveAnswer();
    current=num;
    renderQuestion();
    renderNav();
    window.scrollTo(0,0);
}
document.getElementById('btn-prev').addEventListener('click', () => {
    saveAnswer(); if (current>1){ current--; renderQuestion(); renderNav(); } });
document.getElementById('btn-next').addEventListener('click', () => {
    saveAnswer(); if (current<allQuestions.length){ current++; renderQuestion(); renderNav(); } });
function saveAnswer() {
    const qObj = allQuestions[current-1];
    if (!qObj) return;
    const form = document.getElementById('qForm');
    if (!form) return;
    const selected = form.querySelector('input[name="answer"]:checked')?.value || null;
    if (selected) answers[qObj.questionId]=selected; else delete answers[qObj.questionId];
    renderNav();
}
function htmlEscape(str) {
    const div=document.createElement('div'); div.textContent=str; return div.innerHTML;
}
let totalSeconds = duration*60;
let timerInterval;
function updateTimer() {
    const mins=Math.floor(totalSeconds/60);
    const secs=totalSeconds%60;
    const str= String(mins).padStart(2,'0')+':'+String(secs).padStart(2,'0');
    document.getElementById('countdown').textContent=str;
    if (totalSeconds<=0){ clearInterval(timerInterval); submitAnswers(); }
    else{ totalSeconds--; }
}
function startTimer() {
    updateTimer(); timerInterval=setInterval(updateTimer,1000);
}
async function submitAnswers() {
    saveAnswer();
    clearInterval(timerInterval);
    let score=0;
    const total=allQuestions.length;
    allQuestions.forEach(q => {
        if (answers[q.questionId] && answers[q.questionId]===q.answer) score++;
    });
    const percentage= total>0? ((score/total)*100).toFixed(2):0;
    document.getElementById('q-container').style.display='none';
    document.getElementById('nav-buttons').style.display='none';
    document.getElementById('q-nav').style.display='none';
    document.getElementById('section-display').style.display='none';
    document.getElementById('info-box').innerHTML='Exam Completed!';
    displayResults(score, total, percentage);
}
function displayResults(score, total, percentage) {
    const container=document.querySelector('.container');
    const resDiv=document.createElement('div');
    resDiv.className='question-container';
    resDiv.id='results-view';
    resDiv.innerHTML=`
        <div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Results</div>
        <div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">${score} / ${total}</div>
        <div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">${percentage}%</div>
        <div style="font-size: 16px; color: #555; margin-bottom: 30px;">You answered ${score} out of ${total} questions correctly.</div>
        <div class="nav-button-group">
            <button class="nav-btn" style="background:#007aff;" onclick="viewExplanations()">View Explanation</button>
            <button class="nav-btn" onclick="location.reload()">🔄 Retake Exam</button>
        </div>
    `;
    container.appendChild(resDiv);
}
function viewExplanations() {
    document.getElementById('results-view').style.display='none';
    document.getElementById('info-box').innerHTML='Exam Explanations';
    const explanationContainer=document.getElementById('explanation-container');
    explanationContainer.style.display='block';
    let html='<div class="explanation-view">';
    allQuestions.forEach((q)=>{
        const qid=q.questionId;
        const userAns=answers[qid];
        const isCorrect = userAns===q.answer;
        html+= '<div class="question-container">';
        html+= '<div class="question-header">';
        html+= '<span class="question-id">Q'+qid+' — '+(isCorrect? 'Correct ✅':'Incorrect ❌')+'</span>';
        html+= '<span class="question-section">' + q.sectionName + '</span>';
        html+= '</div>';
        html+= '<div class="question-text">'+htmlEscape(q.question)+'</div>';
        if(q.imageUrl){ html+= '<img src="'+htmlEscape(q.imageUrl)+'" class="question-image" />'; }
        q.options.forEach(opt => {
            let labelClass='';
            if(opt.optionId===q.answer){ labelClass='correct-answer-label'; }
            else if(opt.optionId===userAns&&!isCorrect){ labelClass='user-answer-label'; }
            html+= '<label class="option-label '+labelClass+'">';
            html+= '<span>'+opt.optionId+'. '+htmlEscape(opt.text)+'</span>';
            html+= '</label>';
        });
        if(q.explanation){ html+= '<div class="explanation-box"><strong>Explanation:</strong> '+htmlEscape(q.explanation)+'</div>'; }
        html+= '</div>';
    });
    html+='</div><div class="nav-button-group"><button class="nav-btn" onclick="location.reload()" style="background:#007aff;">🔄 Retake Exam</button></div>';
    explanationContainer.innerHTML=html;
    window.scrollTo(0,0);
}
loadQuestions();
</script>


<?php include 'footer2.php'; ?>
</body>
</html>