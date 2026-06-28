<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector Learn — Step 1</title>
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

    /* Title */
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

    /* Info band */
    .form-box {
      background: linear-gradient(90deg, #4facfe, #43e97b);
      color: white;
      text-align: center;
      padding: 18px 15px;
      font-size: 18px;
      font-weight: 600;
    }

    /* Form */
    form {
      padding: 25px;
    }

    label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 6px;
      color: #243246;
    }

    .field {
      margin-bottom: 18px;
    }

    input,
    select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #e6eaf0;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    input:focus,
    select:focus {
      border-color: #007aff;
      box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
    }

    .btn {
      display: block;
      width: 100%;
      padding: 14px;
      background: #43e97b;
      border: none;
      color: white;
      font-size: 17px;
      font-weight: bold;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.3s ease;
      text-align: center;
    }

    .btn:hover {
      background: #38c172;
    }

    .hint {
      margin-top: 14px;
      font-size: 13px;
      color: #98a3b3;
      text-align: center;
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>
  <div class="container">
    <!-- Step indicator -->
    <div class="steps">
      <span class="active">Step 1: Your Details</span>
      <span>Step 2: Pick Subject</span>
      <span>Step 3: Begin!</span>
    </div>

    <!-- Title -->
    <div class="title">
      <h1>Vector Learn</h1>
      <p>Grow smarter, one step at a time.</p>
    </div>

    <!-- Info Band -->
    <div class="form-box">
      Welcome — Let’s unlock your learning journey today ??
    </div>

    <!-- Form -->
    <form action="choose_subject.php">
      <div class="field">
        <label for="name">Your full name</label>
        <input id="name" name="name" type="text" placeholder="e.g. Temidayo Farinade" required />
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com" required />
      </div>

      <div class="field">
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" placeholder="+234 801 000 0000" required />
      </div>

      <div class="field">
        <label for="class">Exam Type</label>
        <select id="class" name="class" required>
          <option>WAEC</option>
          
        </select>
      </div>

      <button type="submit" class="btn">Next — Pick subject</button>
    </form>

    <div class="hint">Your details are only used to personalize your dashboard.</div>
  </div>


<?php include 'footer2.php'; ?></body>
</html>
