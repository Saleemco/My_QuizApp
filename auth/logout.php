<?php
require_once __DIR__ . '/../includes/session.php';

// Logout user using the improved logout function
logout();

// Redirect to home page (no flash message needed since session is destroyed)
header('Location: ../index.php');
exit;
?>