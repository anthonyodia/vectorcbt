<?php
// CIVIC EDUCATION 2021 CBT - Standalone PHP Page
$jsonFile = __DIR__ . '/Civic2021.json';
$subjectTitle = 'Civic Education 2021';
$subjectTagline = 'Testing your knowledge of citizenship and societal values.';

// --- Functions ---
function load_json_data($file) {
    if (!file_exists($file)) { http_response_code(404); exit("Error: Question file not found."); }
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    return $data['questions'] ?? $data;
}

// --- API ACTIONS ---
$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (in_array($action, ['get_questions', 'submit'])) {
    header('Content-Type: application/json');
    $raw_questions = load_json_data($jsonFile);
    
    if ($action === 'get_questions') {
        $formatted = [];
        foreach ($raw_questions as $q) {
            $formatted[] = [
                'questionId' => (int) $q['id'],
                'question' => $q['question'],
                'image' => $q['image_path'] ?? null,
                'sectionName' => $q['sectionName'] ?? 'General Civic',
                'options' => array_map(function($id, $text) { return ['optionId' => $id, 'text' => $text]; }, array_keys($q['options']), $q['options'])
            ];
        }
        echo json_encode(['success' => true, 'questions' => $formatted]);
        exit();
    }

    if ($action === 'submit') {
        $input = json_decode(file_get_contents('php://input'), true);
        $answers = $input['answers'] ?? [];
        $score = 0;
        foreach ($raw_questions as $q) { if (isset($answers[$q['id']]) && $answers[$q['id']] === $q['answer']) $score++; }
        echo json_encode(['success' => true, 'score' => $score, 'total' => count($raw_questions), 'percentage' => round(($score/count($raw_questions))*100, 2)]);
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
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding-bottom: 20px; overflow: hidden; }
        
        /* 2024 Step Indicator Style */
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }

        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; color: #1e2a3a; margin: 0; }
        .title p { color: #555; margin: 8px 0 22px 0; }

        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; font-size: 18px; }
        
        .section-divider { margin: 30px 25px 0 25px; padding: 15px 20px; background: #f0f0f0; border-left: 4px solid #007aff; border-radius: 6px; font-weight: 600; color: #243246; }
        
        .question-container { margin: 0 25px 25px 25px; padding: 25px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 300px; }
        
        /* The requested 40px margin and bold accent */
        .question-text { font-size: 19px; font-weight: 700; margin-bottom: 40px; line-height: 1.5; color: #1a2a3a; border-left: 5px solid #007aff; padding-left: 15px; }
        
        .q-image { max-width: 100%; height: auto; margin-bottom: 25px; border-radius: 8px; display: block; }

        label.option-label { display: block; padding: 14px; margin-bottom: 12px; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-size: 15px; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        label.option-label input { margin-right: 12px; accent-color: #007aff; transform: scale(1.2); }

        /* 2024 Navigation Buttons */
        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 15px; }
        .nav-btn { flex: 1; padding: 14px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; color: white; background: #43e97b; font-size: 16px; transition: 0.3s; display: inline-flex; justify-content: center; align-items: center; }
        .nav-btn:hover { background: #38c172; }
        .nav-btn:disabled { background: #b2d6be; cursor: not-allowed; }
        
        .question-nav { text-align: center; margin-bottom: 40px; padding: 0 25px; }
        .question-nav a { display: inline-block; width: 36px; height: 36px; line-height: 36px; margin: 5px; background: #f7f7f7; border-radius: 50%; text-decoration: none; color: #007aff; font-weight: 600; transition: 0.3s; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }

        /* Results & Explanations */
        .result-card { text-align: center; padding: 40px; }
        .score-circle { font-size: 48px; color: #007aff; font-weight: 800; margin: 20px 0; }
        .explanation-box { margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 5px solid #2196f3; border-radius: 8px; font-size: 14px; }
        .correct { border: 2px solid #4caf50 !important; background: #e8f5e9 !important; font-weight: 600; }
        .incorrect { border: 2px solid #ff6b6b !important; background: #fff5f5 !important; font-weight: 600; }
    </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>
    <div class="container">
        <div class="steps"><span>Step 1</span><span>Step 2</span><span class="active">Step 3: Begin!</span></div>
        
        <div class="title">
            <h1>Vector Learn — <?php echo htmlspecialchars($subjectTitle); ?></h1>
            <p><?php echo htmlspecialchars($subjectTagline); ?></p>
        </div>

        <div class="form-box" id="info-box">Time Remaining: <span id="countdown">60:00</span></div>
        
        <div id="section-display"></div>

        <div id="q-container" class="question-container">
            <p style="text-align:center; padding: 50px;">Loading exam questions...</p>
        </div>

        <div class="navigation" id="nav-group">
            <button class="nav-btn" id="btn-prev" onclick="move(-1)">← Previous</button>
            <button class="nav-btn" id="btn-next" onclick="move(1)">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
        <div id="explanation-view" style="display:none; margin: 0 25px;"></div>
    </div>

<script>
    let current = 1, questions = [], answers = {}, time = 3600, timerInterval;

    async function start() {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if(data.success) { questions = data.questions; render(); startTimer(); }
    }

    function render() {
        const q = questions[current - 1];
        if(!q) return;

        document.getElementById('section-display').innerHTML = `<div class="section-divider">Section: ${q.sectionName}</div>`;
        
        let html = `<div class="question-text">${q.question}</div>`;
        if(q.image) html += `<img src="${q.image}" class="q-image">`;
        
        html += `<form id="qForm">`;
        q.options.forEach(o => {
            html += `<label class="option-label">
                <input type="radio" name="ans" value="${o.optionId}" onchange="save('${q.questionId}', '${o.optionId}')" ${answers[q.questionId] === o.optionId ? 'checked' : ''}>
                <span>${o.optionId}. ${o.text}</span>
            </label>`;
        });
        document.getElementById('q-container').innerHTML = html + `</form>`;

        document.getElementById('btn-prev').disabled = (current === 1);
        const next = document.getElementById('btn-next');
        if(current === questions.length) {
            next.innerHTML = "Submit Exam";
            next.style.background = "#ff6b6b";
            next.onclick = submit;
        } else {
            next.innerHTML = "Next →";
            next.style.background = "#43e97b";
            next.onclick = () => move(1);
        }
        updateNav();
    }

    function move(dir) { current += dir; render(); window.scrollTo(0,0); }
    function jump(n) { current = n; render(); }
    function save(qid, val) { answers[qid] = val; updateNav(); }

    function updateNav() {
        let h = '';
        questions.forEach((q, i) => {
            let cls = (current === i+1) ? 'active' : (answers[q.questionId] ? 'answered' : '');
            h += `<a href="javascript:void(0)" onclick="jump(${i+1})" class="${cls}">${i+1}</a>`;
        });
        document.getElementById('q-nav').innerHTML = h;
    }

    function startTimer() {
        timerInterval = setInterval(() => {
            let m = Math.floor(time / 60), s = time % 60;
            document.getElementById('countdown').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if(time-- <= 0) submit();
        }, 1000);
    }

    async function submit() {
        if(!confirm("Are you sure you want to submit your exam?")) return;
        clearInterval(timerInterval);
        
        const res = await fetch('?action=submit', { 
            method: 'POST', 
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({answers}) 
        });
        const result = await res.json();
        
        showResults(result);
    }

    function showResults(r) {
        document.getElementById('info-box').innerHTML = "Exam Completed";
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-group').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('section-display').style.display = 'none';

        const ev = document.getElementById('explanation-view');
        ev.style.display = 'block';
        ev.innerHTML = `
            <div class="question-container result-card">
                <h1>Exam Results</h1>
                <div class="score-circle">${r.score} / ${r.total}</div>
                <p style="font-size:20px; color:#43e97b; font-weight:700;">${r.percentage}% Score</p>
                <div style="display:flex; gap:10px; margin-top:30px;">
                    <button class="nav-btn" onclick="location.reload()" style="background:#6c757d">🔄 Retake Exam</button>
                    <button class="nav-btn" onclick="location.href='index.php'">🏠 Home</button>
                </div>
            </div>`;
        window.scrollTo(0,0);
    }

    window.onload = start;
</script>


<?php include 'footer.php'; ?></body>
</html>