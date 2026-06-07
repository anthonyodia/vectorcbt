<?php
// commerce2021.php
// NOTE: This page relies entirely on client-side JS (fetch) for data loading and scoring.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector — WAEC Commerce 2021 CBT</title>
    <style>
        /* === UI/UX Consistency CSS === */
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #fefdfc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 40px auto;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
            padding-bottom: 20px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            background: #f7f7f7;
            font-size: 15px;
            border-bottom: 1px solid #eaeaea;
            border-radius: 40px;
            margin: 20px auto;
            width: 90%;
        }

        .steps span {
            flex: 1;
            text-align: center;
            padding: 6px;
            color: #aaa;
        }

        .steps .active {
            color: #007aff;
            font-weight: 600;
        }

        .title {
            text-align: center;
            margin-top: 10px;
        }

        .title h1 {
            font-size: 30px;
            margin: 0;
            color: #1e2a3a;
        }

        .title p {
            margin: 8px 0 22px 0;
            font-size: 15px;
            color: #555;
        }

        .form-box {
            background: linear-gradient(90deg, #4facfe, #43e97b);
            color: white;
            text-align: center;
            padding: 18px 15px;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 25px 30px 25px;
            border-radius: 12px;
        }

        .question-container {
            margin: 0 25px 25px 25px;
            padding: 25px;
            border: 1px solid #e6eaf0;
            border-radius: 12px;
            background: #fafafa;
            min-height: 300px;
        }

        .question-text {
            font-size: 19px;
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 40px; /* Requested 40px spacing */
            line-height: 1.5;
            border-left: 5px solid #007aff;
            padding-left: 15px;
        }

        label.option-label {
            display: block;
            font-size: 15px;
            color: #1e2a3a;
            margin-bottom: 12px;
            cursor: pointer;
            user-select: none;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        label.option-label:hover {
            background: #f0f7ff;
            border-color: #007aff;
        }

        input[type="radio"] {
            margin-right: 12px;
            cursor: pointer;
            accent-color: #007aff;
            transform: scale(1.2);
        }

        /* Result Highlighting */
        .correct-answer-label {
            border: 2px solid #4CAF50 !important;
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }
        
        .user-answer-label {
            border: 2px solid #FFC107 !important;
            background-color: #fff8e1 !important;
            font-weight: bold;
        }

        .explanation-box {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            background: #e3f2fd;
            border-left: 5px solid #2196F3;
            font-size: 14px;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin: 30px 25px;
            gap: 15px;
        }

        .nav-btn {
            background: #43e97b;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 14px 28px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-btn:hover:not(:disabled) { background: #38c172; }
        .nav-btn:disabled { background: #b2d6be; cursor: not-allowed; }

        .question-nav {
            text-align: center;
            margin: 20px 0 40px 0;
            padding: 0 25px;
        }

        .question-nav a {
            display: inline-block;
            margin: 5px;
            width: 36px;
            height: 36px;
            line-height: 36px;
            border-radius: 50%;
            background: #f7f7f7;
            color: #007aff;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }

        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; }
        table td { border: 1px solid #ddd; padding: 10px; font-weight: bold; }

        .loading { text-align: center; padding: 50px; font-size: 18px; color: #666; }
    </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>
    <div class="container">
        <div class="steps">
            <span>Step 1</span>
            <span>Step 2</span>
            <span class="active">Step 3: Begin!</span>
        </div>

        <div class="title">
            <h1>Vector — <span id="exam-title">WAEC Commerce 2021</span></h1>
            <p>Mastering Business and Trade Fundamentals.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">60:00</span>
        </div>

        <div id="q-container" class="question-container">
            <p class="loading">Initializing question bank...</p>
        </div>
        
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>

        <div class="navigation" id="nav-group">
            <button class="nav-btn" id="btn-prev" onclick="navigate(-1)">← Previous</button>
            <button class="nav-btn" id="btn-next" onclick="navigate(1)">Next →</button>
        </div>

        <div class="question-nav" id="q-nav"></div>
    </div>

<script>
    const jsonFile = 'commerce2021.json';
    let questions = [], answers = {}, current = 1, time = 3600, timer;

    async function init() {
        try {
            const res = await fetch(jsonFile);
            const data = await res.json();
            // Handle both {questions: []} and direct array formats
            questions = data.questions || data;
            render();
            startTimer();
        } catch (e) {
            document.getElementById('q-container').innerHTML = `<p style="color:red; text-align:center">Error loading ${jsonFile}. Please ensure the file exists.</p>`;
        }
    }

    function render() {
        const q = questions[current - 1];
        if (!q) return;

        const qc = document.getElementById('q-container');
        let html = `<div class="question-text">${q.question}</div><form id="qForm">`;
        
        for (let key in q.options) {
            html += `
                <label class="option-label">
                    <input type="radio" name="ans" value="${key}" onchange="save('${q.id}', '${key}')" ${answers[q.id] === key ? 'checked' : ''}>
                    ${key}. ${q.options[key]}
                </label>`;
        }
        qc.innerHTML = html + `</form>`;

        // Nav buttons logic
        document.getElementById('btn-prev').disabled = (current === 1);
        const nextBtn = document.getElementById('btn-next');
        if (current === questions.length) {
            nextBtn.innerHTML = "Submit Exam";
            nextBtn.style.background = "#ff6b6b";
            nextBtn.onclick = submit;
        } else {
            nextBtn.innerHTML = "Next →";
            nextBtn.style.background = "#43e97b";
            nextBtn.onclick = () => navigate(1);
        }
        updateNavNumbers();
    }

    function navigate(dir) {
        current += dir;
        render();
        window.scrollTo(0, 0);
    }

    function jump(n) {
        current = n;
        render();
    }

    function save(qid, val) {
        answers[qid] = val;
        updateNavNumbers();
    }

    function updateNavNumbers() {
        let h = '';
        questions.forEach((q, i) => {
            let cls = (current === i + 1) ? 'active' : (answers[q.id] ? 'answered' : '');
            h += `<a href="javascript:void(0)" onclick="jump(${i+1})" class="${cls}">${i+1}</a>`;
        });
        document.getElementById('q-nav').innerHTML = h;
    }

    function startTimer() {
        timer = setInterval(() => {
            let m = Math.floor(time / 60), s = time % 60;
            document.getElementById('countdown').textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if (time-- <= 0) submit();
        }, 1000);
    }

    function submit() {
        if (time > 0 && !confirm("Finish and see results?")) return;
        clearInterval(timer);
        
        let score = 0;
        questions.forEach(q => { if (answers[q.id] === q.answer) score++; });
        
        showResults(score);
    }

    function showResults(score) {
        document.getElementById('info-box').innerHTML = "Exam Completed";
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-group').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';

        const container = document.getElementById('explanation-container');
        container.style.display = 'block';
        
        let perc = Math.round((score / questions.length) * 100);
        let html = `
            <div class="question-container" style="text-align:center">
                <h2 style="font-size:32px; margin:0">Score: ${score} / ${questions.length}</h2>
                <div style="font-size:48px; color:#43e97b; font-weight:bold; margin:10px 0">${perc}%</div>
                <button class="nav-btn" onclick="location.reload()" style="max-width:200px; margin-top:10px">🔄 Retake</button>
            </div>
            <h3 style="margin-left:25px; color:#1a2a3a">Correction & Explanations</h3>`;

        questions.forEach((q, i) => {
            let userAns = answers[q.id];
            let isCorrect = userAns === q.answer;
            html += `
                <div class="question-container">
                    <div style="font-weight:bold; color:${isCorrect ? '#4caf50' : '#ff6b6b'}">Question ${i+1} ${isCorrect ? '✅' : '❌'}</div>
                    <div style="margin:15px 0">${q.question}</div>`;
            
            for (let key in q.options) {
                let state = '';
                if (key === q.answer) state = 'correct-answer-label';
                else if (key === userAns) state = 'user-answer-label';
                html += `<div class="option-label ${state}">${key}. ${q.options[key]}</div>`;
            }

            if (q.explanation) {
                html += `<div class="explanation-box"><strong>Explanation:</strong> ${q.explanation}</div>`;
            }
            html += `</div>`;
        });
        container.innerHTML = html;
        window.scrollTo(0, 0);
    }

    window.onload = init;
</script>


<?php include 'footer.php'; ?></body>
</html>