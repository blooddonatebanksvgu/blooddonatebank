<?php
/**
 * Header Include
 * Blood Bank Management System
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Require login
requireLogin();

$currentUser = getUserById(getUserId());
$userInitial = strtoupper(substr(getUserName(), 0, 1));

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Blood Bank System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/blood_bank/assets/css/style.css">
</head>
<body>
    <div class="wrapper">
