<?php
$jsonFile = __DIR__ . '/english2025.json';
$action = $_GET['action'] ?? null;

/* ================= API ROUTES ================= */
if ($action) {

    header('Content-Type: application/json');

    $data = json_decode(file_get_contents($jsonFile), true);
    $fullData = $data['WAEC_English_Language_Objective_Questions']['questions'] ?? [];

    /* ---------- QUESTIONS ---------- */
    if ($action === 'get_questions') {

        $questions = [];

        foreach ($fullData as $q) {
            $questions[] = [
                'questionId' => $q['id'],
                'question' => $q['question'],
                'section' => $q['section'],
                'options' => array_map(
                    fn($id, $text) => ['optionId' => $id, 'text' => $text],
                    array_keys($q['options']),
                    $q['options']
                ),
                'image' => $q['image'] ?? null
            ];
        }

        echo json_encode(['success' => true, 'questions' => $questions]);
        exit();
    }

    /* ---------- SUBMIT ---------- */
    if ($action === 'submit') {

        $input = json_decode(file_get_contents('php://input'), true);
        $userAnswers = $input['answers'] ?? [];

        $score = 0;

        foreach ($fullData as $q) {
            if (($userAnswers[$q['id']] ?? null) === $q['answer']) {
                $score++;
            }
        }

        echo json_encode([
            'score' => $score,
            'total' => count($fullData),
            'percentage' => round(($score / count($fullData)) * 100, 2)
        ]);

        exit();
    }

    /* ---------- EXPLANATIONS ---------- */
    if ($action === 'get_explanations') {

        $exps = [];

        foreach ($fullData as $q) {
            $exps[] = [
                'questionId' => $q['id'],
                'question' => $q['question'],
                'correctAnswer' => $q['answer'],
                'explanation' => $q['explanation'],
                'options' => array_map(
                    fn($id, $text) => ['optionId' => $id, 'text' => $text],
                    array_keys($q['options']),
                    $q['options']
                )
            ];
        }

        echo json_encode(['success' => true, 'questions' => $exps]);
        exit();
    }

    /* ---------- AI ROUTE ---------- */
    if ($action === 'get_ai_help') {
        include 'ai_logic.php';
        exit();
    }
}
?>

<!-- NORMAL HTML BELOW (UNCHANGED) -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Vector Learn — WAEC English CBT</title>
<script src="exam_engine.js"></script>


<style>
.container { max-width:1000px; margin:40px auto; background:white; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.08); padding-bottom:20px; }

.steps { display:flex; justify-content:space-between; padding:12px 20px; background:#f7f7f7; }
.steps span { flex:1; text-align:center; color:#aaa; }
.steps .active { color:#007aff; font-weight:600; }

.title { text-align:center; }
.form-box { background:linear-gradient(90deg,#4facfe,#43e97b); color:white; text-align:center; padding:18px; margin:20px; border-radius:12px; }

.question-container { margin:20px; padding:20px; border:1px solid #e6eaf0; border-radius:12px; background:#fafafa; }

.question-header { display:flex; justify-content:space-between; margin-bottom:10px; }

.question-text { font-size:17px; font-weight:600; }

label.option-label { display:block; padding:12px; margin-bottom:10px; border:1px solid #ddd; border-radius:8px; cursor:pointer; }

.navigation { display:flex; justify-content:space-between; margin:20px; }

.nav-btn { background:#43e97b; color:white; border:none; padding:12px; border-radius:10px; flex:1; margin:5px; cursor:pointer; }

.nav-btn.disabled { background:#ccc; }

.question-nav a { margin:3px; padding:6px 10px; border-radius:50%; display:inline-block; background:#eee; text-decoration:none; }

.question-nav a.active { background:#007aff; color:white; }

.question-nav a.answered { background:#4caf50; color:white; }

.explanation-box { background:#e3f2fd; padding:12px; border-left:4px solid #2196F3; margin-top:10px; }

.correct-answer-label { background:#e8f5e9; border:2px solid #4CAF50; }

.user-answer-label { background:#fff8e1; border:2px solid #FFC107; }

</style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="container">

<div class="steps">
    <span>Step 1</span>
    <span>Step 2</span>
    <span class="active">Step 3</span>
</div>

<div class="title">
    <h1>Vector Learn — English CBT</h1>
</div>

<div class="form-box">
    Time Remaining: <span id="countdown">--:--</span>
</div>

<div id="section-display"></div>

<div id="q-container" class="question-container">
    <p>Loading...</p>
</div>

<div class="navigation" id="nav-buttons">
    <button class="nav-btn" id="btn-prev">Prev</button>
    <button class="nav-btn" id="btn-next">Next</button>
</div>

<div class="question-nav" id="q-nav"></div>

<div id="explanation-container" style="display:none;"></div>

</div>

<?php include 'footer2.php'; ?>

</body>
</html>
