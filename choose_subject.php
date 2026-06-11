<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vector Learn - Step 2</title>
<style>
  /* Reset and Base Styles */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background-color: #fefdfc;
    background-image: radial-gradient(#d1d9e6 1px, transparent 1px);
    background-size: 20px 20px;
    margin: 0;
}

  .container {
    max-width: 1000px;
    width: 95%;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
    padding-bottom: 40px;
    animation: fadeIn 0.6s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Steps Bar matched to CBT style */
  .steps-container {
    padding: 25px 25px 10px 25px;
  }

  .steps {
    display: flex;
    justify-content: space-between;
    background: #f7f7f7;
    padding: 10px;
    border-radius: 50px;
    border: 1px solid #eaeaea;
  }

  .steps span {
    flex: 1;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    color: #aaa;
  }

  .steps .active {
    color: #007aff;
    font-weight: 600;
  }

  /* Title Section */
  .title {
    text-align: center;
    margin-top: 15px;
  }

  .title h1 {
    font-size: 32px;
    font-weight: 800;
    color: #1e2a3a;
  }

  .title p {
    margin: 8px 0 25px 0;
    font-size: 15px;
    color: #555;
  }

  /* Header Box with Chemistry Page Gradient */
  .form-box {
    background: linear-gradient(90deg, #4facfe, #43e97b);
    color: white;
    text-align: center;
    padding: 20px;
    font-size: 20px;
    font-weight: 600;
    margin: 0 30px 30px 30px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(67, 233, 123, 0.2);
  }

  /* Subject cards grid */
  .subjects {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 15px;
    padding: 0 30px;
  }

  .subject-card {
    background: #ffffff;
    border: 1px solid #e6eaf0;
    border-radius: 15px;
    padding: 25px 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #243246;
    font-weight: 600;
    font-size: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
  }

  .subject-card i {
    font-style: normal;
    font-size: 28px;
  }

  .subject-card:hover {
    border-color: #007aff;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    background: #f0f7ff;
  }

  /* Selection state matched to Correct Answer highlight */
  .subject-card.selected {
    border: 2px solid #43e97b;
    background-color: #e8f5e9;
    color: #166534;
  }

  /* Button matched to CBT Green */
  .btn {
    display: block;
    width: calc(100% - 60px);
    margin: 40px auto 10px auto;
    padding: 18px;
    background: #43e97b; 
    border: none;
    color: white;
    font-size: 18px;
    font-weight: bold;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    box-shadow: 0 6px 15px rgba(67, 233, 123, 0.2);
  }

  .btn:hover {
    background: #38c172;
    transform: translateY(-2px);
  }

  @media (max-width: 600px) {
    .subjects { grid-template-columns: repeat(2, 1fr); padding: 0 15px; }
    .title h1 { font-size: 26px; }
    .steps { font-size: 12px; }
  }
  .content-wrapper {
    display: flex;
    justify-content: center;
    padding: 40px 0;
}
</style>
</head>
<body>
<div class="content-wrapper">
<div class="container">

  <div class="steps-container">
    <div class="steps">
      <span>Step 1: Details</span>
      <span class="active">Step 2: Subject</span>
      <span>Step 3: Begin</span>
    </div>
  </div>

  <div class="title">
    <h1>Vector Learn</h1>
    <p>Select your preferred subject to start practicing.</p>
  </div>

  <div class="form-box">
    Choose Your Subject 🎯
  </div>

  <div class="subjects">
    <a href="choose_time.php?subject=Mathematics" class="subject-card"><i>🔢</i>Mathematics</a>
    <a href="choose_time.php?subject=Physics" class="subject-card"><i>⚛️</i>Physics</a>
    <a href="choose_time.php?subject=Chemistry" class="subject-card"><i>🧪</i>Chemistry</a>
    <a href="choose_time.php?subject=Biology" class="subject-card"><i>🧬</i>Biology</a>
    <a href="choose_time.php?subject=agric" class="subject-card"><i>🌱</i>Agricultural Science</a>
    <a href="choose_time.php?subject=Computer" class="subject-card"><i>💻</i>Computer Studies</a>
    <a href="choose_time.php?subject=economics" class="subject-card"><i>📈</i>Economics</a>
    <a href="choose_time.php?subject=Commerce" class="subject-card"><i>🛍️</i>Commerce</a>
    <a href="choose_time.php?subject=Accounting" class="subject-card"><i>📊</i>Accounting</a>
    <a href="choose_time.php?subject=Marketing" class="subject-card"><i>📢</i>Marketing</a>
    <a href="choose_time.php?subject=English" class="subject-card"><i>📚</i>English Language</a>
    <a href="choose_time.php?subject=literature" class="subject-card"><i>🎭</i>Literature</a>
    <a href="choose_time.php?subject=Goverment" class="subject-card"><i>🏛️</i>Government</a>
    <a href="choose_time.php?subject=Civic" class="subject-card"><i>⚖️</i>Civic Education</a>
    <a href="choose_time.php?subject=crk" class="subject-card"><i>📖</i>CRS / IRS</a>
    <a href="choose_time.php?subject=Geography" class="subject-card"><i>🌍</i>Geography</a>
    <a href="choose_time.php?subject=Yoruba" class="subject-card"><i>🗣️</i>Yoruba</a>
  </div>

  <button class="btn" onclick="alert('Please select a subject card above!')">Proceed ➡️</button>
</div>

<script>
  const cards = document.querySelectorAll('.subject-card');
  cards.forEach(card => {
    card.addEventListener('click', function() {
      cards.forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
    });
  });
</script>

</body>
</html>
