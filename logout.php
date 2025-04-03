<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to Login.php
header("Location: checkly.php");
exit();
?>