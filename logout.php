<?php
session_start();

// Unset all of the session variables.
$_SESSION = [];

// Destroy the session.
session_destroy();

// To show a message on the login page, we can start a new session.
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page.
header("Location: login.php");
exit;
?>
