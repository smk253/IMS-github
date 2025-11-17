<?php
session_start();

// Destroy all session data
session_unset(); // unset all session variables
session_destroy(); // destroy the session

// Redirect to login page
header("Location: home.php");
exit();
?>
