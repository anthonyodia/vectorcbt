<?php
// clear_cookies.php
// Auto-flush all cookies each time a PHP page runs

if (!headers_sent()) {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 3600, '/');
        unset($_COOKIE[$name]);
    }
}

// Optional: clear session variables (but not destroy the session completely)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
}
?>
