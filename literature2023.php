<?php
// literature2023.php

// NOTE: This page assumes literatureinenglish2023.json is in the same directory and publicly accessible
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector — Literature in English WAEC 2023</title>
  <style>
    /* ... [STYLES UNCHANGED: omitted here for brevity, keep all your original CSS] ... */
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
      <h1>Vector — Literature in English WAEC 2023</h1>
      <p>Grow smarter, one step at a time.</p>
    </div>

    <div class="form-box">
      Time Remaining: <span id="countdown">--:--</span>
    </div>

    <div class="question-container" role="main" aria-live="polite" id="q-container">
      <!-- Questions will be injected here via JS -->
    </div>

    <div class="navigation" id="nav-buttons">
      <button class="nav-btn" id="btn-prev">← Previous</button>
      <button class="nav-btn" id="btn-next">Next →</button>
    </div>

    <div class="question-nav" id="q-nav">
      <!-- Question number links go here -->
    </div>
  </div>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  let current = parseInt(urlParams.get('q')) || 1;
  let duration = parseInt(urlParams.get('duration')) || 30; // default 30 minutes

  let questions = [];
  let answers = {};

  function loadJSON() {
    fetch('literatureinenglish2023.json')
      .then(res => {
        if (!res.ok) throw new Error('Cannot load JSON');
        return res.json();
      })
      .then(data => {
        questions = data;
        renderQuestion();
        renderNav();
        startTimer();
      })
      .catch(err => {
        const qc = document.getElementById('q-container');
        qc.innerHTML = '<p style="color:red;">Error loading questions. Make sure <code>literatureinenglish2023.json</code> exists and is accessible.</p>';
        console.error(err);
      });
  }

  function renderQuestion() {
    const idx = current - 1;
    const qObj = questions[idx];
    if (!qObj) {
      document.getElementById('q-container').innerHTML = '<p>No question found.</p>';
      return;
    }

    const qc = document.getElementById('q-container');

    let passageHTML = '';
    if (qObj.passage) {
      passageHTML = `<div style="font-style: italic; margin-bottom: 12px; color: #666;">${qObj.passage}</div>`;
    }

    let html = `<div class="question-text">Q${qObj.number}. ${qObj.question}</div>${passageHTML}`;

    html += '<form id="qForm">';
    for (let opt in qObj.options) {
      html += `
        <label class="option-label">
          <input type="radio" name="answer" value="${opt}" 
            ${answers[current] === opt ? 'checked' : ''} />
          ${opt}. ${qObj.options[opt]}
        </label>`;
    }
    html += '</form>';
    qc.innerHTML = html;

    document.getElementById('btn-prev').disabled = (current <= 1);
    document.getElementById('btn-next').disabled = (current >= questions.length);

    const nav = document.getElementById('nav-buttons');
    const existingSubmit = document.getElementById('btn-submit');
    if (current === questions.length && !existingSubmit) {
      const submitBtn = document.createElement('button');
      submitBtn.textContent = "Submit";
      submitBtn.id = "btn-submit";
      submitBtn.className = "nav-btn";
      submitBtn.onclick = submitAnswers;
      nav.appendChild(submitBtn);
    } else if (current < questions.length && existingSubmit) {
      existingSubmit.remove();
    }
  }

  function renderNav() {
    const navDiv = document.getElementById('q-nav');
    let html = '';
    for (let i = 1; i <= questions.length; i++) {
      html += `<a href="?q=${i}&duration=${duration}" class="${i === current ? 'active' : ''}">${i}</a>`;
    }
    navDiv.innerHTML = html;
  }

  document.getElementById('btn-prev').addEventListener('click', () => {
    saveAnswer();
    if (current > 1) {
      current--;
      renderQuestion();
      renderNav();
    }
  });

  document.getElementById('btn-next').addEventListener('click', () => {
    saveAnswer();
    if (current < questions.length) {
      current++;
      renderQuestion();
      renderNav();
    }
  });

  function saveAnswer() {
    const form = document.getElementById('qForm');
    if (!form) return;
    const selected = form.answer ? form.answer.value : null;
    if (selected) {
      answers[current] = selected;
    }
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
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
  }

  function submitAnswers() {
    saveAnswer();

    let score = 0;
    let total = questions.length;

    for (let i = 0; i < total; i++) {
      const q = questions[i];
      const userAnswer = answers[q.number] || answers[i + 1];
      if (q.answer && userAnswer === q.answer) {
        score++;
      }
    }

    // Hide question UI
    document.querySelector('.question-container').style.display = 'none';
    document.getElementById('nav-buttons').style.display = 'none';
    document.getElementById('q-nav').style.display = 'none';

    // Show result
    const container = document.querySelector('.container');
    const resultBox = document.createElement('div');
    resultBox.className = 'question-container';
    resultBox.style.textAlign = 'center';

    // Show score and explanation per question below score
    let explanationsHTML = '<hr><h3>Review</h3><ul style="text-align:left;">';
    for (let i = 0; i < total; i++) {
      const q = questions[i];
      const userAnswer = answers[q.number] || answers[i + 1];
      const correct = (userAnswer === q.answer);
      explanationsHTML += `<li><strong>Q${q.number}:</strong> Your answer: <em>${userAnswer || 'No answer'}</em> — ${correct ? '<span style="color:green;">Correct</span>' : '<span style="color:red;">Incorrect</span>'}
      <br><small>Explanation: ${q.explanation || 'N/A'}</small></li>`;
    }
    explanationsHTML += '</ul>';

    resultBox.innerHTML = `
      <div style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">
        Test Submitted!<br />You scored ${score} out of ${total}.
      </div>
      ${explanationsHTML}
      <button class="nav-btn" onclick="location.href='literature2023.php'">Retake Quiz</button>
    `;
    container.appendChild(resultBox);
  }

  // Initialize app
  loadJSON();
</script>



<?php include 'footer2.php'; ?>
</body>
</html>
