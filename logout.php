<?php
/**
 * Logout Script
 * Blood Bank Management System
 */

require_once 'config/session.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php?logout=1");
exit();
?>
