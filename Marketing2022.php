<?php
$jsonFile = __DIR__ . '/marketing2022.json';
$duration = 60;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Vector Learn — Marketing 2022 CBT</title>

<style>
/* YOUR ORIGINAL UI/UX (unchanged) */

.container { max-width:1000px; width:100%; margin:40px auto; background:white; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.08); overflow:hidden; padding-bottom:20px; }
.steps { display:flex; justify-content:space-between; padding:12px 20px; background:#f7f7f7; font-size:15px; border-bottom:1px solid #eaeaea; border-radius:40px; margin:20px auto; width:90%; }
.steps span { flex:1; text-align:center; padding:6px; color:#aaa; }
.steps .active { color:#007aff; font-weight:600; }
.title { text-align:center; margin-top:10px; }
.title h1 { font-size:30px; margin:0; color:#1e2a3a; }
.title p { margin:8px 0 22px 0; font-size:15px; color:#555; }
.form-box { background:linear-gradient(90deg, #4facfe, #43e97b); color:white; text-align:center; padding:18px 15px; font-size:18px; font-weight:600; margin:20px 25px 30px 25px; border-radius:12px; }
.question-container { margin:0 25px 25px 25px; padding:20px; border:1px solid #e6eaf0; border-radius:12px; background:#fafafa; min-height:300px; }
.explanation-view .question-container { background:#fff; border:1px solid #cceeff; margin-bottom:20px; }
.question-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #e0e0e0; }
.question-id { font-size:14px; color:#007aff; font-weight:600; }
.question-text { font-size:17px; font-weight:600; color:#243246; margin-bottom:20px; line-height:1.5; }
label.option-label { display:block; font-size:15px; color:#1e2a3a; margin-bottom:12px; cursor:pointer; user-select:none; padding:12px; border-radius:8px; border:1px solid #e0e0e0; transition:all 0.3s ease; }
label.option-label:hover { background:#f0f7ff; border-color:#007aff; }
label.option-label input[type="radio"] { margin-right:10px; cursor:pointer; accent-color:#007aff; }
.navigation { display:flex; justify-content:space-between; margin:30px 25px; gap:10px; }
.nav-btn { background:#43e97b; border:none; color:white; font-weight:bold; font-size:16px; padding:12px 28px; border-radius:12px; cursor:pointer; transition:background 0.3s ease; display:inline-flex; align-items:center; flex:1; justify-content:center; }
.nav-btn:hover:not(.disabled) { background:#38c172; }
.question-nav { text-align:center; margin:30px 0 40px 0; padding:0 25px; overflow-x:auto; }
.question-nav a { display:inline-block; margin:0 4px; min-width:34px; height:34px; line-height:34px; border-radius:50%; background:#f7f7f7; color:#007aff; font-weight:600; font-size:14px; text-decoration:none; }
.question-nav a.active { background:#007aff; color:white; }
.question-nav a.answered { background:#4caf50; color:white; }
.loading { text-align:center; padding:40px; font-size:18px; color:#666; }
.error { background:#ffebee; color:#c62828; padding:20px; margin:20px 25px; border-radius:12px; border-left:4px solid #c62828; }

/* Added explanation styles */
.correct-answer-label { background:#e8f5e9 !important; border:2px solid #4CAF50 !important; }
.user-answer-label { background:#fff8e1 !important; border:2px solid #FFC107 !important; }
.explanation-box { margin-top:15px; padding:15px; background:#e3f2fd; border-left:4px solid #2196F3; border-radius:10px; }

.nav-button-group { display:flex; justify-content:center; gap:10px; margin-top:30px; margin-bottom:30px; }
.home-btn { background:#6c757d; }
.home-btn:hover { background:#5a6268; }

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
        <h1>Vector Learn — Marketing 2022</h1>
        <p>Test your knowledge in marketing concepts and industrial goods.</p>
    </div>

    <div class="form-box" id="info-box">Time Remaining: <span id="countdown">--:--</span></div>

    <div id="section-display"></div>

    <div class="question-container" id="q-container">
        <p class="loading">Loading questions...</p>
    </div>

    <div class="navigation" id="nav-buttons">
        <button class="nav-btn" id="btn-prev" disabled>← Previous</button>
        <button class="nav-btn" id="btn-next" disabled>Next →</button>
    </div>

    <div class="question-nav" id="q-nav"></div>

    <div id="explanation-container" style="display:none; margin:25px;"></div>

</div>

<script>
let current=1, duration=<?=intval($duration)?>, allQuestions=[], answers={};

async function loadQuestions(){
    const resp=await fetch('marketing2022.json');
    allQuestions=await resp.json();
    renderQuestion(); renderNav(); startTimer();
}

function htmlEscape(t){const d=document.createElement('div'); d.textContent=t; return d.innerHTML;}

function showError(m){document.getElementById('q-container').innerHTML='<div class="error">'+m+'</div>';}

function renderQuestion(){
    const q=allQuestions[current-1];
    let html='<div class="question-header"><span class="question-id">Q'+q.id+'</span></div>';
    html+='<div class="question-text">'+htmlEscape(q.question)+'</div><form id="qForm">';
    for(let k in q.options){
        html+=`
        <label class="option-label">
            <input type="radio" name="answer" value="${k}" ${answers[q.id]===k?'checked':''}>
            <span>${k}. ${htmlEscape(q.options[k])}</span>
        </label>`;
    }
    html+='</form>';
    document.getElementById('q-container').innerHTML=html;

    document.getElementById('btn-prev').disabled=(current<=1);
    document.getElementById('btn-next').disabled=(current>=allQuestions.length);

    if(current===allQuestions.length){
        if(!document.getElementById('btn-submit')){
            let s=document.createElement('button');
            s.id='btn-submit';
            s.className='nav-btn';
            s.style.background='#ff6b6b';
            s.textContent='Submit Exam';
            s.onclick=submitAnswers;
            document.getElementById('nav-buttons').appendChild(s);
        }
        document.getElementById('btn-next').style.display='none';
    } else {
        let sb=document.getElementById('btn-submit');
        if(sb) sb.remove();
        document.getElementById('btn-next').style.display='inline-flex';
    }
}

function renderNav(){
    let nav='';
    allQuestions.forEach((q,i)=>{
        nav+=`<a href="#" onclick="navigate(${i+1});return false;" 
        class="${i+1===current?'active':''} ${answers[q.id]?'answered':''}">${i+1}</a>`;
    });
    document.getElementById('q-nav').innerHTML=nav;
}

function navigate(n){saveAnswer(); current=n; renderQuestion(); renderNav(); window.scrollTo(0,0);}

document.getElementById('btn-prev').onclick=()=>{ if(current>1){saveAnswer(); current--; renderQuestion(); renderNav();}};
document.getElementById('btn-next').onclick=()=>{ if(current<allQuestions.length){saveAnswer(); current++; renderQuestion(); renderNav();}};

function saveAnswer(){
    const q=allQuestions[current-1];
    const sel=document.querySelector('input[name="answer"]:checked')?.value;
    if(sel) answers[q.id]=sel;
    renderNav();
}

let totalSeconds=duration*60;
function startTimer(){updateTimer(); setInterval(updateTimer,1000);}
function updateTimer(){
    const m=String(Math.floor(totalSeconds/60)).padStart(2,'0');
    const s=String(totalSeconds%60).padStart(2,'0');
    document.getElementById('countdown').textContent=m+':'+s;
    if(totalSeconds--<=0) submitAnswers();
}

function submitAnswers(){
    saveAnswer();
    let score=0;
    allQuestions.forEach(q=>{ if(answers[q.id]===q.answer) score++; });

    const total=allQuestions.length, perc=Math.round((score/total)*100);

    document.getElementById('q-container').style.display='none';
    document.getElementById('nav-buttons').style.display='none';
    document.getElementById('q-nav').style.display='none';
    document.getElementById('info-box').innerHTML='Exam Completed!';

    const box=document.createElement('div');
    box.className='question-container';
    box.style.textAlign='center';

    box.innerHTML=`
        <div style="font-size:32px;font-weight:bold;">Results</div>
        <div style="font-size:48px;color:#007aff;font-weight:bold;margin:20px 0;">${score}/${total}</div>
        <div style="font-size:24px;color:#43e97b;">${perc}%</div>
        <br>
        <div class="nav-button-group">
            <button class="nav-btn home-btn" onclick="location.href='index.php'">🏠 Home</button>
            <button class="nav-btn" onclick="window.location.reload()">🔄 Retake</button>
            <button class="nav-btn" onclick="viewExplanations()">📘 View Explanation</button>
        </div>
    `;
    document.querySelector('.container').appendChild(box);
}

function viewExplanations(){
    document.querySelector('.container').innerHTML='<div class="title"><h1>Explanations</h1></div>';

    let out='<div class="explanation-view">';
    allQuestions.forEach(q=>{
        const user=answers[q.id];
        out+=`<div class="question-container">
                <div class="question-header"><span class="question-id">Q${q.id}</span></div>
                <div class="question-text">${htmlEscape(q.question)}</div>`;

        for(let k in q.options){
            let cls='';
            if(k===q.answer) cls='correct-answer-label';
            else if(k===user) cls='user-answer-label';

            out+=`<label class="option-label ${cls}">${k}. ${htmlEscape(q.options[k])}</label>`;
        }

        if(q.explanation){
            out+=`<div class="explanation-box"><strong>Explanation:</strong><br>${htmlEscape(q.explanation)}</div>`;
        }

        out+='</div>';
    });

    out+=`
    <div class="nav-button-group">
        <button class="nav-btn home-btn" onclick="location.href='index.php'">🏠 Home</button>
        <button class="nav-btn" onclick="window.location.reload()">🔄 Retake Exam</button>
    </div>
    `;

    document.querySelector('.container').innerHTML+=out;
}

loadQuestions();
</script>



<?php include 'footer2.php'; ?>
</body>
</html>
