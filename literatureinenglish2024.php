<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector Learn — English Literature CBT</title>
  <style>
    /* === Your exact CSS from original design === */
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

    .passage-box {
      margin: 0 25px 25px 25px;
      padding: 20px;
      border: 2px solid #e6eaf0;
      border-radius: 12px;
      background: #f5f9fc;
      max-height: 400px;
      overflow-y: auto;
    }

    .passage-box h3 {
      margin: 0 0 15px 0;
      color: #243246;
      font-size: 16px;
      font-weight: 600;
    }

    .passage-text {
      font-size: 15px;
      line-height: 1.6;
      color: #333;
      white-space: pre-wrap;
      word-wrap: break-word;
    }

    .question-container {
      margin: 0 25px 25px 25px;
      padding: 20px;
      border: 1px solid #e6eaf0;
      border-radius: 12px;
      background: #fafafa;
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

    .question-section {
      font-size: 12px;
      color: #999;
      background: #f0f0f0;
      padding: 4px 8px;
      border-radius: 6px;
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

    label.option-label:hover {
      background: #f0f7ff;
      border-color: #007aff;
    }

    label.option-label input[type="radio"]:checked + span {
      color: #007aff;
      font-weight: 600;
    }

    label.option-label input[type="radio"] {
      margin-right: 10px;
      cursor: pointer;
      accent-color: #007aff;
    }

    .explanation-box {
      margin-top: 20px;
      padding: 15px;
      background: #e8f5e9;
      border-left: 4px solid #4caf50;
      border-radius: 6px;
      display: none;
    }

    .explanation-box.show {
      display: block;
    }

    .explanation-box h4 {
      margin: 0 0 8px 0;
      color: #2e7d32;
      font-size: 14px;
    }

    .explanation-box p {
      margin: 0;
      color: #1b5e20;
      font-size: 14px;
      line-height: 1.5;
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

    .section-divider {
      margin: 30px 25px 0 25px;
      padding: 15px 20px;
      background: #f0f0f0;
      border-left: 4px solid #007aff;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      color: #243246;
    }

    @media (max-width: 768px) {
      .container {
        margin: 20px;
      }

      .passage-box {
        max-height: 300px;
      }

      .title h1 {
        font-size: 24px;
      }

      .question-text {
        font-size: 15px;
      }

      .navigation {
        flex-direction: column;
      }

      .nav-btn {
        width: 100%;
      }
    }
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
      <h1>Vector Learn — English Literature 2024</h1>
      <p>Master literature, one question at a time.</p>
    </div>

    <div class="form-box">
      Time Remaining: <span id="countdown">--:--</span>
    </div>

    <div id="section-display"></div>

    <div id="passage-display"></div>

    <div class="question-container" role="main" aria-live="polite" id="q-container">
      <p>Loading questions...</p>
    </div>

    <div class="navigation" id="nav-buttons">
      <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
      <button class="nav-btn" id="btn-next" disabled>Next →</button>
    </div>

    <div class="question-nav" id="q-nav">
    </div>
  </div>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  let current = parseInt(urlParams.get('q')) || 1;
  let duration = parseInt(urlParams.get('duration')) || 60; // 60 minutes default

  let allQuestions = [];
  let questionsData = [];
  let answers = {};
  let currentSection = null;

  function loadJSON() {
    // Load the literature JSON file
    fetch('literatureinenglish2024.json')
      .then(res => {
        if (!res.ok) throw new Error('Cannot load JSON');
        return res.json();
      })
      .then(data => {
        // Flatten all sections into a single questions array with section info
        if (data.sections && Array.isArray(data.sections)) {
          data.sections.forEach(section => {
            if (section.questions && Array.isArray(section.questions)) {
              section.questions.forEach(q => {
                q.sectionId = section.sectionId;
                q.sectionName = section.sectionName;
                q.passage = section.passage || null;
                allQuestions.push(q);
              });
            }
          });
          questionsData = data;
        }

        if (allQuestions.length > 0) {
          renderQuestion();
          renderNav();
          startTimer();
        } else {
          document.getElementById('q-container').innerHTML = '<p>Error: No questions found in JSON structure.</p>';
        }
      })
      .catch(err => {
        const qc = document.getElementById('q-container');
        qc.innerHTML = '<p>Error loading Literature questions. Make sure "literatureinenglish2024.json" is in the same folder and correctly formatted.</p>';
        console.error(err);
      });
  }

  function renderQuestion() {
    const qObj = allQuestions[current - 1];
    if (!qObj) {
      document.getElementById('q-container').innerHTML = '<p>No question found.</p>';
      return;
    }

    // Display section if changed
    if (currentSection !== qObj.sectionId) {
      currentSection = qObj.sectionId;
      const sectionDiv = document.getElementById('section-display');
      sectionDiv.innerHTML = `<div class="section-divider">📚 ${qObj.sectionName}</div>`;
    }

    // Display passage if available
    const passageDiv = document.getElementById('passage-display');
    if (qObj.passage) {
      passageDiv.innerHTML = `
        <div class="passage-box">
          <h3>Passage:</h3>
          <div class="passage-text">${escapeHtml(qObj.passage)}</div>
        </div>
      `;
    } else {
      passageDiv.innerHTML = '';
    }

    const qc = document.getElementById('q-container');
    let html = `
      <div class="question-header">
        <span class="question-id">Q${qObj.questionId}</span>
        <span class="question-section">${qObj.sectionName}</span>
      </div>
      <div class="question-text">${escapeHtml(qObj.question)}</div>
    `;
    
    html += '<form id="qForm">';
    
    // Handle both array and object options format
    let optionsArray = [];
    if (Array.isArray(qObj.options)) {
      optionsArray = qObj.options;
    } else if (typeof qObj.options === 'object') {
      optionsArray = Object.entries(qObj.options).map(([key, value]) => ({
        optionId: key,
        text: value
      }));
    }

    optionsArray.forEach(opt => {
      const optId = opt.optionId || opt.id;
      const optText = opt.text;
      html += `
        <label class="option-label">
          <input type="radio" name="answer" value="${optId}" 
            ${answers[current] === optId ? 'checked' : ''} />
          <span>${optId}. ${escapeHtml(optText)}</span>
        </label>`;
    });
    
    html += `</form>`;
    
    // Add explanation if answer is selected (optional - remove if not needed)
    html += `<div class="explanation-box" id="explanationBox"></div>`;
    
    qc.innerHTML = html;

    // Add change listener to show explanation
    const radios = document.querySelectorAll('input[name="answer"]');
    radios.forEach(radio => {
      radio.addEventListener('change', showExplanation);
    });

    document.getElementById('btn-prev').disabled = (current <= 1);
    
    const btnNext = document.getElementById('btn-next');
    btnNext.disabled = (current >= allQuestions.length);

    const nav = document.getElementById('nav-buttons');
    const existingSubmit = document.getElementById('btn-submit');
    
    // Show/hide Submit button on the last question
    if (current === allQuestions.length) {
      if (!existingSubmit) {
        const submitBtn = document.createElement('button');
        submitBtn.textContent = "Submit Exam";
        submitBtn.id = "btn-submit";
        submitBtn.className = "nav-btn";
        submitBtn.style.background = '#ff6b6b';
        submitBtn.onclick = submitAnswers;
        nav.appendChild(submitBtn);
      }
      btnNext.style.display = 'none';
    } else {
      if (existingSubmit) {
        existingSubmit.remove();
      }
      btnNext.style.display = 'inline-flex';
    }
  }

  function showExplanation() {
    const qObj = allQuestions[current - 1];
    const selected = document.querySelector('input[name="answer"]:checked')?.value;
    
    if (selected && qObj.explanation) {
      const expBox = document.getElementById('explanationBox');
      expBox.innerHTML = `
        <h4>💡 Explanation:</h4>
        <p>${escapeHtml(qObj.explanation)}</p>
      `;
      expBox.classList.add('show');
    }
  }

  function renderNav() {
    const navDiv = document.getElementById('q-nav');
    let html = '';
    for (let i = 1; i <= allQuestions.length; i++) {
      const answered = answers[i] ? 'answered' : '';
      const active = i === current ? 'active' : '';
      html += `<a href="#" onclick="navigate(${i}); return false;" class="${active} ${answered}">${i}</a>`;
    }
    navDiv.innerHTML = html;
  }
  
  function navigate(questionNumber) {
    saveAnswer();
    current = questionNumber;
    renderQuestion();
    renderNav();
    window.scrollTo(0, 0);
  }

  document.getElementById('btn-prev').addEventListener('click', () => {
    saveAnswer();
    if (current > 1) {
      current--;
      renderQuestion();
      renderNav();
      window.scrollTo(0, 0);
    }
  });

  document.getElementById('btn-next').addEventListener('click', () => {
    saveAnswer();
    if (current < allQuestions.length) {
      current++;
      renderQuestion();
      renderNav();
      window.scrollTo(0, 0);
    }
  });

  function saveAnswer() {
    const form = document.getElementById('qForm');
    if (!form) return;
    const selected = form.answer ? (Array.isArray(form.answer) 
      ? Array.from(form.answer).find(r => r.checked)?.value 
      : form.answer.value) : null;

    if (selected) {
      answers[current] = selected;
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  let totalSeconds = duration * 60;
  let timerInterval;

  function updateTimer() {
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    const str = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    document.getElementById('countdown').textContent = str;
    if (totalSeconds <= 0) {
      clearInterval(timerInterval);
      submitAnswers();
    } else {
      totalSeconds--;
    }
  }

  function startTimer() {
    if (allQuestions.length > 0) {
      updateTimer();
      timerInterval = setInterval(updateTimer, 1000);
    }
  }

  function submitAnswers() {
    saveAnswer();
    clearInterval(timerInterval);

    let score = 0;
    let total = allQuestions.length;
    let results = [];

    for (let i = 0; i < total; i++) {
      const q = allQuestions[i];
      const userAnswer = answers[i + 1];
      const isCorrect = userAnswer === q.correctAnswer;
      
      if (isCorrect) score++;
      
      results.push({
        questionId: q.questionId,
        question: q.question,
        userAnswer: userAnswer || 'Not Answered',
        correctAnswer: q.correctAnswer,
        isCorrect: isCorrect
      });
    }

    // Hide question UI
    document.querySelector('.question-container').style.display = 'none';
    document.getElementById('nav-buttons').style.display = 'none';
    document.getElementById('q-nav').style.display = 'none';
    document.getElementById('passage-display').style.display = 'none';
    document.getElementById('section-display').style.display = 'none';
    document.querySelector('.form-box').innerHTML = "Exam Completed!";

    // Show result
    const container = document.querySelector('.container');
    const percentage = Math.round((score / total) * 100);
    const resultBox = document.createElement('div');
    resultBox.className = 'question-container';
    resultBox.style.textAlign = 'center';
    resultBox.innerHTML = `
      <div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">
        🎓 Test Submitted!
      </div>
      <div style="font-size: 48px; color: #007aff; font-weight: bold; margin: 20px 0;">
        ${score}/${total}
      </div>
      <div style="font-size: 24px; color: #43e97b; margin-bottom: 30px;">
        ${percentage}%
      </div>
      <div style="font-size: 16px; color: #555; margin-bottom: 30px;">
        You answered ${score} questions correctly out of ${total}.
      </div>
      <button class="nav-btn" onclick="location.reload()" style="display: inline-block; width: auto;">Retake Exam</button>
    `;
    container.appendChild(resultBox);
  }

  // Start the app
  loadJSON();
</script>



<?php include 'footer2.php'; ?></body>
</html>