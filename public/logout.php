<?php
session_start(); // Start it so we can destroy it
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session itself

// Redirect back to the home page
header("Location: index.php");
exit();
?>