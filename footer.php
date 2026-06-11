<style>
  .footer {
    width: 100%;
    background: #ffffff;
    color: #1e2a3a;
    padding: 30px 20px;
    font-family: "Segoe UI", Arial, sans-serif;
    margin-top: 40px;
    border-top: 1px solid #eaeaea;
    box-sizing: border-box;

    /* FIX: ensures full-width behavior in all layouts */
    position: relative;
    left: 0;
    right: 0;
  }

  /* container stays centered INSIDE full footer */
  .footer-container {
    max-width: 1000px;
    width: 95%;
    margin: 0 auto;

    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 25px;
  }

  .footer h3 {
    margin: 0 0 10px 0;
    color: #43e97b;
    font-size: 16px;
  }

  .footer p {
    font-size: 14px;
    color: #555;
    max-width: 220px;
    margin: 0;
  }

  .footer a {
    color: #1e2a3a;
    text-decoration: none;
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
    transition: 0.2s;
  }

  .footer a:hover {
    color: #43e97b;
  }

  .footer-bottom {
    text-align: center;
    margin-top: 25px;
    font-size: 13px;
    color: #98a3b3;
    border-top: 1px solid #eaeaea;
    padding-top: 15px;

    max-width: 1000px;
    width: 95%;
    margin-left: auto;
    margin-right: auto;
  }
</style>

<footer class="footer">
  <div class="footer-container">

    <div>
      <h3>Vector Learn</h3>
      <p>
        Learn smarter, grow faster. Your journey to mastery starts here.
      </p>
    </div>

    <div>
      <h3>Quick Links</h3>
      <a href="#">Home</a>
      <a href="#">About</a>
      <a href="#">Pricing</a>
      <a href="#">Support</a>
    </div>

    <div>
      <h3>Accounts</h3>
      <a href="#">Login As Premium</a>
      <a href="#">Login As Professional</a>
    </div>

    <div>
      <h3>Contact</h3>
      <a href="#">Email Us</a>
      <a href="#">Help Center</a>
    </div>

  </div>

  <div class="footer-bottom">
    &copy; 2026 Vector Learn. All rights reserved.
  </div>
</footer>
