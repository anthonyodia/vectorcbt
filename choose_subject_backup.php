<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vector Learn - Step 2</title>
<style>
  /* Reset */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  

  .container {
    max-width: 1000px;
    width: 95%;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    padding-bottom: 30px;
  }

  .steps {
    display: flex;
    justify-content: space-between;
    padding: 15px 25px;
    background: #f7f7f7;
    font-size: 15px;
    border-bottom: 1px solid #e0e0e0;
    border-radius: 40px;
    margin: 20px auto 0 auto;
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
    margin-top: 15px;
  }

  .title h1 {
    font-size: 32px;
    color: #1e2a3a;
  }

  .title p {
    margin: 8px 0 25px 0;
    font-size: 16px;
    color: #555;
  }

  .form-box {
    background: linear-gradient(90deg, #4facfe, #43e97b);
    color: white;
    text-align: center;
    padding: 20px 15px;
    font-size: 20px;
    font-weight: 600;
    margin: 0 25px 25px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }

  /* Subject cards grid */
  .subjects {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 20px;
    padding: 0 25px;
  }

  .subject-card {
    background: linear-gradient(145deg, #ffffff, #f7f7f7);
    border-radius: 16px;
    padding: 25px 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s ease;
    border: 2px solid transparent;
    text-decoration: none;
    color: #333;
    font-weight: 600;
    font-size: 17px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  }

  .subject-card:hover {
    background: linear-gradient(135deg, #43e97b, #4facfe);
    color: white;
    border: 2px solid #43e97b;
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }

  .btn {
    display: block;
    width: 90%;
    margin: 30px auto 10px auto;
    padding: 16px;
    background: #43e97b;
    border: none;
    color: white;
    font-size: 18px;
    font-weight: bold;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
    text-align: center;
  }

  .btn:hover {
    background: #38c172;
    transform: translateY(-3px);
  }

  @media (max-width: 600px) {
    .steps {
      flex-direction: column;
      gap: 10px;
    }
    .form-box {
      font-size: 18px;
      padding: 18px 10px;
    }
  }
</style>
</head>
<body>

<div class="container">
  <div class="steps">
    <span>Step 1: Your Details</span>
    <span class="active">Step 2: Pick Subject</span>
    <span>Step 3: Begin!</span>
  </div>

  <div class="title">
    <h1>Vector Learn</h1>
    <p>Select the subject you want to practice today.</p>
  </div>

  <div class="form-box">
    Choose Your Subject 🎯
  </div>

  <div class="subjects">
    <a href="choose_time.php?subject=Mathematics" class="subject-card">Mathematics</a>
    <a href="choose_time.php?subject=Physics" class="subject-card">Physics</a>
    <a href="choose_time.php?subject=Chemistry" class="subject-card">Chemistry</a>
    <a href="choose_time.php?subject=Biology" class="subject-card">Biology</a>
    <a href="choose_time.php?subject=agric" class="subject-card">Agricultural Science</a>
    <a href="choose_time.php?subject=Computer" class="subject-card">Computer Studies</a>
    <a href="choose_time.php?subject=economics" class="subject-card">Economics</a>
    <a href="choose_time.php?subject=Commerce" class="subject-card">Commerce</a>
    <a href="choose_time.php?subject=Accounting" class="subject-card">Accounting</a>
  
    <a href="choose_time.php?subject=Marketing" class="subject-card">Marketing</a>
    <a href="choose_time.php?subject=English" class="subject-card">English Language</a>
    <a href="choose_time.php?subject=literature" class="subject-card">Literature in English</a>
    <a href="choose_time.php?subject=Goverment" class="subject-card">Government</a>
    <a href="choose_time.php?subject=Civic" class="subject-card">Civic Education</a>
    <a href="choose_time.php?subject=crk" class="subject-card">CRS / IRS</a>
    <a href="choose_time.php?subject=Geography" class="subject-card">Geography</a>
    <a href="choose_time.php?subject=Yoruba" class="subject-card">Yoruba</a>
    
  </div>

  <button class="btn">Proceed ➡️</button>
</div>

<?php include 'footer2.php'; ?>
</body>
</html>
