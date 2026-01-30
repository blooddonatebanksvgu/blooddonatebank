<?php
/**
 * Patient Profile
 * Blood Bank Management System
 */

$pageTitle = 'My Profile';
require_once '../includes/header.php';
requireRole('patient');

$patientInfo = getPatientByUserId(getUserId());
$bloodGroups = getBloodGroups();
$error = '';
$success = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $bloodGroupId = !empty($_POST['blood_group_id']) ? (int)$_POST['blood_group_id'] : null;
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $gender = sanitize($_POST['gender']);
    $address = sanitize($_POST['address']);
    $disease = sanitize($_POST['disease']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    
    $userId = getUserId();
    $patientId = $patientInfo['id'];
    
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
            
            // Update patient
            $bgValue = $bloodGroupId ? $bloodGroupId : 'NULL';
            $ageValue = $age ? $age : 'NULL';
            mysqli_query($conn, "UPDATE patient SET blood_group_id = $bgValue, age = $ageValue, gender = '$gender', address = '$address', disease = '$disease' WHERE id = $patientId");
            
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
            
            // Refresh patient info
            $patientInfo = getPatientByUserId(getUserId());
        }
    }
}

require_once '../includes/patient_sidebar.php';
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
                           value="<?php echo htmlspecialchars($patientInfo['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($patientInfo['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($patientInfo['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" class="form-control" min="1" max="120"
                               value="<?php echo $patientInfo['age'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="Male" <?php echo ($patientInfo['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($patientInfo['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($patientInfo['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="blood_group_id">Blood Group (if known)</label>
                    <select id="blood_group_id" name="blood_group_id" class="form-control">
                        <option value="">Unknown</option>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?php echo $bg['id']; ?>" 
                                <?php echo ($patientInfo['blood_group_id'] ?? 0) == $bg['id'] ? 'selected' : ''; ?>>
                                <?php echo $bg['group_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="disease">Medical Condition</label>
                    <input type="text" id="disease" name="disease" class="form-control"
                           value="<?php echo htmlspecialchars($patientInfo['disease'] ?? ''); ?>"
                           placeholder="Any medical condition or disease">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($patientInfo['address'] ?? ''); ?></textarea>
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
            <h3><i class="fas fa-info-circle"></i> Account Details</h3>
        </div>
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="user-avatar" style="width: 100px; height: 100px; font-size: 3rem; margin: 0 auto;">
                    <?php echo strtoupper(substr($patientInfo['name'] ?? 'P', 0, 1)); ?>
                </div>
            </div>
            
            <table class="table">
                <tr>
                    <td><strong>Patient ID:</strong></td>
                    <td>#<?php echo str_pad($patientInfo['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td><strong>Blood Group:</strong></td>
                    <td>
                        <?php if ($patientInfo['group_name'] ?? null): ?>
                            <span class="badge badge-danger"><?php echo $patientInfo['group_name']; ?></span>
                        <?php else: ?>
                            <span class="text-muted">Not specified</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="badge <?php echo getStatusBadge($patientInfo['status'] ?? 'active'); ?>"><?php echo ucfirst($patientInfo['status'] ?? 'active'); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Member Since:</strong></td>
                    <td><?php echo formatDate($patientInfo['created_at'] ?? date('Y-m-d')); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
