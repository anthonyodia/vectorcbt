<style>
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
  }

  .logo {
    color: #43e97b;
    font-weight: bold;
    font-size: 18px;
  }

  .nav-links {
    display: flex;
    gap: 18px;
    align-items: center;
  }

  .nav-links a {
    color: #1e2a3a;
    text-decoration: none;
    font-size: 14px;
    transition: 0.2s;
  }

  .nav-links a:hover {
    color: #43e97b;
  }

  .dropdown {
    position: relative;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    min-width: 180px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    overflow: hidden;
    z-index: 100;
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
