<?php
/**
 * Donor Profile
 * Blood Bank Management System
 */

$pageTitle = 'My Profile';
require_once '../includes/header.php';
requireRole('donor');

$donorInfo = getDonorByUserId(getUserId());
$bloodGroups = getBloodGroups();
$error = '';
$success = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $gender = sanitize($_POST['gender']);
    $address = sanitize($_POST['address']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    
    $userId = getUserId();
    $donorId = $donorInfo['id'];
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else if (!isValidEmail($email)) {
        $error = 'Please enter a valid email.';
    } else {
        // Check email uniqueness
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
        if (mysqli_num_rows($checkEmail) > 0) {
            $error = 'Email already in use.';
        } else {
            // Update user
            mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId");
            
            // Update donor
            $ageValue = $age ? $age : 'NULL';
            mysqli_query($conn, "UPDATE donor SET age = $ageValue, gender = '$gender', address = '$address' WHERE id = $donorId");
            
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Handle password change
            if (!empty($currentPassword)) {
                $userQuery = mysqli_query($conn, "SELECT password FROM users WHERE id = $userId");
                $user = mysqli_fetch_assoc($userQuery);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } else if (!empty($newPassword) && strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    mysqli_query($conn, "UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
                    $success = 'Profile and password updated successfully.';
                } else if (!empty($newPassword)) {
                    $error = 'New password must be at least 6 characters.';
                }
            } else {
                $success = 'Profile updated successfully.';
            }
            
            // Refresh donor info
            $donorInfo = getDonorByUserId(getUserId());
        }
    }
}

require_once '../includes/donor_sidebar.php';
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
            <h3><i class="fas fa-user"></i> Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($donorInfo['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($donorInfo['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($donorInfo['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" class="form-control" min="18" max="65"
                               value="<?php echo $donorInfo['age'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="Male" <?php echo ($donorInfo['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($donorInfo['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($donorInfo['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Blood Group</label>
                    <input type="text" class="form-control" value="<?php echo $donorInfo['group_name'] ?? 'N/A'; ?>" disabled>
                    <small class="text-muted">Blood group cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($donorInfo['address'] ?? ''); ?></textarea>
                </div>
                
                <hr>
                <h4>Change Password</h4>
                <p class="text-muted">Leave blank to keep current password</p>
                
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
            <h3><i class="fas fa-id-card"></i> Donor Card</h3>
        </div>
        <div class="card-body">
            <div class="donor-card text-center p-3" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-radius: 15px;">
                <div class="mb-2">
                    <i class="fas fa-tint" style="font-size: 2rem;"></i>
                </div>
                <h3 style="margin: 0;"><?php echo htmlspecialchars($donorInfo['name'] ?? ''); ?></h3>
                <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;">
                    <?php echo $donorInfo['group_name'] ?? 'N/A'; ?>
                </p>
                <p style="margin: 5px 0;">Donor ID: #<?php echo str_pad($donorInfo['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></p>
                <p style="margin: 5px 0;">Total Donations: <?php echo $donorInfo['total_donations'] ?? 0; ?></p>
                <?php if ($donorInfo['last_donation_date']): ?>
                    <p style="margin: 5px 0;">Last Donation: <?php echo formatDate($donorInfo['last_donation_date']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mt-3">
                <table class="table">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge <?php echo getStatusBadge($donorInfo['status'] ?? 'active'); ?>"><?php echo ucfirst($donorInfo['status'] ?? 'active'); ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Member Since:</strong></td>
                        <td><?php echo formatDate($donorInfo['created_at'] ?? date('Y-m-d')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
