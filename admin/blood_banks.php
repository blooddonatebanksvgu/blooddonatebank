<?php
/**
 * Manage Blood Banks - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Manage Blood Banks';
require_once '../includes/header.php';
requireRole('admin');

$states = getStates();
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $bbQuery = mysqli_query($conn, "SELECT user_id FROM blood_bank WHERE id = $id");
    if ($bb = mysqli_fetch_assoc($bbQuery)) {
        if ($bb['user_id']) {
            mysqli_query($conn, "DELETE FROM users WHERE id = " . $bb['user_id']);
        }
        mysqli_query($conn, "DELETE FROM blood_bank WHERE id = $id");
        setFlashMessage('success', 'Blood bank deleted successfully.');
    }
    header("Location: blood_banks.php");
    exit();
}

// Handle status update
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $currentStatus = $_GET['current'] ?? 'active';
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
    
    if (mysqli_query($conn, "UPDATE blood_bank SET status = '$newStatus' WHERE id = $id")) {
        setFlashMessage('success', 'Blood bank status updated successfully.');
    }
    header("Location: blood_banks.php");
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null;
    $cityId = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
    $locationId = !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null;
    $status = sanitize($_POST['status']);
    
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        if ($id > 0) {
            // Get existing blood bank
            $bbResult = mysqli_query($conn, "SELECT user_id FROM blood_bank WHERE id = $id");
            $bbData = mysqli_fetch_assoc($bbResult);
            $userId = $bbData['user_id'];
            
            if ($userId) {
                $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
                if (mysqli_num_rows($checkEmail) > 0) {
                    $error = 'Email address already registered.';
                }
            }
            
            if (empty($error)) {
                // Update user if exists
                if ($userId) {
                    mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId");
                }
                
                // Update blood bank
                $stateValue = $stateId ? $stateId : 'NULL';
                $cityValue = $cityId ? $cityId : 'NULL';
                $locationValue = $locationId ? $locationId : 'NULL';
                
                $updateQuery = "UPDATE blood_bank SET name = '$name', email = '$email', phone = '$phone', 
                               address = '$address', state_id = $stateValue, city_id = $cityValue, 
                               location_id = $locationValue, status = '$status' WHERE id = $id";
                mysqli_query($conn, $updateQuery);
                
                setFlashMessage('success', 'Blood bank updated successfully.');
                header("Location: blood_banks.php");
                exit();
            }
        } else {
            // Check email uniqueness
            $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email address already registered.';
            } else {
                // Create user for blood bank
                $password = password_hash('bloodbank123', PASSWORD_DEFAULT);
                $insertUser = "INSERT INTO users (name, email, password, phone, role, status) VALUES ('$name', '$email', '$password', '$phone', 'bloodbank', 'active')";
                
                if (mysqli_query($conn, $insertUser)) {
                    $userId = mysqli_insert_id($conn);
                    
                    $stateValue = $stateId ? $stateId : 'NULL';
                    $cityValue = $cityId ? $cityId : 'NULL';
                    $locationValue = $locationId ? $locationId : 'NULL';
                    
                    $insertBB = "INSERT INTO blood_bank (user_id, name, email, phone, address, state_id, city_id, location_id, status) 
                                VALUES ($userId, '$name', '$email', '$phone', '$address', $stateValue, $cityValue, $locationValue, '$status')";
                    mysqli_query($conn, $insertBB);
                    
                    // Initialize blood stock for all blood groups
                    $bbId = mysqli_insert_id($conn);
                    for ($i = 1; $i <= 8; $i++) {
                        mysqli_query($conn, "INSERT INTO blood_stock (blood_bank_id, blood_group_id, quantity_ml) VALUES ($bbId, $i, 0)");
                    }
                    
                    setFlashMessage('success', 'Blood bank added successfully. Default password: bloodbank123');
                    header("Location: blood_banks.php");
                    exit();
                } else {
                    $error = 'Failed to add blood bank.';
                }
            }
        }
    }
}

// Get all blood banks
$bbQuery = "SELECT bb.*, s.state_name, c.city_name, l.location_name 
            FROM blood_bank bb 
            LEFT JOIN state s ON bb.state_id = s.id 
            LEFT JOIN city c ON bb.city_id = c.id 
            LEFT JOIN location l ON bb.location_id = l.id 
            ORDER BY bb.created_at DESC";
$bloodBanks = mysqli_fetch_all(mysqli_query($conn, $bbQuery), MYSQLI_ASSOC);

// Get blood bank for editing
$editBB = null;
$cities = [];
$locations = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT * FROM blood_bank WHERE id = $editId";
    $editBB = mysqli_fetch_assoc(mysqli_query($conn, $editQuery));
    
    if ($editBB && $editBB['state_id']) {
        $cities = getCitiesByState($editBB['state_id']);
    }
    if ($editBB && $editBB['city_id']) {
        $locations = getLocationsByCity($editBB['city_id']);
    }
}

require_once '../includes/admin_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-<?php echo $editBB ? 'edit' : 'plus'; ?>"></i> <?php echo $editBB ? 'Edit Blood Bank' : 'Add New Blood Bank'; ?></h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <?php if ($editBB): ?>
                <input type="hidden" name="id" value="<?php echo $editBB['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Blood Bank Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo $editBB ? htmlspecialchars($editBB['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo $editBB ? htmlspecialchars($editBB['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required
                           value="<?php echo $editBB ? htmlspecialchars($editBB['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo ($editBB && $editBB['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editBB && $editBB['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="state_id">State</label>
                    <select id="state_id" name="state_id" class="form-control">
                        <option value="">Select State</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo $state['id']; ?>" <?php echo ($editBB && $editBB['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                <?php echo $state['state_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="city_id">City</label>
                    <select id="city_id" name="city_id" class="form-control">
                        <option value="">Select City</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo ($editBB && $editBB['city_id'] == $city['id']) ? 'selected' : ''; ?>>
                                <?php echo $city['city_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location_id">Location</label>
                    <select id="location_id" name="location_id" class="form-control">
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location['id']; ?>" <?php echo ($editBB && $editBB['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                <?php echo $location['location_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Full Address</label>
                <textarea id="address" name="address" class="form-control" rows="2"><?php echo $editBB ? htmlspecialchars($editBB['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editBB ? 'Update Blood Bank' : 'Add Blood Bank'; ?>
                </button>
                <?php if ($editBB): ?>
                    <a href="blood_banks.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> All Blood Banks</h3>
        <span class="badge badge-primary"><?php echo count($bloodBanks); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($bloodBanks) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bloodBanks as $index => $bb): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($bb['name']); ?></td>
                                <td><?php echo htmlspecialchars($bb['email']); ?></td>
                                <td><?php echo htmlspecialchars($bb['phone']); ?></td>
                                <td>
                                    <?php 
                                    $location = [];
                                    if ($bb['city_name']) $location[] = $bb['city_name'];
                                    if ($bb['state_name']) $location[] = $bb['state_name'];
                                    echo implode(', ', $location) ?: 'N/A';
                                    ?>
                                </td>
                                <td><span class="badge <?php echo getStatusBadge($bb['status']); ?>"><?php echo ucfirst($bb['status']); ?></span></td>
                                <td class="action-btns">
                                    <a href="blood_banks.php?edit=<?php echo $bb['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="blood_banks.php?toggle_status=<?php echo $bb['id']; ?>&current=<?php echo $bb['status']; ?>" 
                                       class="btn btn-sm btn-warning" title="Toggle Status">
                                        <i class="fas fa-toggle-<?php echo $bb['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                    </a>
                                    <a href="blood_banks.php?delete=<?php echo $bb['id']; ?>" class="btn btn-sm btn-danger" 
                                       data-confirm-delete="Are you sure you want to delete this blood bank?" title="Delete">
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
                <i class="fas fa-hospital"></i>
                <h4>No Blood Banks Found</h4>
                <p>Add new blood banks using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
