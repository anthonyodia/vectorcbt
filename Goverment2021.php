<?php
session_start();

// Logic: Handles government2021.json and Session storage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $jsonFile = 'goverment2021.json';
    
    if ($_POST['action'] === 'loadQuestions') {
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $decoded = json_decode($jsonData, true);
            $questions = $decoded['questions'] ?? [];
            
            if (json_last_error() === JSON_ERROR_NONE && !empty($questions)) {
                echo json_encode(['success' => true, 'questions' => $questions]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON or empty questions key']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'government2021.json not found']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'saveAnswer') {
        $questionId = $_POST['questionId'] ?? null;
        $answer = $_POST['answer'] ?? null;
        if ($questionId && $answer) {
            $_SESSION['answers'][$questionId] = $answer;
            echo json_encode(['success' => true]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'submitAnswers') {
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $decoded = json_decode($jsonData, true);
            $questions = $decoded['questions'] ?? [];
            
            $score = 0;
            $total = count($questions);
            foreach ($questions as $q) {
                $userAnswer = $_SESSION['answers'][$q['id']] ?? null;
                if ($userAnswer === $q['answer']) { $score++; }
            }
            unset($_SESSION['answers']);
            echo json_encode([
                'success' => true,
                'score' => $score,
                'total' => $total,
                'percentage' => $total > 0 ? round(($score / $total) * 100, 2) : 0
            ]);
        }
        exit;
    }
}

$currentQuestion = isset($_GET['q']) ? (int)$_GET['q'] : 1;
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 40;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Government CBT</title>
    <style>
        /* Modernized Styling Pattern */
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; display: flex; justify-content: center; min-height: 100vh; }
        .container { max-width: 1000px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; padding-bottom: 20px; }
        
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 15px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 90%; }
        .steps span { flex: 1; text-align: center; color: #aaa; }
        .steps .active { color: #007aff; font-weight: 600; }

        .title { text-align: center; margin-top: 10px; }
        .title h1 { font-size: 30px; margin: 0; color: #1e2a3a; }
        .title p { margin: 8px 0 22px 0; font-size: 15px; color: #555; }

        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 18px 15px; font-size: 18px; font-weight: 600; margin: 20px 25px 30px 25px; border-radius: 12px; }

        .question-container { margin: 0 25px 25px; padding: 25px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 250px; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .question-id { font-size: 14px; color: #007aff; font-weight: 600; }
        .question-text { font-size: 18px; font-weight: 600; color: #243246; margin-bottom: 20px; line-height: 1.5; }

        label.option-label { display: block; font-size: 16px; padding: 12px; margin-bottom: 12px; border-radius: 8px; border: 1px solid #e0e0e0; cursor: pointer; transition: 0.2s; background: white; }
        label.option-label:hover { background: #f0f7ff; border-color: #007aff; }
        input[type="radio"] { margin-right: 12px; accent-color: #007aff; }

        .navigation { display: flex; justify-content: space-between; margin: 30px 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; padding: 12px 28px; border-radius: 12px; cursor: pointer; font-size: 16px; flex: 1; transition: 0.3s; }
        .nav-btn:hover { opacity: 0.9; }
        .nav-btn:disabled { background: #b2d6be; cursor: not-allowed; }

        .question-nav { text-align: center; margin: 20px 25px; display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .question-nav a { display: inline-block; width: 35px; height: 35px; line-height: 35px; border-radius: 50%; background: #f7f7f7; color: #007aff; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; border: 1px solid transparent; }
        .question-nav a.active { background: #007aff !important; color: white !important; }
        .question-nav a.answered { background: #e1f5fe; border: 1px solid #007aff; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1: Details</span>
            <span>Step 2: Subject</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector Learn — Government 2021</h1>
            <p>Official Examination Interface</p>
        </div>

        <div class="form-box">Time Remaining: <span id="countdown">--:--</span></div>

        <div id="quiz-ui">
            <div class="question-container" id="q-container">
                <p style="text-align:center;">Loading questions, please wait...</p>
            </div>

            <div class="navigation" id="nav-buttons">
                <button class="nav-btn" id="btn-prev" onclick="navigate(current - 1)">← Previous</button>
                <button class="nav-btn" id="btn-next" onclick="navigate(current + 1)">Next →</button>
            </div>

            <div class="question-nav" id="q-nav"></div>
        </div>
    </div>

<script>
    let current = <?php echo $currentQuestion; ?>;
    let duration = <?php echo $duration; ?>;
    let questions = [];
    let answers = <?php echo json_encode($_SESSION['answers'] ?? (object)[]); ?>;

    function loadQuestions() {
        const fd = new FormData(); 
        fd.append('action', 'loadQuestions');
        
        fetch(window.location.pathname, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    questions = data.questions;
                    renderQuestion();
                    renderNav();
                    startTimer();
                } else {
                    document.getElementById('q-container').innerHTML = `<p style="color:red; text-align:center;">Error: ${data.error}</p>`;
                }
            })
            .catch(err => {
                document.getElementById('q-container').innerHTML = `<p style="color:red; text-align:center;">Fetch error. Check your server connection.</p>`;
            });
    }

    function renderQuestion() {
        if (questions.length === 0) return;
        const qObj = questions[current - 1];
        
        let html = `
            <div class="question-header">
                <span class="question-id">Question ${current}</span>
                <span style="font-size:12px; background:#eee; padding:4px 8px; border-radius:6px;">Government 2021</span>
            </div>
            <div class="question-text">${qObj.question}</div>
            <form id="qForm">`;

        for (let opt in qObj.options) {
            const isChecked = (answers[qObj.id] === opt) ? 'checked' : '';
            html += `
                <label class="option-label">
                    <input type="radio" name="answer" value="${opt}" onchange="saveAnswer('${qObj.id}', '${opt}')" ${isChecked}>
                    <span>${opt}. ${qObj.options[opt]}</span>
                </label>`;
        }
        document.getElementById('q-container').innerHTML = html + `</form>`;
        
        document.getElementById('btn-prev').disabled = (current === 1);
        const nextBtn = document.getElementById('btn-next');
        
        if (current === questions.length) {
            nextBtn.textContent = "Submit Exam";
            nextBtn.style.background = "#ff3b30";
            nextBtn.onclick = submitAnswers;
        } else {
            nextBtn.textContent = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => navigate(current + 1);
        }
        renderNav();
    }

    function renderNav() {
        let html = '';
        questions.forEach((q, i) => {
            const num = i + 1;
            const answeredClass = answers[q.id] ? 'answered' : '';
            const activeClass = (num === current) ? 'active' : '';
            html += `<a href="#" onclick="navigate(${num}); return false;" class="${activeClass} ${answeredClass}">${num}</a>`;
        });
        document.getElementById('q-nav').innerHTML = html;
    }

    function navigate(num) {
        if(num < 1 || num > questions.length) return;
        current = num;
        renderQuestion();
        window.scrollTo(0,0);
    }

    function saveAnswer(qId, val) {
        answers[qId] = val;
        const fd = new FormData();
        fd.append('action', 'saveAnswer');
        fd.append('questionId', qId);
        fd.append('answer', val);
        fetch(window.location.pathname, { method: 'POST', body: fd });
        renderNav();
    }

    let totalSeconds = duration * 60;
    function startTimer() {
        const timerDisplay = document.getElementById('countdown');
        const timer = setInterval(() => {
            if (totalSeconds <= 0) { 
                clearInterval(timer); 
                alert("Time is up!");
                submitAnswers(); 
            }
            const m = Math.floor(totalSeconds / 60);
            const s = totalSeconds % 60;
            timerDisplay.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            totalSeconds--;
        }, 1000);
    }

    function submitAnswers() {
        if(totalSeconds > 0 && !confirm("Finish and see results?")) return;
        
        const fd = new FormData(); 
        fd.append('action', 'submitAnswers');
        fetch(window.location.pathname, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                document.querySelector('.container').innerHTML = `
                    <div class="form-box">Exam Result</div>
                    <div class="question-container" style="text-align:center; padding: 50px;">
                        <h2 style="color: #1e2a3a">Submission Successful!</h2>
                        <div style="font-size: 60px; color: #43e97b; font-weight: bold; margin: 20px 0;">
                            ${data.score} / ${data.total}
                        </div>
                        <p style="font-size: 18px; color: #555;">Percentage Score: <strong>${data.percentage}%</strong></p>
                        <button class="nav-btn" style="margin-top: 30px; max-width:200px;" onclick="location.reload()">Take Again</button>
                    </div>`;
            });
    }

    loadQuestions();
</script>


<?php include 'footer2.php'; ?></body>
</html>