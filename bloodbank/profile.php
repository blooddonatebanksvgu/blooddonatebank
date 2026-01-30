<?php
/**
 * Blood Bank Profile
 * Blood Bank Management System
 */

$pageTitle = 'Profile';
require_once '../includes/header.php';
requireRole('bloodbank');

$bloodBankInfo = getBloodBankByUserId(getUserId());
$states = getStates();
$error = '';
$success = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    
    $userId = getUserId();
    $bbId = $bloodBankInfo['id'];
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else {
        // Update user
        mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId");
        
        // Update blood bank
        mysqli_query($conn, "UPDATE blood_bank SET name = '$name', email = '$email', phone = '$phone', address = '$address' WHERE id = $bbId");
        
        $_SESSION['user_name'] = $name;
        
        // Handle password change
        if (!empty($currentPassword)) {
            $userQuery = mysqli_query($conn, "SELECT password FROM users WHERE id = $userId");
            $user = mysqli_fetch_assoc($userQuery);
            
            if (password_verify($currentPassword, $user['password']) && !empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
                $success = 'Profile and password updated successfully.';
            } else if (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            }
        } else {
            $success = 'Profile updated successfully.';
        }
        
        // Refresh blood bank info
        $bloodBankInfo = getBloodBankByUserId(getUserId());
    }
}

require_once '../includes/bloodbank_sidebar.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-hospital"></i> Blood Bank Profile</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="name" class="required">Blood Bank Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($bloodBankInfo['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($bloodBankInfo['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($bloodBankInfo['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($bloodBankInfo['address'] ?? ''); ?></textarea>
                </div>
                
                <hr>
                <h4>Change Password</h4>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
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
            <h3><i class="fas fa-info-circle"></i> Details</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="badge <?php echo getStatusBadge($bloodBankInfo['status'] ?? 'active'); ?>"><?php echo ucfirst($bloodBankInfo['status'] ?? 'active'); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Created:</strong></td>
                    <td><?php echo formatDate($bloodBankInfo['created_at'] ?? date('Y-m-d')); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
