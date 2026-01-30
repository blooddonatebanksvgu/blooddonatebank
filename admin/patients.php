<?php
/**
 * Manage Patients - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Manage Patients';
require_once '../includes/header.php';
requireRole('admin');

$bloodGroups = getBloodGroups();
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $patientQuery = mysqli_query($conn, "SELECT user_id FROM patient WHERE id = $id");
    if ($patient = mysqli_fetch_assoc($patientQuery)) {
        if (mysqli_query($conn, "DELETE FROM users WHERE id = " . $patient['user_id'])) {
            setFlashMessage('success', 'Patient deleted successfully.');
        } else {
            setFlashMessage('danger', 'Failed to delete patient.');
        }
    }
    header("Location: patients.php");
    exit();
}

// Handle status update
if (isset($_GET['toggle_status'])) {
    $userId = (int)$_GET['toggle_status'];
    $currentStatus = $_GET['current'] ?? 'active';
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
    
    if (mysqli_query($conn, "UPDATE users SET status = '$newStatus' WHERE id = $userId")) {
        setFlashMessage('success', 'Patient status updated successfully.');
    }
    header("Location: patients.php");
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $bloodGroupId = !empty($_POST['blood_group_id']) ? (int)$_POST['blood_group_id'] : null;
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $gender = sanitize($_POST['gender']);
    $address = sanitize($_POST['address']);
    $disease = sanitize($_POST['disease']);
    $status = sanitize($_POST['status']);
    
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else if (!isValidPhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number.';
    } else {
        if ($id > 0) {
            $patientResult = mysqli_query($conn, "SELECT user_id FROM patient WHERE id = $id");
            $patientData = mysqli_fetch_assoc($patientResult);
            $userId = $patientData['user_id'];
            
            $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email address already registered.';
            } else {
                mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email', phone = '$phone', status = '$status' WHERE id = $userId");
                
                $bgValue = $bloodGroupId ? $bloodGroupId : 'NULL';
                $ageValue = $age ? $age : 'NULL';
                mysqli_query($conn, "UPDATE patient SET blood_group_id = $bgValue, age = $ageValue, gender = '$gender', address = '$address', disease = '$disease' WHERE id = $id");
                
                setFlashMessage('success', 'Patient updated successfully.');
                header("Location: patients.php");
                exit();
            }
        } else {
            $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($checkEmail) > 0) {
                $error = 'Email address already registered.';
            } else {
                $password = password_hash('patient123', PASSWORD_DEFAULT);
                
                $insertUser = "INSERT INTO users (name, email, password, phone, role, status) VALUES ('$name', '$email', '$password', '$phone', 'patient', '$status')";
                if (mysqli_query($conn, $insertUser)) {
                    $userId = mysqli_insert_id($conn);
                    
                    $bgValue = $bloodGroupId ? $bloodGroupId : 'NULL';
                    $ageValue = $age ? $age : 'NULL';
                    $insertPatient = "INSERT INTO patient (user_id, blood_group_id, age, gender, address, disease) VALUES ($userId, $bgValue, $ageValue, '$gender', '$address', '$disease')";
                    mysqli_query($conn, $insertPatient);
                    
                    setFlashMessage('success', 'Patient added successfully. Default password: patient123');
                    header("Location: patients.php");
                    exit();
                } else {
                    $error = 'Failed to add patient.';
                }
            }
        }
    }
}

// Get all patients
$patientsQuery = "SELECT p.*, u.name, u.email, u.phone, u.status, u.created_at, bg.group_name 
                  FROM patient p 
                  JOIN users u ON p.user_id = u.id 
                  LEFT JOIN blood_group bg ON p.blood_group_id = bg.id 
                  ORDER BY u.created_at DESC";
$patients = mysqli_fetch_all(mysqli_query($conn, $patientsQuery), MYSQLI_ASSOC);

// Get patient for editing
$editPatient = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT p.*, u.name, u.email, u.phone, u.status 
                  FROM patient p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.id = $editId";
    $editPatient = mysqli_fetch_assoc(mysqli_query($conn, $editQuery));
}

require_once '../includes/admin_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-<?php echo $editPatient ? 'edit' : 'plus'; ?>"></i> <?php echo $editPatient ? 'Edit Patient' : 'Add New Patient'; ?></h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <?php if ($editPatient): ?>
                <input type="hidden" name="id" value="<?php echo $editPatient['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo $editPatient ? htmlspecialchars($editPatient['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo $editPatient ? htmlspecialchars($editPatient['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required data-validate-phone
                           value="<?php echo $editPatient ? htmlspecialchars($editPatient['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="blood_group_id">Blood Group (if known)</label>
                    <select id="blood_group_id" name="blood_group_id" class="form-control">
                        <option value="">Select Blood Group</option>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?php echo $bg['id']; ?>" <?php echo ($editPatient && $editPatient['blood_group_id'] == $bg['id']) ? 'selected' : ''; ?>>
                                <?php echo $bg['group_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" class="form-control" min="1" max="120"
                           value="<?php echo $editPatient ? $editPatient['age'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($editPatient && $editPatient['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($editPatient && $editPatient['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($editPatient && $editPatient['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo ($editPatient && $editPatient['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editPatient && $editPatient['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="disease">Medical Condition / Disease</label>
                <input type="text" id="disease" name="disease" class="form-control"
                       value="<?php echo $editPatient ? htmlspecialchars($editPatient['disease']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2"><?php echo $editPatient ? htmlspecialchars($editPatient['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editPatient ? 'Update Patient' : 'Add Patient'; ?>
                </button>
                <?php if ($editPatient): ?>
                    <a href="patients.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> All Patients</h3>
        <span class="badge badge-primary"><?php echo count($patients); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($patients) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Disease</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $index => $patient): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                <td>
                                    <?php if ($patient['group_name']): ?>
                                        <span class="badge badge-danger"><?php echo $patient['group_name']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($patient['disease'] ?? 'N/A'); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($patient['status']); ?>"><?php echo ucfirst($patient['status']); ?></span></td>
                                <td class="action-btns">
                                    <a href="patients.php?edit=<?php echo $patient['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="patients.php?toggle_status=<?php echo $patient['user_id']; ?>&current=<?php echo $patient['status']; ?>" 
                                       class="btn btn-sm btn-warning" title="Toggle Status">
                                        <i class="fas fa-toggle-<?php echo $patient['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                    </a>
                                    <a href="patients.php?delete=<?php echo $patient['id']; ?>" class="btn btn-sm btn-danger" 
                                       data-confirm-delete="Are you sure you want to delete this patient?" title="Delete">
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
                <i class="fas fa-users"></i>
                <h4>No Patients Found</h4>
                <p>Add new patients using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
