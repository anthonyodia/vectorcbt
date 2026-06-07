<?php
// civiceducation.php - Vector Learn CBT Page (Server-Side Logic)

// ❗ REVISED STEP 1: Using the exact JSON file name requested ❗
$jsonFile = __DIR__ . '/Civic2024.json';

// Check if action is requested via AJAX
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// --- Helper Functions to Process JSON (No change here) ---
function flattenQuestions($data, $includeAnswers = false) {
    $questions = [];
    
    // Check for the 'question_set' key (used in your Accounting JSON)
    $source_questions = $data['question_set'] ?? [];
    
    // Fallback/Legacy check for the 'questions' key 
    if (empty($source_questions)) {
        $source_questions = $data['questions'] ?? [];
    }
    
    foreach ($source_questions as $question) {
        
        // Use 'id' (from your JSON) or 'number' or 'questionId'
        $qId = $question['id'] ?? $question['number'] ?? $question['questionId'];
        
        // *** CRITICAL FIX: Handle options as an object (Your structure) or an array (JS expected structure) ***
        $options_raw = $question['options'] ?? $question['choices'] ?? [];
        $options = [];

        if (is_array($options_raw) && !empty($options_raw) && isset($options_raw[0]) && is_array($options_raw[0])) {
            // Case 1: Already an array of objects (JS expected structure)
            $options = $options_raw;
        } elseif (is_array($options_raw) && !empty($options_raw)) {
             // Case 2: Object format (Your JSON structure: {"A": "text", "B": "text"})
             // Convert to: [{"optionId": "A", "text": "text"}, ...]
             foreach ($options_raw as $optionId => $text) {
                 $options[] = [
                     'optionId' => $optionId,
                     'text' => $text
                 ];
             }
        }
        // ************************************************************

        $q = [
            // Ensure questionId is the consistent key used for indexing
            'questionId' => $qId, 
            'sectionName' => $data['subject'] ?? ($question['sectionName'] ?? 'Civic Education'),
            'sectionId' => $question['sectionId'] ?? 1,
            'question' => $question['question'] ?? null,
            'instruction' => $question['instruction'] ?? null,
            'related_info' => $question['related_info'] ?? $question['data_table'] ?? null,
            'options' => $options, // Now guaranteed to be an array
        ];

        if ($includeAnswers) {
            // Check 'answer' key (used in Accounting/Civic JSON) then 'correctAnswer'
            $correctAnswerKey = isset($question['answer']) ? 'answer' : 'correctAnswer';
            
            // Explicitly cast answer to a string when loading from JSON
            $q['correctAnswer'] = isset($question[$correctAnswerKey]) ? (string)$question[$correctAnswerKey] : null;
            $q['explanation'] = $question['explanation'] ?? 'No explanation provided.';
        }
        $questions[] = $q;
    }
    return $questions;
}

// --- ACTION 1: Get Questions for the Exam View (No Answers) ---
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    // ❗ USING Civic2024.json ❗
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Question file not found: ' . basename($jsonFile)]);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON format or empty file']);
        exit();
    }
    
    $questions = flattenQuestions($data, false);
    
    echo json_encode([
        'success' => true,
        'examName' => $data['exam'] ?? 'Civic Education CBT',
        'subjectName' => $data['subject'] ?? 'Civic Education',
        'totalQuestions' => count($questions),
        'questions' => $questions
    ]);
    exit();
}

// --- ACTION 2: Get All Question Details for Explanation View (Includes Answers) ---
if ($action === 'get_explanations') {
    header('Content-Type: application/json');
    // ❗ USING Civic2024.json ❗
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Question file not found']);
        exit();
    }
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON format']);
        exit();
    }

    $questionsWithDetails = flattenQuestions($data, true);
    
    echo json_encode(['success' => true, 'questions' => $questionsWithDetails]);
    exit();
}

// --- ACTION 3: Submit Answers and Calculate Score ---
if ($action === 'submit') {
    header('Content-Type: application/json');
    
    $input = file_get_contents('php://input');
    $data_in = json_decode($input, true);

    $answers = $data_in['answers'] ?? [];
    
    // ❗ USING Civic2024.json ❗
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Question file not found']);
        exit();
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    $questionMap = [];
    foreach (flattenQuestions($data, true) as $question) {
        // Use questionId for map key, ensuring it's cast to integer for consistency
        $questionMap[(int)$question['questionId']] = $question;
    }
    
    $score = 0;
    $total = count($questionMap);
    
    // --- SCORING LOOP ---
    foreach ($answers as $qId => $answer) {
        $qId = (int)$qId; // Cast QID to integer for lookup
        $answer = (string)$answer; // Ensure user answer is a string ('A', 'B', etc.)
        
        if (isset($questionMap[$qId]) && $answer == $questionMap[$qId]['correctAnswer']) {
            $score++;
        }
    }
    
    $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => $percentage
    ]);
    exit();
}

// If no specific action is requested, fall through to the HTML client view.

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector Learn — Civic Education CBT</title>
  <style>
    /* --- Start of your original CSS (omitted for brevity) --- */
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
      max-width: 950px;
      width: 100%;
      margin: 40px auto;
      background: white;
      border-radius: 14px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      overflow: hidden;
      padding-bottom: 30px;
    }

    /* Step tracker */
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

    /* Header */
    .header {
      text-align: center;
      margin: 30px 0 15px 0;
    }

    .header h2 {
      font-size: 28px;
      margin: 0;
      color: #1e2a3a;
    }

    /* Info band */
    .form-box {
      background: linear-gradient(90deg, #4facfe, #43e97b);
      color: white;
      text-align: center;
      padding: 20px 15px;
      font-size: 18px;
      font-weight: 600;
      margin: 20px auto;
      border-radius: 10px;
      width: 90%;
    }

    .timer {
      font-size: 18px;
      font-weight: bold;
      margin-top: 8px;
      color: #ffecec;
    }

    /* Question area */
    .question-box {
      padding: 25px;
      margin-top: 10px;
    }

    .question {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #222;
    }
    
    .loading-text {
        text-align: center;
        padding: 40px;
        font-size: 18px;
        color: #666;
    }
    
    .question-instruction {
        font-size: 15px;
        font-style: italic;
        color: #777;
        margin-bottom: 15px;
    }
    
    .question-section {
        font-size: 14px;
        color: #007aff;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .error {
        background: #ffebee;
        color: #c62828;
        padding: 20px;
        margin: 20px 25px;
        border-radius: 12px;
        border-left: 4px solid #c62828;
    }
    
    .options label {
      display: block;
      background: #f8f9fa;
      padding: 14px;
      margin: 12px 0;
      border-radius: 10px;
      border: 1px solid #ddd;
      cursor: pointer;
      transition: 0.3s;
      font-size: 15px;
      color: #333;
    }

    .options input {
      margin-right: 10px;
      accent-color: #007bff;
    }

    .options label:hover {
      background: #e6f0ff;
      border-color: #007bff;
    }

    /* Navigation buttons */
    .nav-buttons {
      display: flex;
      justify-content: space-between;
      margin: 30px 25px 10px 25px;
      gap: 12px;
    }

    .btn {
      flex: 1;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }

    .prev-btn {
      background: #6c757d;
      color: #fff;
    }

    .next-btn {
      background: #007bff;
      color: #fff;
    }

    .submit-btn {
      background: #43e97b;
      color: #fff;
    }

    .btn:hover:not(:disabled) {
      opacity: 0.9;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: default;
    }
    
    /* Styles for Results/Explanation Views */
    .results-box, .explanation-container {
        padding: 25px;
        margin-top: 10px;
    }
    .result-score {
        font-size: 48px;
        font-weight: bold;
        color: #007bff;
    }
    .result-text {
        font-size: 20px;
        margin-bottom: 30px;
    }
    .nav-button-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }
    .explanation-view .options label {
        cursor: default;
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
        font-size: 14px;
    }
    /* --- End of your original CSS + additions for new features --- */
  </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>
  <div class="container">
        <div class="steps">
      <span>Step 1: Your Details</span>
      <span>Step 2: Pick Subject</span>
      <span class="active">Step 3: Exam</span>
    </div>

        <div class="header" id="header">
      <h2>Subject: <span id="subject-title">Civic Education</span></h2>
    </div>

        <div class="form-box" id="info-box">
      Good luck! Stay focused & do your best 🎯
      <div class="timer" id="timer">Time Left: --:--</div>
    </div>

        <div class="question-box" id="question-box">
      <p class="loading-text" id="loading-text">Loading questions...</p>
          </div>

        <div class="nav-buttons" id="nav-buttons">
      <button class="btn prev-btn" id="prev-btn" disabled>← Previous</button>
      <button class="btn next-btn" id="next-btn" style="display:none;">Next →</button>
      <button class="btn submit-btn" id="submit-btn" style="display:none;">Submit</button>
    </div>
    
    <div id="results-view" class="results-box" style="display:none; text-align:center;"></div>
    <div id="explanation-container" class="explanation-container" style="display:none;"></div>
    
  </div>

  <script>
    // --- START: Absolute URL Handling ---
    let scriptBaseUrl = window.location.href;
    scriptBaseUrl = scriptBaseUrl.split('?')[0]; 
    const homeUrl = scriptBaseUrl.substring(0, scriptBaseUrl.lastIndexOf('/') + 1) + 'index.php'; 
    // --- END: Absolute URL Handling ---
    
    let allQuestions = [];
    let currentQuestionIndex = 0;
    let userAnswers = {}; // { questionId: 'A', questionId: 'B', ...}
    let explanationData = null; 
    let timerInterval;

    const questionBox = document.getElementById("question-box");
    const loadingText = document.getElementById("loading-text");
    const questionText = document.getElementById("question-text");
    const optionsContainer = document.getElementById("options-container");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const submitBtn = document.getElementById("submit-btn");
    const timerDisplay = document.getElementById("timer");
    const infoBox = document.getElementById("info-box");
    const navButtons = document.getElementById("nav-buttons");
    const header = document.getElementById("header");

    // Get duration from URL param, default to 30 mins
    const urlParams = new URLSearchParams(window.location.search);
    let duration = parseInt(urlParams.get("duration"));
    if (!duration || isNaN(duration)) duration = 30;
    let timeLeft = duration * 60;
    
    // --- UTILITY FUNCTIONS ---
    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(message) {
        loadingText.style.display = 'none';
        questionBox.innerHTML = '<div class="error">**Error:** ' + message + '</div>';
        prevBtn.disabled = true;
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'none';
    }


    // Timer function
    function updateTimer() {
      let minutes = Math.floor(timeLeft / 60);
      let seconds = timeLeft % 60;
      seconds = seconds < 10 ? "0" + seconds : seconds;
      timerDisplay.innerHTML = `Time Left: ${minutes}:${seconds}`;
      timeLeft--;
      if (timeLeft < 0) {
        clearInterval(timerInterval);
        alert("Time is up! Submitting your test...");
        submitAnswers();
      }
    }

    // --- MAIN RENDER FUNCTION ---
    function loadQuestion() {
      if (allQuestions.length === 0) {
        questionBox.innerHTML = '<p class="loading-text">No questions loaded.</p>';
        return;
      }
      
      loadingText.style.display = 'none';
      questionBox.style.display = 'block';

      const q = allQuestions[currentQuestionIndex];
      const qId = q.questionId;
      
      let html = '';
      
      html += `<div class="question-section">Question ${currentQuestionIndex + 1} of ${allQuestions.length} | Subject: ${htmlEscape(q.sectionName)}</div>`;
      
      if (q.instruction) {
          html += `<div class="question-instruction">${htmlEscape(q.instruction)}</div>`;
      }
      
      html += `<div class="question" id="question-text">${q.question}</div>`;

      html += `<div class="options" id="options-container">`;
      
      q.options.forEach(opt => { 
        const id = `q${qId}_option_${opt.optionId}`;
        const isChecked = userAnswers[qId] === opt.optionId;

        html += `<label for="${id}">`;
        html += `<input type="radio" name="question-${qId}" id="${id}" value="${opt.optionId}" ${isChecked ? 'checked' : ''}> `;
        html += `<span>${htmlEscape(opt.optionId)}. ${htmlEscape(opt.text)}</span>`;
        html += `</label>`;
      });
      
      html += '</div>';

      questionBox.innerHTML = html;
      
      // Add event listeners to newly rendered options
      const options = questionBox.querySelectorAll('input[type="radio"]');
      options.forEach(radio => {
          radio.addEventListener('change', saveAnswer);
      });

      prevBtn.disabled = currentQuestionIndex === 0;
      
      if (currentQuestionIndex === allQuestions.length - 1) {
          nextBtn.style.display = "none";
          submitBtn.style.display = "inline-block";
      } else {
          nextBtn.style.display = "inline-block";
          submitBtn.style.display = "none";
      }
    }

    // Save answer
    function saveAnswer() {
      const q = allQuestions[currentQuestionIndex];
      const qId = q.questionId;
      const selectedOption = document.querySelector(`input[name="question-${qId}"]:checked`);
      if (selectedOption) userAnswers[qId] = selectedOption.value;
    }

    // --- NAVIGATION EVENT LISTENERS ---
    prevBtn.addEventListener("click", () => {
      saveAnswer();
      if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        loadQuestion();
      }
    });

    nextBtn.addEventListener("click", () => {
      saveAnswer();
      if (currentQuestionIndex < allQuestions.length - 1) {
        currentQuestionIndex++;
        loadQuestion();
      }
    });

    submitBtn.addEventListener("click", () => {
      saveAnswer();
      if (Object.keys(userAnswers).length < allQuestions.length) {
        if (!confirm(`You have only answered ${Object.keys(userAnswers).length} out of ${allQuestions.length} questions. Submit anyway?`)) return;
      }
      submitAnswers();
    });

    // --- AJAX FUNCTIONS ---
    
    async function fetchQuestions() {
        try {
            const url = scriptBaseUrl + '?action=get_questions';
            const response = await fetch(url); 
            const data = await response.json();
            
            if (data.success && data.questions.length > 0) {
                allQuestions = data.questions;
                document.getElementById('subject-title').textContent = data.subjectName; 
                loadQuestion();
                timerInterval = setInterval(updateTimer, 1000);
            } else {
                // ❗ REVISED ERROR MESSAGE ❗
                showError('Failed to load questions. Check that **Civic2024.json** exists, is valid, and contains questions. (Server responded: ' + JSON.stringify(data) + ')');
            }
        } catch (error) {
            console.error('AJAX Load Error:', error);
            showError('CRITICAL Network Error: Failed to complete AJAX request. Check your browser console for security/network errors.');
        }
    }

    async function submitAnswers() {
        saveAnswer();
        if(timerInterval) clearInterval(timerInterval);
        
        // Hide info box and nav buttons immediately
        infoBox.style.display = 'none';
        navButtons.style.display = 'none';
        questionBox.style.display = 'none';

        try {
            const url = scriptBaseUrl + '?action=submit';
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers: userAnswers }) 
            });

            const result = await response.json();
            if (result.success) {
                displayResults(result);
            } else {
                document.getElementById('results-view').style.display = 'block';
                document.getElementById('results-view').innerHTML = `<div class="error">Submission failed: ${result.error || 'Server error.'}</div>`;
            }
        } catch (error) {
            document.getElementById('results-view').style.display = 'block';
            document.getElementById('results-view').innerHTML = `<div class="error">Error submitting answers: ${error.message}</div>`;
        }
    }
    
    function displayResults(result) {
        const resultsView = document.getElementById('results-view');
        resultsView.style.display = 'block';
        
        let html = '<div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">Exam Results</div>';
        html += `<div class="result-score">${result.score}/${result.total}</div>`;
        html += `<div class="result-text">${result.percentage}% Score</div>`;
        html += '<div style="font-size: 16px; color: #555; margin-bottom: 30px;">Great job! Review your performance below.</div>';
        
        // Navigation Group for Results View
        html += '<div class="nav-button-group">';
        html += '<button class="btn prev-btn" onclick="window.location.href = homeUrl;">🏠 Go to Home</button>';
        html += '<button class="btn next-btn" style="background:#007bff;" onclick="viewExplanations()">View Explanation</button>';
        html += `<button class="btn submit-btn" onclick="window.location.href = scriptBaseUrl;">🔄 Retake Exam</button>`;
        html += '</div>';

        resultsView.innerHTML = html;
        window.scrollTo(0, 0);
    }
    
    async function viewExplanations() {
        document.getElementById('results-view').style.display = 'none';
        header.innerHTML = '<h2>Subject: Civic Education — Explanations</h2>';
        
        const explanationContainer = document.getElementById('explanation-container');
        explanationContainer.style.display = 'block';
        explanationContainer.innerHTML = '<p class="loading-text">Loading explanations...</p>';
        
        if (!explanationData) {
              try {
                  const url = scriptBaseUrl + '?action=get_explanations';
                  const response = await fetch(url);
                  const data = await response.json();
                  
                  if (data.success) {
                      explanationData = data.questions;
                      renderExplanations();
                  } else {
                      explanationContainer.innerHTML = '<div class="error">Failed to load explanation data.</div>';
                  }
              } catch (error) {
                  explanationContainer.innerHTML = '<div class="error">Error loading explanations: ' + error.message + '</div>';
              }
        } else {
            renderExplanations();
        }
    }

    function renderExplanations() {
        const explanationContainer = document.getElementById('explanation-container');
        let html = '<div class="explanation-view">';

        explanationData.forEach((qObj, index) => {
            const qId = qObj.questionId;
            const userAnswer = userAnswers[qId]; 
            const isCorrect = userAnswer === qObj.correctAnswer;
            
            html += '<div class="question-box" style="margin-bottom: 20px;">';
            
            html += `<div class="question-section">Q${index + 1}. | Result: ${isCorrect ? 'Correct ✅' : 'Incorrect ❌'} | Your Answer: ${userAnswer || 'N/A'}</div>`;
            
            if (qObj.instruction) {
                 html += `<div class="question-instruction">${htmlEscape(qObj.instruction)}</div>`;
            }

            html += `<div class="question">${htmlEscape(qObj.question)}</div>`;
            
            html += '<div class="options">';
            qObj.options.forEach(opt => {
                let labelClass = '';
                if (opt.optionId === qObj.correctAnswer) {
                    labelClass = 'correct-answer-label';
                } else if (opt.optionId === userAnswer && opt.optionId !== qObj.correctAnswer) {
                    labelClass = 'user-answer-label';
                }

                html += `<label class="${labelClass}">`;
                html += `<span>${htmlEscape(opt.optionId)}. ${htmlEscape(opt.text)}</span>`;
                html += `</label>`;
            });
            html += '</div>';

            if (qObj.explanation) {
                html += `<div class="explanation-box"><strong>Explanation:</strong> ${htmlEscape(qObj.explanation)}</div>`;
            }

            html += '</div>'; // close question-box
        });

        html += '</div>';
        // Navigation Group for Explanation View
        html += '<div class="nav-button-group">';
        html += '<button class="btn prev-btn" onclick="window.location.href = homeUrl;">🏠 Go to Home</button>';
        html += `<button class="btn submit-btn" onclick="window.location.href = scriptBaseUrl;">🔄 Retake Exam</button>`;
        html += '</div>';
        
        explanationContainer.innerHTML = html;
        window.scrollTo(0, 0); 
    }
    
    // Start the process
    fetchQuestions();
  </script>


<?php include 'footer.php'; ?></body>
</html>