<script>
let current = 1;
let allQuestions = [];
let answers = {};
let totalSeconds = 3600;
let timerInterval;

// ... [Keep your existing htmlEscape, getAIPersonalizedHelp, and startTimer functions here] ...

function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;

    // Update UI
    document.getElementById('section-display').innerHTML = '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';
    
    const qc = document.getElementById('q-container');
    let html = `
        <div class="question-header">
            <span class="question-id">Question ${current}</span>
            <span class="question-section">${htmlEscape(qObj.sectionName)}</span>
        </div>
        <div class="question-text">${htmlEscape(qObj.question)}</div>
    `;
    if (qObj.image) html += `<img src="${htmlEscape(qObj.image)}" alt="Diagram"/>`;
    
    html += '<form id="qForm">';
    qObj.options.forEach(opt => {
        html += `
            <label class="option-label">
                <input type="radio" name="answer" value="${opt.optionId}" ${answers[qObj.questionId] === opt.optionId ? 'checked' : ''} onchange="saveAnswer('${qObj.questionId}', '${opt.optionId}')"/>
                <span>${opt.optionId}. ${htmlEscape(opt.text)}</span>
            </label>`;
    });
    html += '</form>';
    qc.innerHTML = html;

    // Navigation state logic
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const navButtons = document.getElementById('nav-buttons');
    
    btnPrev.disabled = (current <= 1);
    
    // Manage Submit vs Next
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
        if (existingSubmit) existingSubmit.remove();
        btnNext.style.display = 'inline-flex';
        btnNext.disabled = false;
    }
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

// SAFE INITIALIZATION
document.addEventListener('DOMContentLoaded', () => {
    // Attach listeners once on load
    document.getElementById('btn-prev').onclick = () => { if(current > 1) navigate(current - 1); };
    document.getElementById('btn-next').onclick = () => { if(current < allQuestions.length) navigate(current + 1); };
    
    loadQuestions();
});

// ... [Keep your existing saveAnswer, renderNav, loadQuestions, submitAnswers, viewExplanations functions here] ...
</script>
