<?php
/**
 * Access Denied Page
 * Blood Bank Management System
 */

require_once 'config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Blood Bank Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="access-denied">
        <div>
            <i class="fas fa-ban"></i>
            <h1>Access Denied</h1>
            <p>You don't have permission to access this page.</p>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo '/' . getUserRole() . '/dashboard.php'; ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
