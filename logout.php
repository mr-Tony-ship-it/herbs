<?php
session_start(); // Start the session

// Function to destroy session
function destroySession() {
    // Unset all of the session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }
    // Finally, destroy the session
    session_destroy();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: user/login.php");
    exit;
}

// Check if the logged-in user is an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Destroy session and redirect to the index page if the user is admin
    destroySession();
    header("Location: index.php");
    exit;
}

// Destroy session and redirect to the feedback form or another page after logout
destroySession();
header("Location: feedback_form.php");
exit;
?>
