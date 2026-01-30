<?php
/**
 * Admin Profile
 * Blood Bank Management System
 */

$pageTitle = 'My Profile';
require_once '../includes/header.php';
requireRole('admin');

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $userId = getUserId();
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check email uniqueness
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
        if (mysqli_num_rows($checkEmail) > 0) {
            $error = 'Email address already in use.';
        } else {
            // Update basic info
            mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId");
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Handle password change
            if (!empty($currentPassword)) {
                $userQuery = mysqli_query($conn, "SELECT password FROM users WHERE id = $userId");
                $user = mysqli_fetch_assoc($userQuery);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } else if (empty($newPassword) || strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters.';
                } else if ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    mysqli_query($conn, "UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
                    $success = 'Profile and password updated successfully.';
                }
            } else {
                $success = 'Profile updated successfully.';
            }
        }
    }
}

// Get current user data
$userData = getUserById(getUserId());

require_once '../includes/admin_sidebar.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $success; ?></span>
    </div>
<?php endif; ?>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($userData['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($userData['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                </div>
                
                <hr>
                <h4 class="mb-2">Change Password</h4>
                <p class="text-muted mb-2">Leave blank to keep current password</p>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Account Details</h3>
        </div>
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="user-avatar" style="width: 100px; height: 100px; font-size: 3rem; margin: 0 auto;">
                    <?php echo strtoupper(substr($userData['name'], 0, 1)); ?>
                </div>
            </div>
            
            <table class="table">
                <tr>
                    <td><strong>Role:</strong></td>
                    <td><span class="badge badge-primary"><?php echo ucfirst($userData['role']); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="badge <?php echo getStatusBadge($userData['status']); ?>"><?php echo ucfirst($userData['status']); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Member Since:</strong></td>
                    <td><?php echo formatDate($userData['created_at']); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
