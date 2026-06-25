<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page Not Available Yet</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, sans-serif;
}

body{
    background: linear-gradient(135deg,#4facfe,#43e97b);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.container{
    max-width:700px;
    width:100%;
    background:#fff;
    border-radius:20px;
    padding:50px 30px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
}

.error-code{
    font-size:100px;
    font-weight:bold;
    color:#007aff;
    line-height:1;
}

h1{
    color:#1e2a3a;
    margin:15px 0;
}

p{
    color:#666;
    font-size:18px;
    line-height:1.6;
    margin-bottom:30px;
}

.btn-group{
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
}

.btn{
    text-decoration:none;
    padding:14px 25px;
    border-radius:10px;
    color:#fff;
    font-weight:bold;
    transition:.3s;
}

.home-btn{
    background:#007aff;
}

.home-btn:hover{
    background:#005ecb;
}

.contact-btn{
    background:#43e97b;
}

.contact-btn:hover{
    background:#2cc965;
}

.notice{
    margin-top:25px;
    padding:15px;
    background:#f7f7f7;
    border-left:4px solid #007aff;
    border-radius:8px;
    color:#555;
}

@media(max-width:600px){
    .error-code{
        font-size:70px;
    }
}
</style>
</head>
<body>

<div class="container">

    <div class="error-code">404</div>

    <h1>Page Not Available Yet</h1>

    <p>
        The page you're looking for is currently unavailable,
        and under development, try other years for now
    </p>

    <div class="btn-group">
        <a href="index.php" class="btn home-btn">🏠 Go Home</a>
        <a href="/contact.php" class="btn contact-btn">📩 Contact Us</a>
    </div>

    <div class="notice">
        If you discovered this page through a link on our website,
        please let us know so we can fix it.
    </div>

</div>

</body>
</html>
