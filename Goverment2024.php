<?php
session_start();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'loadQuestions') {
        $jsonFile = 'government2024.json';
        
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $questions = json_decode($jsonData, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($questions)) {
                echo json_encode(['success' => true, 'questions' => $questions]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON format']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'government.json not found']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'saveAnswer') {
        $questionId = $_POST['questionId'] ?? null;
        $answer = $_POST['answer'] ?? null;
        
        if ($questionId && $answer) {
            $_SESSION['answers'][$questionId] = $answer;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'submitAnswers') {
        $jsonFile = 'government2024.json';
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $questions = json_decode($jsonData, true);
            
            $score = 0;
            $total = count($questions);
            
            foreach ($questions as $q) {
                $userAnswer = $_SESSION['answers'][$q['id']] ?? null;
                if ($userAnswer === $q['answer']) {
                    $score++;
                }
            }
            
            unset($_SESSION['answers']);
            
            echo json_encode([
                'success' => true,
                'score' => $score,
                'total' => $total,
                'percentage' => round(($score / $total) * 100, 2)
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'government.json not found']);
        }
        exit;
    }
}

if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}

$currentQuestion = isset($_GET['q']) ? (int)$_GET['q'] : 1;
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 30;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector Learn — Government CBT</title>
  <style>
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
      max-width: 900px;
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
    }

    .question-text {
      font-size: 18px;
      font-weight: 600;
      color: #243246;
      margin-bottom: 20px;
    }

    label.option-label {
      display: block;
      font-size: 16px;
      color: #1e2a3a;
      margin-bottom: 15px;
      cursor: pointer;
      user-select: none;
    }

    input[type="radio"] {
      margin-right: 10px;
      cursor: pointer;
    }

    .navigation {
      display: flex;
      justify-content: space-between;
      margin: 30px 25px;
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
    }

    .question-nav a {
      display: inline-block;
      margin: 0 6px;
      width: 34px;
      height: 34px;
      line-height: 34px;
      border-radius: 50%;
      background: #f7f7f7;
      color: #007aff;
      font-weight: 600;
      font-size: 16px;
      text-decoration: none;
      user-select: none;
      transition: background-color 0.3s, color 0.3s;
    }

    .question-nav a.active,
    .question-nav a:hover {
      background: #007aff;
      color: white;
    }

    .result-box {
      text-align: center;
      padding: 40px 20px;
    }

    .result-score {
      font-size: 48px;
      font-weight: bold;
      color: #43e97b;
      margin: 20px 0;
    }

    .result-details {
      font-size: 18px;
      color: #555;
      margin: 15px 0;
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
      <h1>Vector Learn — Government 2024</h1>
      <p>Grow smarter, one step at a time.</p>
    </div>

    <div class="form-box">
      Time Remaining: <span id="countdown">--:--</span>
    </div>

    <div class="question-container" role="main" aria-live="polite" id="q-container">
      <p>Loading questions...</p>
    </div>

    <div class="navigation" id="nav-buttons">
      <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
      <button class="nav-btn" id="btn-next" disabled>Next →</button>
    </div>

    <div class="question-nav" id="q-nav"></div>
  </div>

<script>
  let current = <?php echo $currentQuestion; ?>;
  let duration = <?php echo $duration; ?>;
  let questions = [];
  let answers = {};

  function loadQuestions() {
    const formData = new FormData();
    formData.append('action', 'loadQuestions');

    fetch(window.location.pathname, {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.questions) {
        questions = data.questions;
        renderQuestion();
        renderNav();
        startTimer();
      } else {
        document.getElementById('q-container').innerHTML = `<p>Error: ${data.error || 'Could not load questions'}</p>`;
      }
    })
    .catch(err => {
      document.getElementById('q-container').innerHTML = '<p>Error loading questions. Make sure "government.json" exists.</p>';
      console.error(err);
    });
  }

  function renderQuestion() {
    const idx = current - 1;
    const qObj = questions[idx];
    if (!qObj) {
      document.getElementById('q-container').innerHTML = '<p>No question.</p>';
      return;
    }

    const qc = document.getElementById('q-container');
    let html = `<div class="question-text">Q${qObj.id}. ${qObj.question}</div>`;
    html += '<form id="qForm">';
    for (let opt in qObj.options) {
      html += `
        <label class="option-label">
          <input type="radio" name="answer" value="${opt}" 
            ${answers[current] === opt ? 'checked' : ''} />
          ${opt}. ${qObj.options[opt]}
        </label>`;
    }
    html += `</form>`;
    qc.innerHTML = html;

    document.getElementById('btn-prev').disabled = (current <= 1);
    
    const btnNext = document.getElementById('btn-next');
    btnNext.disabled = (current >= questions.length);

    const nav = document.getElementById('nav-buttons');
    const existingSubmit = document.getElementById('btn-submit');
    
    if (current === questions.length) {
      if (!existingSubmit) {
        const submitBtn = document.createElement('button');
        submitBtn.textContent = "Submit";
        submitBtn.id = "btn-submit";
        submitBtn.className = "nav-btn";
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

  function renderNav() {
    const navDiv = document.getElementById('q-nav');
    let html = '';
    for (let i = 1; i <= questions.length; i++) {
      html += `<a href="#" onclick="navigate(${i}); return false;" class="${i === current ? 'active' : ''}">${i}</a>`;
    }
    navDiv.innerHTML = html;
  }
  
  function navigate(questionNumber) {
    saveAnswer();
    current = questionNumber;
    renderQuestion();
    renderNav();
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
    const sel = form.answer ? (form.answer.value || (form.answer.length > 0 ? Array.from(form.answer).find(r => r.checked)?.value : null)) : null;

    if (sel) {
      answers[current] = sel;
      
      const formData = new FormData();
      formData.append('action', 'saveAnswer');
      formData.append('questionId', current);
      formData.append('answer', sel);
      
      fetch(window.location.pathname, {
        method: 'POST',
        body: formData
      }).catch(err => console.error('Error saving answer:', err));
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
    if (questions.length > 0) {
      updateTimer();
      timerInterval = setInterval(updateTimer, 1000);
    }
  }

  function submitAnswers() {
    saveAnswer();
    clearInterval(timerInterval);

    const formData = new FormData();
    formData.append('action', 'submitAnswers');

    console.log('Submitting answers...');

    fetch(window.location.pathname, {
      method: 'POST',
      body: formData
    })
    .then(res => {
      console.log('Response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Data received:', data);
      if (data.success) {
        document.querySelector('.question-container').style.display = 'none';
        document.getElementById('nav-buttons').style.display = 'none';
        document.getElementById('q-nav').style.display = 'none';
        document.querySelector('.form-box').innerHTML = "Test Completed!";

        const container = document.querySelector('.container');
        const resultBox = document.createElement('div');
        resultBox.className = 'question-container result-box';
        resultBox.innerHTML = `
          <div style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">
            Test Submitted!
          </div>
          <div class="result-score">${data.score} / ${data.total}</div>
          <div class="result-details">Percentage: ${data.percentage}%</div>
          <button class="nav-btn" onclick="location.reload()">Retake Quiz</button>
        `;
        container.appendChild(resultBox);
      } else {
        alert('Error: ' + data.error);
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      alert('Error submitting answers: ' + err.message);
    });
}

  loadQuestions();
</script>



<?php include 'footer.php'; ?></body>
</html>