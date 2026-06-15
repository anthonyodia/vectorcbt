<script>
let current = 1;
let allQuestions = [];
let answers = {};
let totalSeconds = 3600;
let timerInterval;

// 1. Core Logic
async function loadQuestions() {
    try {
        const res = await fetch('?action=get_questions');
        const data = await res.json();
        if (data.success) {
            allQuestions = data.questions;
            
            // Attach button listeners ONLY after data is loaded and buttons exist
            document.getElementById('btn-prev').onclick = () => { if(current > 1) navigate(current - 1); };
            document.getElementById('btn-next').onclick = () => { if(current < allQuestions.length) navigate(current + 1); };
            
            renderQuestion();
            renderNav();
            startTimer();
        }
    } catch (e) {
        console.error("Critical Load Error:", e);
    }
}

function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) return;

    // Refresh UI
    document.getElementById('section-display').innerHTML = '<div class="section-divider">Section: ' + htmlEscape(qObj.sectionName) + '</div>';
    
    // ... [Insert your existing form generation logic here] ...
    
    // Button Logic
    const btnNext = document.getElementById('btn-next');
    const btnSubmit = document.getElementById('btn-submit');
    
    document.getElementById('btn-prev').disabled = (current <= 1);
    
    if (current === allQuestions.length) {
        btnNext.style.display = 'none';
        // Ensure submit button exists
        if (!document.getElementById('btn-submit')) {
            const submitBtn = document.createElement('button');
            submitBtn.id = 'btn-submit';
            submitBtn.className = 'nav-btn';
            submitBtn.textContent = 'Submit Exam';
            submitBtn.style.background = '#ff6b6b';
            submitBtn.onclick = submitAnswers;
            document.getElementById('nav-buttons').appendChild(submitBtn);
        }
    } else {
        btnNext.style.display = 'inline-flex';
        btnNext.disabled = false;
        const sub = document.getElementById('btn-submit');
        if (sub) sub.remove();
    }
}

function navigate(num) {
    current = num;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
}

// Ensure the page starts the process
window.onload = loadQuestions;

// ... [Include your helper functions: htmlEscape, saveAnswer, renderNav, startTimer, submitAnswers, viewExplanations, getAIPersonalizedHelp] ...
</script>
