<?php
// Initialize the session
session_start();

// Unset all of the session variables to clear the data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// This is a security best practice to ensure the session is completely removed from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session on the server side
session_destroy();

// Redirect to the login page.
// Since login.php is in the same 'authurization' folder, we use a direct path.
header("Location: ./authurization/login.php");
exit;
?>