<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vector Learn — Step 3</title>
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
    }

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

    select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #e6eaf0;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

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

<?php include 'topnavbar.php'; ?>
  <div class="container">
    <!-- Step indicator -->
    <div class="steps">
      <span>Step 1: Your Details</span>
      <span>Step 2: Pick Subject</span>
      <span class="active">Step 3: Duration</span>
    </div>

    <!-- Title -->
    <div class="title">
      <h1>Vector Learn</h1>
      <p>Choose how long you want your CBT to last ?</p>
    </div>

    <!-- Info Band -->
    <div class="form-box">
      Final step — Set your exam duration and begin ??
    </div>

    <!-- Form -->
    <form onsubmit="startExam(event)">
      <div class="field">
        <label for="duration">Select Duration</label>
        <select id="duration" required>
          <option value="">— Select Duration —</option>
          <option value="30">30 Minutes</option>
          <option value="45">45 Minutes</option>
          <option value="60">1 Hour</option>
          <option value="90">1 Hour 30 Minutes</option>
          <option value="120">2 Hours</option>
        </select>
      </div>

      <div class="field">
        <label for="year">Select Year</label>
        <select id="year" required>
          <option value="">— Select Year —</option>
          <option value="2020">2020</option>
          <option value="2021">2021</option>
          <option value="2022">2022</option>
          <option value="2023">2023</option>
          <option value="2024">2024</option>
          <option value="2025">2025</option>
        </select>
      </div>

      <button type="submit" class="btn">Start Exam</button>
    </form>

    <div class="hint">You can always practice again later.</div>
  </div>

  <!-- Script -->
  <script>
    function startExam(event) {
      event.preventDefault();

      const duration = document.getElementById("duration").value;
      let year = document.getElementById("year").value;

      // Get the subject from the URL parameter
      const urlParams = new URLSearchParams(window.location.search);
      const subject = urlParams.get("subject");

      // If the subject is Yoruba, override the year to 2024
      if (subject && subject.toLowerCase() === "yoruba") {
        year = "2024"; // Always set year to 2024 for Yoruba
      }

      // If any of the necessary fields are empty, show an alert
      if (!subject || !duration || !year) {
        alert("Please select a subject, duration, and year.");
        return;
      }

      // Redirect to the appropriate page for the subject and year
      const redirectPage = `${subject}${year}.php`;
      window.location.href = `${redirectPage}?duration=${encodeURIComponent(duration)}`;
    }
  </script>


<?php include 'footer.php'; ?></body>
</html>
