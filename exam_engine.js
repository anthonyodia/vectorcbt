let current = 1;
let allQuestions = [];
let answers = {};
let totalSeconds = 3600;
let timerInterval;

function htmlEscape(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Universal AI Helper
async function getAIPersonalizedHelp(qId, qText, correct, expl) {
    const box = document.getElementById('ai-box-' + qId);
    if (!box) return;
    box.innerHTML = '<em>Thinking...</em>';
    try {
        const res = await fetch('?action=get_ai_help', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ question: qText, correctAnswer: correct, explanation: expl })
        });
        const data = await res.json();
        const content = data.choices[0].message.content;
        box.innerHTML = '<strong>AI Tutor:</strong><br>' + content.replace(/\n/g, '<br>');
    } catch (e) {
        box.innerHTML = 'AI assistance currently unavailable.';
    }
}

// Logic to render questions (Standardized)
function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;

    // Set Section Header
    const sectionDisplay = document.getElementById('section-display');
    if (sectionDisplay) sectionDisplay.innerHTML = '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';

    const qc = document.getElementById('q-container');
    let html = `
        <div class="question-header">
            <span class="question-id">Question ${current}</span>
            <span class="question-section">${htmlEscape(qObj.sectionName)}</span>
        </div>
        <div class="question-text">${htmlEscape(qObj.question)}</div>
    `;
    if (qObj.image || qObj.imageUrl) {
        html += `<img src="${htmlEscape(qObj.image || qObj.imageUrl)}" class="question-image" alt="Diagram"/>`;
    }
    
    html += '<form id="qForm">';
    qObj.options.forEach(opt => {
        html += `
            <label class="option-label">
                <input type="radio" name="answer" value="${opt.optionId}" 
                ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''} 
                onchange="saveAnswer('${qObj.questionId}', '${opt.optionId}')"/>
                <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
            </label>`;
    });
    html += '</form>';
    qc.innerHTML = html;

    // Navigation logic
    document.getElementById('btn-prev').disabled = (current <= 1);
    const btnNext = document.getElementById('btn-next');
    const navButtons = document.getElementById('nav-buttons');
    let existingSubmit = document.getElementById('btn-submit');

    if (current === allQuestions.length) {
        if (!existingSubmit) {
            const submitBtn = document.createElement('button');
            submitBtn.textContent = 'Submit Exam';
            submitBtn.id = 'btn-submit';
            submitBtn.className = 'nav-btn';
            submitBtn.style.background = '#ff6b6b';
            submitBtn.onclick = submitAnswers;
            navButtons.appendChild(submitBtn);
            btnNext.style.display = 'none';
        }
    } else {
        if (existingSubmit) existingSubmit.remove();
        btnNext.style.display = 'inline-flex';
        btnNext.disabled = false;
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
        html += `<a href="#" onclick="navigate(${num}); return false;" class="${cls}">${num}</a>`;
    });
    document.getElementById('q-nav').innerHTML = html;
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

// Timer and Initialization
function startTimer() {
    timerInterval = setInterval(() => {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        const el = document.getElementById('countdown');
        if (el) el.textContent = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
        if (totalSeconds <= 0) { clearInterval(timerInterval); submitAnswers(); }
        totalSeconds--;
    }, 1000);
}

async function loadQuestions() {
    const res = await fetch('?action=get_questions');
    const data = await res.json();
    if (data.success) {
        allQuestions = data.questions;
        renderQuestion();
        renderNav();
        startTimer();
    }
}

async function submitAnswers() {
    clearInterval(timerInterval);
    const res = await fetch('?action=submit', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ answers: answers })
    });
    const result = await res.json();
    viewExplanations(result);
}

async function viewExplanations(result) {
    document.getElementById('q-container').style.display = 'none';
    document.getElementById('nav-buttons').style.display = 'none';
    document.getElementById('q-nav').style.display = 'none';
    const secDisplay = document.getElementById('section-display');
    if (secDisplay) secDisplay.style.display = 'none';
    
    const infoBox = document.getElementById('info-box');
    if (infoBox) {
        infoBox.innerHTML = `Exam Results: ${result.score}/${result.total} (${result.percentage}%)`;
        infoBox.style.background = '#007aff';
    }

    const expContainer = document.getElementById('explanation-container');
    expContainer.style.display = 'block';
    
    const res = await fetch('?action=get_explanations');
    const data = await res.json();

    let html = '<div class="explanation-view">';
    data.questions.forEach((q, i) => {
        const isCorrect = answers[q.questionId] === q.correctAnswer;
        html += `
            <div class="question-container" id="container-${q.questionId}">
                <div class="question-header">
                    <span class="question-id">Q${i+1} — ${isCorrect ? '✅' : '❌'}</span>
                </div>
                <div class="question-text">${htmlEscape(q.question)}</div>
                ${q.options.map(opt => {
                    let cls = '';
                    if(opt.optionId === q.correctAnswer) cls = 'correct-answer-label';
                    else if(opt.optionId === answers[q.questionId]) cls = 'user-answer-label';
                    return `<label class="option-label ${cls}"><span>${opt.optionId}. ${htmlEscape(opt.text)}</span></label>`;
                }).join('')}
                <div class="explanation-box"><strong>Explanation:</strong> <p>${htmlEscape(q.explanation)}</p></div>
                ${!isCorrect ? `<div id="ai-box-${q.questionId}" class="ai-box"></div>` : ''}
            </div>`;
    });

    html += `
        <div style="text-align: center; margin: 30px 0;">
            <a href="choose_subject.php" class="nav-btn" style="background:#007aff;">← Back to Subjects</a>
        </div></div>`;

    expContainer.innerHTML = html;

    data.questions.forEach(q => {
        if (answers[q.questionId] !== q.correctAnswer) {
            getAIPersonalizedHelp(q.questionId, q.question, q.correctAnswer, q.explanation);
        }
    });
    window.scrollTo(0,0);
}

window.onload = loadQuestions;
