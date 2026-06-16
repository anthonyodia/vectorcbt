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

/* ================= AI TUTOR ================= */
async function getAIPersonalizedHelp(qId, qText, correct, expl) {

    const box = document.getElementById('ai-box-' + qId);
    if (!box) return;

    box.innerHTML = '<em>Thinking...</em>';

    try {
        const res = await fetch('English2025.php?action=get_ai_help', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                question: qText,
                correctAnswer: correct,
                explanation: expl
            })
        });

        const data = await res.json();

        const reply =
            data?.choices?.[0]?.message?.content ||
            data?.error ||
            "AI not available";

        box.innerHTML =
            '<strong>AI Tutor:</strong><br>' +
            reply.replace(/\n/g, '<br>');

    } catch (e) {
        console.error(e);
        box.innerHTML = "AI assistance failed.";
    }
}

/* ================= LOAD QUESTIONS ================= */
async function loadQuestions() {
    try {
        const res = await fetch('English2025.php?action=get_questions');
        const data = await res.json();

        if (!data.success) return;

        allQuestions = data.questions;

        document.getElementById('btn-prev').onclick = () => {
            if (current > 1) navigate(current - 1);
        };

        document.getElementById('btn-next').onclick = () => {
            if (current < allQuestions.length) navigate(current + 1);
        };

        renderQuestion();
        renderNav();
        startTimer();

    } catch (e) {
        console.error("Load error:", e);
    }
}

/* ================= RENDER QUESTION ================= */
function renderQuestion() {

    const qObj = allQuestions[current - 1];
    if (!qObj) return;

    document.getElementById('section-display').innerHTML =
        `<div class="section-divider">Section: ${htmlEscape(qObj.section)}</div>`;

    const qc = document.getElementById('q-container');

    let html = `
        <div class="question-header">
            <span class="question-id">Question ${current}</span>
            <span class="question-section">${htmlEscape(qObj.section)}</span>
        </div>

        <div class="question-text">
            ${htmlEscape(qObj.question)}
            ${qObj.image ? `<br><img src="${qObj.image}" class="question-image">` : ''}
        </div>

        <form>
    `;

    qObj.options.forEach(opt => {

        html += `
            <label class="option-label">
                <input type="radio"
                    name="answer"
                    value="${opt.optionId}"
                    ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''}
                    onchange="saveAnswer('${qObj.questionId}', '${opt.optionId}')">

                <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
            </label>
        `;
    });

    html += `</form>`;

    qc.innerHTML = html;

    document.getElementById('btn-prev').disabled = (current <= 1);

    const btnNext = document.getElementById('btn-next');

    if (current === allQuestions.length) {
        btnNext.style.display = 'none';

        if (!document.getElementById('btn-submit')) {
            const btn = document.createElement('button');
            btn.id = 'btn-submit';
            btn.className = 'nav-btn';
            btn.textContent = 'Submit Exam';
            btn.style.background = '#ff6b6b';
            btn.onclick = submitAnswers;

            document.getElementById('nav-buttons').appendChild(btn);
        }

    } else {
        btnNext.style.display = 'inline-flex';

        const sub = document.getElementById('btn-submit');
        if (sub) sub.remove();
    }
}

/* ================= ANSWERS ================= */
function saveAnswer(qId, value) {
    answers[qId] = value;
    renderNav();
}

/* ================= NAV ================= */
function renderNav() {

    let html = '';

    allQuestions.forEach((q, i) => {

        const num = i + 1;

        const cls =
            (num === current ? 'active ' : '') +
            (answers[q.questionId] ? 'answered' : '');

        html += `
            <a href="#" onclick="navigate(${num});return false;" class="${cls}">
                ${num}
            </a>
        `;
    });

    document.getElementById('q-nav').innerHTML = html;
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

/* ================= TIMER ================= */
function startTimer() {
    timerInterval = setInterval(() => {

        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;

        document.getElementById('countdown').textContent =
            String(mins).padStart(2, '0') + ':' +
            String(secs).padStart(2, '0');

        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            submitAnswers();
        }

        totalSeconds--;

    }, 1000);
}

/* ================= SUBMIT ================= */
async function submitAnswers() {

    clearInterval(timerInterval);

    const res = await fetch('English2025.php?action=submit', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers})
    });

    const result = await res.json();
    viewExplanations(result);
}

/* ================= EXPLANATIONS ================= */
async function viewExplanations(result) {

   document.getElementById('q-container').style.display = 'none';
document.getElementById('nav-buttons').style.display = 'none';
document.getElementById('q-nav').style.display = 'none';
document.getElementById('section-display').style.display = 'none';
    
    document.getElementById('info-box').innerHTML =
        `Score: ${result.score}/${result.total} (${result.percentage}%)`;

    const res = await fetch('English2025.php?action=get_explanations');
    const data = await res.json();

    let html = '<div class="explanation-view">';

    data.questions.forEach((q, i) => {

        const isCorrect = answers[q.questionId] === q.correctAnswer;

        html += `
            <div class="question-container">

                <div class="question-header">
                    <span class="question-id">
                        Q${i + 1} — ${isCorrect ? '✅' : '❌'}
                    </span>
                </div>

                <div class="question-text">
                    ${htmlEscape(q.question)}
                </div>

                ${q.options.map(opt => {

                    let cls = '';
                    if (opt.optionId === q.correctAnswer) cls = 'correct-answer-label';
                    else if (opt.optionId === answers[q.questionId]) cls = 'user-answer-label';

                    return `
                        <label class="option-label ${cls}">
                            <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
                        </label>
                    `;
                }).join('')}

                <div class="explanation-box">
                    <strong>Explanation:</strong>
                    <p>${htmlEscape(q.explanation)}</p>
                </div>

                ${!isCorrect ? `<div id="ai-box-${q.questionId}" class="explanation-box"></div>` : ''}

            </div>
        `;
    });

    html += '</div>';

    document.getElementById('explanation-container').innerHTML = html;
    document.getElementById('explanation-container').style.display = 'block';

    // trigger AI
    data.questions.forEach(q => {
        if (answers[q.questionId] !== q.correctAnswer) {
            getAIPersonalizedHelp(
                q.questionId,
                q.question,
                q.correctAnswer,
                q.explanation
            );
        }
    });
}

window.onload = loadQuestions;
