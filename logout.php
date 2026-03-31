<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
redirect('index.php');
?>
