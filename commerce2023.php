<?php
// commerce2023.php
// NOTE: This page relies entirely on client-side JS (fetch) for data loading and scoring.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector — Commerce 2023 CBT</title>
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
            max-width: 1000px; /* Increased max width for better viewing */
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
            padding: 20px;
            border: 1px solid #e6eaf0;
            border-radius: 12px;
            background: #fafafa;
            min-height: 300px;
        }
        
        /* New styles for explanation view */
        .explanation-view .question-container {
            background: #ffffff;
            border: 1px solid #cceeff;
            margin-bottom: 20px;
        }

        .question-text {
            font-size: 17px;
            font-weight: 600;
            color: #243246;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        label.option-label {
            display: block;
            font-size: 15px;
            color: #1e2a3a;
            margin-bottom: 12px;
            cursor: pointer;
            user-select: none;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        /* Explanation View Overrides */
        .explanation-view label.option-label {
            cursor: default;
        }
        .explanation-view input[type="radio"] {
            display: none; /* Hide radios in explanation view */
        }
        
        label.option-label:hover {
            background: #f0f7ff;
            border-color: #007aff;
        }
        
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
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #e3f2fd;
            border-left: 5px solid #2196F3;
        }

        .explanation-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        /* End Explanation View Styles */

        input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
            accent-color: #007aff;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin: 30px 25px;
            gap: 10px;
        }

        .nav-btn {
            background: #43e97b;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-btn:hover:not(.disabled) {
            background: #38c172;
        }

        .nav-btn.disabled {
            background: #b2d6be;
            cursor: default;
            pointer-events: none;
        }

        .question-nav {
            text-align: center;
            margin: 30px 0 40px 0;
            padding: 0 25px;
            overflow-x: auto;
        }

        .question-nav a {
            display: inline-block;
            margin: 0 4px;
            min-width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 50%;
            background: #f7f7f7;
            color: #007aff;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            user-select: none;
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
        }

        .question-nav a.active {
            background: #007aff;
            color: white;
        }

        .question-nav a:hover {
            background: #e6f0ff;
        }
        
        .question-nav a.answered {
            background: #4caf50;
            color: white;
        }
        
        .nav-button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .question-id {
            font-size: 14px;
            color: #007aff;
            font-weight: 600;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

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
            <h1>Vector — <span id="exam-title">Commerce 2023</span></h1>
            <p>Grow smarter, one step at a time.</p>
        </div>

        <div class="form-box" id="info-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

        <div class="question-container" role="main" aria-live="polite" id="q-container">
             <p class="loading">Loading questions...</p>
        </div>
        
        <div id="explanation-container" style="display: none; margin: 0 25px;"></div>

        <div class="navigation" id="nav-buttons">
            <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
            <button class="nav-btn" id="btn-next" disabled>Next →</button>
        </div>

        <div class="question-nav" id="q-nav">
            </div>
    </div>

<script>
    // --- Configuration ---
    const jsonFile = 'commerce2023.json';
    
    // --- Global State ---
    const urlParams = new URLSearchParams(window.location.search);
    let current = parseInt(urlParams.get('q')) || 1;
    let duration = parseInt(urlParams.get('duration')) || 60; // Default to 60 minutes
    
    let questions = [];
    // Answers stored by question 'number' key from JSON
    let answers = {}; 
    let totalQuestions = 0;
    let timerInterval;

    function htmlEscape(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // --- Data Loading ---
    async function loadJSON() {
        try {
            const res = await fetch(jsonFile);
            if (!res.ok) throw new Error('Cannot load JSON file: ' + jsonFile);
            
            let data = await res.json();
            
            // *** JSON PATTERN ADAPTATION ***
            // Expecting: a root-level array of question objects (as per the structure provided)
            if (Array.isArray(data)) {
                 questions = data;
            } else if (data.question_set && Array.isArray(data.question_set)) {
                 questions = data.question_set; // Fallback for previous structure
            } else {
                 throw new Error("JSON format invalid. Expected a root array of questions.");
            }
            // *** END ADAPTATION ***

            totalQuestions = questions.length;
            document.getElementById('exam-title').textContent = 'Commerce 2023'; 
            
            renderQuestion();
            renderNav();
            startTimer();

        } catch (err) {
            const qc = document.getElementById('q-container');
            qc.innerHTML = `<div style="padding: 20px; color: red;">Error: ${err.message}</div>`;
            console.error(err);
        }
    }

    // --- Rendering Functions ---
    function renderQuestion() {
        const idx = current - 1;
        const qObj = questions[idx];
        if (!qObj) {
            document.getElementById('q-container').innerHTML = '<p>No question available.</p>';
            return;
        }
        
        // Key mapping: question ID is qObj.number, text is qObj.instruction
        const qId = qObj.number; 
        const qc = document.getElementById('q-container');
        
        let html = '<div class="question-header">';
        html += `<span class="question-id">Q${qId}</span>`;
        // No section name in this JSON, so we leave this blank or use a default
        html += `<span class="question-section">Commerce</span>`; 
        html += '</div>';

        html += `<div class="question-text">${htmlEscape(qObj.instruction)}</div>`;

        if (qObj.image) {
            html += `<div style="text-align:center; margin-bottom: 15px;">
                      <img src="${htmlEscape(qObj.image)}" alt="Question Image" style="max-width:100%; height:auto; border-radius:8px;" />
                     </div>`;
        }

        html += '<form id="qForm">';
        for (let opt in qObj.options) {
            const optText = qObj.options[opt];
            html += `
                <label class="option-label">
                    <input type="radio" name="answer" value="${htmlEscape(opt)}" 
                        ${answers[qId] === opt ? 'checked' : ''} />
                    <span>${htmlEscape(opt)}. ${htmlEscape(optText)}</span>
                </label>`;
        }
        html += `</form>`;
        qc.innerHTML = html;

        document.getElementById('btn-prev').disabled = (current <= 1);
        
        const btnNext = document.getElementById('btn-next');
        btnNext.disabled = (current >= totalQuestions);

        const nav = document.getElementById('nav-buttons');
        const existingSubmit = document.getElementById('btn-submit');
        
        // Show/Hide Submit Button
        if (current === totalQuestions) {
            if (!existingSubmit) {
                const submitBtn = document.createElement('button');
                submitBtn.textContent = 'Submit Exam';
                submitBtn.id = 'btn-submit';
                submitBtn.className = 'nav-btn';
                submitBtn.style.background = '#ff6b6b';
                submitBtn.onclick = submitAnswers;
                nav.appendChild(submitBtn);
                btnNext.style.display = 'none'; 
            }
        } else {
            if (existingSubmit) existingSubmit.remove();
            btnNext.style.display = 'inline-flex';
        }
        
        // Ensure UI is visible
        qc.style.display = 'block';
        nav.style.display = 'flex';
        document.getElementById('q-nav').style.display = 'block';
    }

    function renderNav() {
        const navDiv = document.getElementById('q-nav');
        let html = '';
        for (let i = 1; i <= totalQuestions; i++) {
             const qId = questions[i-1].number; 
             const answered = answers[qId] ? 'answered' : '';
             const active = i === current ? 'active' : '';
             // Use navigate function for clean state management
             html += `<a href="#" onclick="navigate(${i}); return false;" class="${active} ${answered}">${i}</a>`;
        }
        navDiv.innerHTML = html;
    }

    // --- Navigation & Answer Saving ---
    function navigate(num) {
        saveAnswer();
        current = num;
        renderQuestion();
        renderNav();
        window.scrollTo(0, 0);
    }
    
    document.getElementById('btn-prev').addEventListener('click', () => {
        saveAnswer();
        if (current > 1) navigate(current - 1);
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        saveAnswer();
        if (current < totalQuestions) navigate(current + 1);
    });

    function saveAnswer() {
        const form = document.getElementById('qForm');
        if (!form || !form.answer) return;
        
        const selected = form.answer.value || null;
        const qObj = questions[current - 1]; 
        const qId = qObj.number; // Use the question 'number' as the key
        
        if (selected) {
             answers[qId] = selected;
        } else if(answers[qId]) {
             // Clear if previously answered but now unchecked (rare for radio)
             // delete answers[qId]; 
        }
        renderNav(); // Update nav bar instantly
    }

    // --- Timer Logic ---
    function updateTimer() {
        const totalSeconds = duration * 60;
        const countdownElement = document.getElementById('countdown');

        // Logic relies on timerInterval being set globally
        if (!timerInterval) {
            timerInterval = setInterval(() => {
                const mins = Math.floor(totalSeconds / 60);
                const secs = totalSeconds % 60;
                const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                countdownElement.textContent = str;
                
                if (totalSeconds <= 0) {
                    clearInterval(timerInterval);
                    submitAnswers();
                    return;
                }
                duration = duration - (1 / 60); // Decrement duration by one second
            }, 1000);
            return;
        }

        // Timer update logic for subsequent calls (already running)
        const mins = Math.floor(duration);
        const secs = Math.floor((duration - mins) * 60);
        const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        countdownElement.textContent = str;
        
        if (duration <= 0) {
             clearInterval(timerInterval);
             submitAnswers();
        }
        
    }
    
    function startTimer() {
        // Start the continuous 1 second countdown and update
        timerInterval = setInterval(() => {
            if (duration <= 0) {
                clearInterval(timerInterval);
                submitAnswers();
                return;
            }
            duration -= 1 / 60; // Decrement by one minute per 60 seconds
            
            const mins = Math.floor(duration);
            const secs = Math.floor((duration - mins) * 60);
            const str = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            document.getElementById('countdown').textContent = str;
            
        }, 1000);
    }
    
    // --- Submission & Results Logic ---
    function submitAnswers() {
        saveAnswer();
        if(timerInterval) clearInterval(timerInterval);

        let score = 0;
        let total = questions.length;

        // SCORING LOOP: Use 'number' key for lookup
        questions.forEach(q => {
            const userAnswer = answers[q.number]; 
            if (q.answer && userAnswer === q.answer) {
                score++;
            }
        });
        
        const percentage = total > 0 ? Math.round((score / total) * 100) : 0;
        
        displayResults(score, total, percentage);
    }

    function displayExplanations(container) {
        let html = '<div class="explanation-view">';

        questions.forEach((qObj) => {
            const qId = qObj.number;
            const userAnswer = answers[qId]; 
            const isCorrect = userAnswer === qObj.answer;
            
            html += '<div class="question-container">';
            html += '<div class="question-header">';
            html += `<span class="question-id">Q${qId} — ${isCorrect ? 'Correct ✅' : 'Incorrect ❌'}</span>`;
            html += `<span class="question-section">Commerce</span>`;
            html += '</div>';

            html += `<div class="question-text">${htmlEscape(qObj.instruction)}</div>`;
            
            // Render options with class highlighting
            for (let opt in qObj.options) {
                let labelClass = '';
                if (opt === qObj.answer) {
                    labelClass = 'correct-answer-label';
                } else if (opt === userAnswer && opt !== qObj.answer) {
                    labelClass = 'user-answer-label';
                }

                html += `<label class="option-label ${labelClass}">`;
                html += `<span>${htmlEscape(opt)}. ${htmlEscape(qObj.options[opt])}</span>`;
                html += '</label>';
            }

            if (qObj.explanation) {
                html += `<div class="explanation-box"><strong>Explanation:</strong> ${htmlEscape(qObj.explanation)}</div>`;
            }

            html += '</div>';
        });

        html += '</div>';
        
        html += '<div class="nav-button-group">';
        html += '<button class="nav-btn" onclick="location.reload()">🔄 Retake Exam</button>';
        html += '</div>';

        container.innerHTML = html;
        window.scrollTo(0, 0); 
    }
    
    function displayResults(score, total, percentage) {
        document.getElementById('q-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.getElementById('info-box').innerHTML = 'Exam Completed!';

        const container = document.querySelector('.container');
        const resultBox = document.createElement('div');
        resultBox.className = 'question-container';
        resultBox.id = 'results-view'; 
        resultBox.style.textAlign = 'center';
        
        resultBox.innerHTML = `
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Results</div>
            <div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">${score}/${total}</div>
            <div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">${percentage}%</div>
            <div style="font-size: 16px; color: #555; margin-bottom: 30px;">You answered ${score} out of ${total} questions correctly.</div>
            
            <div class="nav-button-group">
                <button class="nav-btn" style="background: #007aff;" id="btn-view-exp">View Explanation</button>
                <button class="nav-btn" onclick="location.reload()">🔄 Retake Exam</button>
            </div>
        `;
        
        container.appendChild(resultBox);
        
        // Setup explanation button handler
        document.getElementById('btn-view-exp').addEventListener('click', (e) => {
             e.target.style.display = 'none';
             document.getElementById('results-view').style.display = 'none';
             const explanationContainer = document.getElementById('explanation-container');
             explanationContainer.style.display = 'block';
             displayExplanations(explanationContainer);
        });
    }

    // Initialize app
    loadJSON();
</script>



<?php include 'footer.php'; ?></body>
</html>