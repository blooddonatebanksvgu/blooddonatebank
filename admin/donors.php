<?php
/**
 * Manage Donors - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Manage Donors';
require_once '../includes/header.php';
requireRole('admin');

$bloodGroups = getBloodGroups();
$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Get user_id first
    $donorQuery = mysqli_query($conn, "SELECT user_id FROM donor WHERE id = $id");
    if ($donor = mysqli_fetch_assoc($donorQuery)) {
        // Delete user (donor will be deleted by cascade)
        if (mysqli_query($conn, "DELETE FROM users WHERE id = " . $donor['user_id'])) {
            setFlashMessage('success', 'Donor deleted successfully.');
        } else {
            setFlashMessage('danger', 'Failed to delete donor.');
        }
    }
    header("Location: donors.php");
    exit();
}

// Handle status update
if (isset($_GET['toggle_status'])) {
    $userId = (int)$_GET['toggle_status'];
    $currentStatus = $_GET['current'] ?? 'active';
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
    
    if (mysqli_query($conn, "UPDATE users SET status = '$newStatus' WHERE id = $userId")) {
        setFlashMessage('success', 'Donor status updated successfully.');
    } else {
        setFlashMessage('danger', 'Failed to update donor status.');
    }
    header("Location: donors.php");
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $bloodGroupId = (int)$_POST['blood_group_id'];
    $age = (int)$_POST['age'];
    $gender = sanitize($_POST['gender']);
    $address = sanitize($_POST['address']);
    $status = sanitize($_POST['status']);
    
    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($bloodGroupId)) {
        $error = 'Please fill in all required fields.';
    } else if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else if (!isValidPhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number.';
    } else {
        if ($id > 0) {
            // Get user_id for this donor
            $donorResult = mysqli_query($conn, "SELECT user_id FROM donor WHERE id = $id");
            $donorData = mysqli_fetch_assoc($donorResult);
            $userId = $donorData['user_id'];
            
            // Check email uniqueness (excluding current)
            $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email address already registered.';
            } else {
                // Update user
                mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone', status = '$status' WHERE id = $userId");
                
                // Update donor
                mysqli_query($conn, "UPDATE donor SET blood_group_id = $bloodGroupId, age = $age, gender = '$gender', address = '$address' WHERE id = $id");
                
                setFlashMessage('success', 'Donor updated successfully.');
                header("Location: donors.php");
                exit();
            }
        } else {
            // Check email uniqueness
            $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email address already registered.';
            } else {
                // Default password for new donors
                $password = password_hash('donor123', PASSWORD_DEFAULT);
                
                // Insert user
                $insertUser = "INSERT INTO users (name, email, password, phone, role, status) VALUES ('$name', '$email', '$password', '$phone', 'donor', '$status')";
                if (mysqli_query($conn, $insertUser)) {
                    $userId = mysqli_insert_id($conn);
                    
                    // Insert donor
                    $insertDonor = "INSERT INTO donor (user_id, blood_group_id, age, gender, address) VALUES ($userId, $bloodGroupId, $age, '$gender', '$address')";
                    mysqli_query($conn, $insertDonor);
                    
                    setFlashMessage('success', 'Donor added successfully. Default password: donor123');
                    header("Location: donors.php");
                    exit();
                } else {
                    $error = 'Failed to add donor.';
                }
            }
        }
    }
}

// Get all donors
$donorsQuery = "SELECT d.*, u.name, u.email, u.phone, u.status, u.created_at, bg.group_name 
                FROM donor d 
                JOIN users u ON d.user_id = u.id 
                JOIN blood_group bg ON d.blood_group_id = bg.id 
                ORDER BY u.created_at DESC";
$donors = mysqli_fetch_all(mysqli_query($conn, $donorsQuery), MYSQLI_ASSOC);

// Get donor for editing
$editDonor = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT d.*, u.name, u.email, u.phone, u.status 
                  FROM donor d 
                  JOIN users u ON d.user_id = u.id 
                  WHERE d.id = $editId";
    $editDonor = mysqli_fetch_assoc(mysqli_query($conn, $editQuery));
}

require_once '../includes/admin_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-<?php echo $editDonor ? 'edit' : 'plus'; ?>"></i> <?php echo $editDonor ? 'Edit Donor' : 'Add New Donor'; ?></h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <?php if ($editDonor): ?>
                <input type="hidden" name="id" value="<?php echo $editDonor['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo $editDonor ? htmlspecialchars($editDonor['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo $editDonor ? htmlspecialchars($editDonor['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required data-validate-phone
                           value="<?php echo $editDonor ? htmlspecialchars($editDonor['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="blood_group_id" class="required">Blood Group</label>
                    <select id="blood_group_id" name="blood_group_id" class="form-control" required>
                        <option value="">Select Blood Group</option>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?php echo $bg['id']; ?>" <?php echo ($editDonor && $editDonor['blood_group_id'] == $bg['id']) ? 'selected' : ''; ?>>
                                <?php echo $bg['group_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" class="form-control" min="18" max="65"
                           value="<?php echo $editDonor ? $editDonor['age'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($editDonor && $editDonor['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($editDonor && $editDonor['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($editDonor && $editDonor['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo ($editDonor && $editDonor['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editDonor && $editDonor['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2"><?php echo $editDonor ? htmlspecialchars($editDonor['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editDonor ? 'Update Donor' : 'Add Donor'; ?>
                </button>
                <?php if ($editDonor): ?>
                    <a href="donors.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> All Donors</h3>
        <span class="badge badge-primary"><?php echo count($donors); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($donors) > 0): ?>
            <div class="table-responsive">
                <table class="table" id="donorsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Age</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $index => $donor): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $donor['group_name']; ?></span></td>
                                <td><?php echo $donor['age'] ?? 'N/A'; ?></td>
                                <td><span class="badge <?php echo getStatusBadge($donor['status']); ?>"><?php echo ucfirst($donor['status']); ?></span></td>
                                <td><?php echo formatDate($donor['created_at']); ?></td>
                                <td class="action-btns">
                                    <a href="donors.php?edit=<?php echo $donor['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="donors.php?toggle_status=<?php echo $donor['user_id']; ?>&current=<?php echo $donor['status']; ?>" 
                                       class="btn btn-sm btn-warning" title="Toggle Status">
                                        <i class="fas fa-toggle-<?php echo $donor['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                    </a>
                                    <a href="donors.php?delete=<?php echo $donor['id']; ?>" class="btn btn-sm btn-danger" 
                                       data-confirm-delete="Are you sure you want to delete this donor?" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-holding-heart"></i>
                <h4>No Donors Found</h4>
                <p>Add new donors using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div><!-- End content-wrapper -->
</main><!-- End main-content -->

<?php require_once '../includes/footer.php'; ?>
