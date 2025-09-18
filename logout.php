<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Start the session
session_start();

// Enable error reporting to catch any PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Unset all session variables
$_SESSION = [];

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to index.php with a success message
header("Location: index.php?logout=success");
exit();

// Flush the output buffer
ob_end_flush();
?>