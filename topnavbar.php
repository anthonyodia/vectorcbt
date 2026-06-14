<link rel="stylesheet" href="global.css">
<style>

  /* NAVBAR WRAPPER FIX */
  .navbar {
    width: 100%;
    background: #ffffff;
    padding: 14px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: "Segoe UI", Arial, sans-serif;
    border-bottom: 1px solid #eaeaea;
    box-sizing: border-box;

    /* IMPORTANT FIXES */
    position: relative;
    z-index: 1000;
    flex-wrap: wrap;
  }

  /* LOGO */
  .logo {
    color: #43e97b;
    font-weight: bold;
    font-size: 18px;
    white-space: nowrap;
  }

  /* NAV LINKS */
  .nav-links {
    display: flex;
    gap: 18px;
    align-items: center;
    flex-wrap: wrap; /* prevents overflow on small screens */
  }

  .nav-links a {
    color: #1e2a3a;
    text-decoration: none;
    font-size: 14px;
    transition: 0.2s;
    white-space: nowrap;
  }

  .nav-links a:hover {
    color: #43e97b;
  }

  /* DROPDOWN */
  .dropdown {
    position: relative;
  }

  .dropdown > a {
    display: inline-block;
    white-space: nowrap;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: white;
    min-width: 180px;
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    overflow: hidden;
    z-index: 2000;
  }

  .dropdown-content a {
    display: block;
    padding: 10px 14px;
    color: #1e2a3a;
    text-decoration: none;
    font-size: 14px;
  }

  .dropdown-content a:hover {
    background: #f2f2f2;
    color: #43e97b;
  }

  .dropdown:hover .dropdown-content {
    display: block;
  }

  /* MOBILE SAFETY FIX */
  @media (max-width: 600px) {
    .navbar {
      gap: 10px;
      padding: 12px 15px;
    }

    .nav-links {
      gap: 10px;
      font-size: 13px;
    }
  }
</style>

<div class="navbar">
  <div class="logo">Vector Learn</div>

  <div class="nav-links">
    <a href="#">Login As Premium</a>
    <a href="#">Login As Professional</a>
    <a href="#">About</a>

    <div class="dropdown">
      <a href="#">More ▾</a>
      <div class="dropdown-content">
        <a href="#">Support</a>
        <a href="#">Pricing</a>
        <a href="#">Contact</a>
      </div>
    </div>
  </div>
</div>
