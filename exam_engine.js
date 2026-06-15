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
        // Check for specific Groq/OpenAI response structure
        const content = data.choices ? data.choices[0].message.content : "AI response error.";
        box.innerHTML = '<strong>AI Tutor:</strong><br>' + content.replace(/\n/g, '<br>');
    } catch (e) {
        box.innerHTML = 'AI assistance currently unavailable.';
    }
}

function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;

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
                ${answers[qObj.questionId] == opt.optionId ? 'checked' : ''} 
                onchange="saveAnswer('${qObj.questionId}', '${opt.optionId}')"/>
                <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
            </label>`;
    });
    html += '</form>';
    qc.innerHTML = html;

    // Navigation logic
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const navButtons = document.getElementById('nav-buttons');
    
    btnPrev.disabled = (current <= 1);
    
    // Handle Submit Button
    let existingSubmit = document.getElementById('btn-submit');
    if (current === allQuestions.length) {
        btnNext.style.display = 'none';
        if (!existingSubmit) {
            const submitBtn = document.createElement('button');
            submitBtn.textContent = 'Submit Exam';
            submitBtn.id = 'btn-submit';
            submitBtn.className = 'nav-btn';
            submitBtn.style.background = '#ff6b6b';
            submitBtn.onclick = submitAnswers;
            navButtons.appendChild(submitBtn);
        }
    } else {
        btnNext.style.display = 'inline-flex';
        btnNext.disabled = false;
        if (existingSubmit) existingSubmit.remove();
    }
}

function saveAnswer(qId, val) {
    answers[qId] = val;
    renderNav();
}

function renderNav() {
    const nav = document.getElementById('q-nav');
    if (!nav) return;
    let html = '';
    allQuestions.forEach((q, i) => {
        const num = i + 1;
        const cls = (num === current ? 'active ' : '') + (answers[q.questionId] ? 'answered' : '');
        html += `<a href="#" onclick="navigate(${num}); return false;" class="${cls}">${num}</a>`;
    });
    nav.innerHTML = html;
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

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
    try {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if (data.success) {
            allQuestions = data.questions;
            renderQuestion();
            renderNav();
            startTimer();
        }
    } catch (e) {
        document.getElementById('q-container').innerHTML = '<p class="error">Failed to load questions. Please check your PHP file.</p>';
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

// Initialization
document.addEventListener('DOMContentLoaded', loadQuestions);
