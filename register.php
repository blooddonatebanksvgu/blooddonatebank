<?php
/**
 * Registration Page
 * Blood Bank Management System
 */

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToRoleDashboard();
}

$error = '';
$bloodGroups = getBloodGroups();

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $bloodGroupId = isset($_POST['blood_group_id']) ? (int)$_POST['blood_group_id'] : null;
    $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
    $gender = sanitize($_POST['gender'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($phone) || !isValidPhone($phone)) {
        $errors[] = 'Valid 10-digit phone number is required.';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!in_array($role, ['donor', 'patient'])) {
        $errors[] = 'Invalid role selected.';
    }
    
    if ($role === 'donor' && empty($bloodGroupId)) {
        $errors[] = 'Blood group is required for donors.';
    }
    
    // Check if email already exists
    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        $errors[] = 'Email address already registered.';
    }
    
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $insertUser = "INSERT INTO users (name, email, password, phone, role, status) 
                       VALUES ('$name', '$email', '$hashedPassword', '$phone', '$role', 'active')";
        
        if (mysqli_query($conn, $insertUser)) {
            $userId = mysqli_insert_id($conn);
            
            // Insert role-specific data
            if ($role === 'donor') {
                $insertDonor = "INSERT INTO donor (user_id, blood_group_id, age, gender, address) 
                               VALUES ($userId, $bloodGroupId, " . ($age ? $age : 'NULL') . ", " . 
                               ($gender ? "'$gender'" : 'NULL') . ", " . 
                               ($address ? "'$address'" : 'NULL') . ")";
                mysqli_query($conn, $insertDonor);
            } else if ($role === 'patient') {
                $insertPatient = "INSERT INTO patient (user_id, blood_group_id, age, gender, address) 
                                 VALUES ($userId, " . ($bloodGroupId ? $bloodGroupId : 'NULL') . ", " . 
                                 ($age ? $age : 'NULL') . ", " . 
                                 ($gender ? "'$gender'" : 'NULL') . ", " . 
                                 ($address ? "'$address'" : 'NULL') . ")";
                mysqli_query($conn, $insertPatient);
            }
            
            // Redirect to login with success message
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = 'Registration failed. Please try again.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blood Bank Management System</title>
    <meta name="description" content="Register as a donor or patient in Blood Bank Management System">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 550px;">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-tint"></i>
                </div>
                <h1>Create Account</h1>
                <p>Register as Donor or Patient</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate>
                    <!-- Role Selection -->
                    <div class="form-group">
                        <label class="required">Register As</label>
                        <div class="role-select">
                            <label class="role-option <?php echo (isset($_POST['role']) && $_POST['role'] === 'donor') ? 'selected' : ''; ?>">
                                <input type="radio" name="role" value="donor" <?php echo (!isset($_POST['role']) || $_POST['role'] === 'donor') ? 'checked' : ''; ?> required>
                                <i class="fas fa-hand-holding-heart"></i>
                                <span>Donor</span>
                            </label>
                            <label class="role-option <?php echo (isset($_POST['role']) && $_POST['role'] === 'patient') ? 'selected' : ''; ?>">
                                <input type="radio" name="role" value="patient" <?php echo (isset($_POST['role']) && $_POST['role'] === 'patient') ? 'checked' : ''; ?>>
                                <i class="fas fa-user-injured"></i>
                                <span>Patient</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="required">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   placeholder="Enter your full name" required
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="Enter your email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   placeholder="10-digit phone number" required data-validate-phone
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="blood_group_id" class="required donor-field">Blood Group</label>
                            <select id="blood_group_id" name="blood_group_id" class="form-control">
                                <option value="">Select Blood Group</option>
                                <?php foreach ($bloodGroups as $bg): ?>
                                    <option value="<?php echo $bg['id']; ?>" <?php echo (isset($_POST['blood_group_id']) && $_POST['blood_group_id'] == $bg['id']) ? 'selected' : ''; ?>>
                                        <?php echo $bg['group_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" class="form-control" 
                                   placeholder="Your age" min="18" max="65"
                                   value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" 
                                  placeholder="Enter your address" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="required">Password</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Minimum 6 characters" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="required">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   placeholder="Confirm your password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="index.php"><i class="fas fa-home"></i> Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Toggle blood group requirement based on role
        document.querySelectorAll('input[name="role"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const bloodGroupField = document.getElementById('blood_group_id');
                if (this.value === 'donor') {
                    bloodGroupField.required = true;
                    bloodGroupField.parentNode.querySelector('label').classList.add('required');
                } else {
                    bloodGroupField.required = false;
                    bloodGroupField.parentNode.querySelector('label').classList.remove('required');
                }
            });
        });
        
        // Initial check
        if (document.querySelector('input[name="role"]:checked').value === 'donor') {
            document.getElementById('blood_group_id').required = true;
        }
    </script>
</body>
</html>
