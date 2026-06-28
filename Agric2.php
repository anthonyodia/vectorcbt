<?php
session_start();

// 1. Configuration
$jsonFileName = 'agric2024.json'; 

// Load JSON file
// @ suppresses file read errors, which are caught below
$questionsJson = @file_get_contents($jsonFileName); 

// 2. Data Validation and Setup with Error Handling
if ($questionsJson === false) {
    // File not found or unreadable
    die('<h1>Fatal Error 🚫</h1><p><strong>Error:</strong> Could not read file <code>' . $jsonFileName . '</code>. Please check that the file exists in the same directory and permissions are correct.</p>');
}

$questions = json_decode($questionsJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // JSON Syntax or Encoding Error (like BOM)
    die('<h1>Fatal Error ⚠️</h1><p><strong>Error:</strong> Failed to decode JSON from <code>' . $jsonFileName . '</code>. Error: <strong>' . json_last_error_msg() . '</strong>. **Action:** Please ensure the file is saved as **UTF-8 without BOM**.</p>');
}

if (!is_array($questions) || empty($questions)) {
    // Empty JSON array
    die('<h1>Fatal Error 📋</h1><p><strong>Error:</strong> The JSON file is empty or does not contain a valid array of questions.</p>');
}

// Get current question number and duration
$current = isset($_GET['q']) ? (int)$_GET['q'] : 1;
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 30; // Default 30 minutes

$total = count($questions);
// Ensure current question is within valid bounds
if ($current < 1) $current = 1;
if ($current > $total) $current = $total;

$question = $questions[$current - 1] ?? null;

// Initialize answers session array
if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}

// 3. Handle Restart Logic
if (isset($_GET['restart']) && $_GET['restart'] == '1') {
    unset($_SESSION['answers']);
    unset($_SESSION['last_score']);
    // Redirect to Q1 and reset timer
    header("Location: " . basename($_SERVER['PHP_SELF']) . "?q=1&duration=$duration&resetTimer=1");
    exit();
}

// 4. Handle Form Submission (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedAnswer = $_POST['answer'] ?? null;
    $submitType = $_POST['submit_type'] ?? 'next';

    // Save answer using current question number (1-based index)
    if ($selectedAnswer !== null) {
        $_SESSION['answers'][$current] = $selectedAnswer;
    }

    if ($current === $total || $submitType === 'finish') {
        // Final question submitted or user pressed finish button — calculate score
        $score = 0;
        foreach ($_SESSION['answers'] as $qNum => $selectedKey) {
            // Check if the original question exists and the answer matches
            if (isset($questions[$qNum - 1]['answer']) && $questions[$qNum - 1]['answer'] === $selectedKey) {
                $score++;
            }
        }

        $_SESSION['last_score'] = $score;
        // Redirect to the same page with 'finish' flag to display result
        header("Location: " . basename($_SERVER['PHP_SELF']) . "?finish=1&duration=$duration");
        exit();
    } else {
        // Go to next question
        header("Location: " . basename($_SERVER['PHP_SELF']) . "?q=" . ($current + 1) . "&duration=$duration");
        exit();
    }
} else {
    // Check if we just finished (after POST redirect)
    $showResult = isset($_GET['finish']) && isset($_SESSION['last_score']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Agricultural Science CBT</title>
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
        
        /* Image styling for diagrams */
        .question-container img {
            max-width: 100%; 
            height: auto;
            border: 1px solid #ccc; 
            border-radius: 8px;
            display: block;
            margin: 15px auto;
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

        .question-nav a.active {
            background-color: #007aff;
            color: white;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #007aff;
        }
        
        .question-nav a.answered {
            background-color: #4facfe; /* Different color for answered questions */
            color: white;
        }
        
        .question-nav a.active.answered {
            background-color: #007aff; 
            box-shadow: 0 0 0 2px #007aff;
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
            <h1>Vector Learn — Agricultural Science 2024</h1>
            <p>Grow smarter, one step at a time.</p>
        </div>

        <div class="form-box">
            Time Remaining: <span id="countdown">--:--</span>
        </div>

<?php if ($showResult): ?>
        <div class="question-container" role="main" aria-live="polite" style="text-align:center; font-size:24px; font-weight:bold;">
            You scored <?= $_SESSION['last_score'] ?? 0 ?> / <?= $total ?>! 🎉
            <br /><br />
            <form method="get" action="<?= basename($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="restart" value="1" />
                <button type="submit" style="padding:12px 28px; font-size:16px; border-radius:12px; cursor:pointer; background: #ff5e3a;" class="nav-btn">Restart Quiz</button>
            </form>
        </div>
<?php else: ?>
        <div class="question-container" role="main" aria-live="polite">
            <div class="question-text">
                Q<?= htmlspecialchars($question['id']) ?>. <?= htmlspecialchars($question['question']) ?>
            </div>
            
            <?php 
            // Correct pathing for the image from your JSON structure
            $imagePath = !empty($question['image']) ? str_replace('agriculturalscience/', '', $question['image']) : ''; 
            
            // Check if the image file exists in the current directory (assuming images are uploaded there)
            if (!empty($imagePath) && file_exists($imagePath)): 
            ?>
                <div style="text-align: center;">
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Question image" />
                </div>
            <?php elseif (!empty($question['image'])): ?>
                 <div style="text-align: center;">
                    <p style="color: #ff5e3a; font-weight: bold;">[Image required for this question: <?= htmlspecialchars($question['image']) ?>]</p>
                 </div>
            <?php endif; ?>

            <form method="post" id="quizForm" action="<?= basename($_SERVER['PHP_SELF']) . "?q=" . $current . "&duration=" . $duration ?>">
                <?php foreach ($question['options'] as $key => $value): ?>
                    <label class="option-label" for="opt<?= $key ?>">
                        <input 
                            type="radio" 
                            id="opt<?= $key ?>" 
                            name="answer" 
                            value="<?= htmlspecialchars($key) ?>" 
                            <?= (isset($_SESSION['answers'][$current]) && $_SESSION['answers'][$current] == $key) ? 'checked' : '' ?>
                        />
                        <?= htmlspecialchars($key) ?>. <?= htmlspecialchars($value) ?>
                    </label>
                <?php endforeach; ?>
                
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button type="submit" name="submit_type" value="next" class="nav-btn" style="width: 48%; background: #007aff;">
                        <?= ($current === $total) ? 'Finish & Submit Score' : 'Save & Next Question' ?>
                    </button>
                    
                    <button type="submit" name="submit_type" value="finish" class="nav-btn" style="width: 48%; background: #ff5e3a;">
                        Review & Finish Early
                    </button>
                </div>
            </form>
        </div>

        <div class="navigation">
            <?php if ($current > 1): ?>
                <a href="?q=<?= $current - 1 ?>&duration=<?= $duration ?>" class="nav-btn">← Previous</a>
            <?php else: ?>
                <button class="nav-btn disabled">← Previous</button>
            <?php endif; ?>
             
            <?php if ($current < $total): ?>
                 <a href="?q=<?= $current + 1 ?>&duration=<?= $duration ?>" class="nav-btn" style="background: #ccc; color: #555;">Jump Next →</a>
            <?php else: ?>
                <button class="nav-btn disabled">End of Quiz</button>
            <?php endif; ?>
        </div>

        <div class="question-nav" aria-label="Jump to question">
            <?php for ($i = 1; $i <= $total; $i++): ?>
                <a 
                    href="?q=<?= $i ?>&duration=<?= $duration ?>" 
                    class="<?= $i === $current ? 'active' : '' ?> <?= isset($_SESSION['answers'][$i]) ? 'answered' : '' ?>" 
                    aria-current="<?= $i === $current ? 'page' : 'false' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
<?php endif; ?>
    </div>

<script>
// Timer countdown in JS with persistence in localStorage
const durationMinutes = <?= $duration ?>;
const durationSeconds = durationMinutes * 60;
const urlParams = new URLSearchParams(window.location.search);
const resetTimer = urlParams.get('resetTimer') === '1';

function getStoredTime() {
    const stored = localStorage.getItem('remainingTime');
    // Only use stored time if we are NOT resetting the timer
    if (stored !== null && !resetTimer) {
        return parseInt(stored, 10);
    }
    return durationSeconds;
}

let totalSeconds = getStoredTime();

function updateTimer() {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const timerEl = document.getElementById("countdown");
    
    // Safety check to ensure element exists before trying to update it
    if (timerEl) {
        timerEl.textContent = `${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
    }

    if (totalSeconds <= 0) {
        clearInterval(timerInterval);
        alert("Time's up! The test will automatically finish now.");
        localStorage.removeItem('remainingTime');
        
        // Auto-submit the form as if the user clicked finish
        const form = document.getElementById("quizForm");
        if (form) {
             const hiddenInput = document.createElement('input');
             hiddenInput.type = 'hidden';
             hiddenInput.name = 'submit_type';
             hiddenInput.value = 'finish';
             form.appendChild(hiddenInput);
             form.submit();
        } else {
             // Redirect to the finish state if form isn't present (e.g., on results page)
             window.location.href = "<?= basename($_SERVER['PHP_SELF']) ?>?finish=1";
        }
    } else {
        localStorage.setItem('remainingTime', totalSeconds);
    }
    totalSeconds--;
}

const timerInterval = setInterval(updateTimer, 1000);
updateTimer();

<?php if ($showResult): ?>
    // Clear timer when result is shown
    clearInterval(timerInterval);
    localStorage.removeItem('remainingTime');
<?php endif; ?>
</script>


<?php include 'footer2.php'; ?></body>
</html>
