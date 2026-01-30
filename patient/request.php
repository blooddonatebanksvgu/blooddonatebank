<?php
/**
 * Request Blood - Patient
 * Blood Bank Management System
 */

$pageTitle = 'Request Blood';
require_once '../includes/header.php';
requireRole('patient');

$patientInfo = getPatientByUserId(getUserId());
$patientId = $patientInfo['id'] ?? 0;
$bloodGroups = getBloodGroups();
$bloodBanks = getAllBloodBanks();

$error = '';

// Handle request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodGroupId = (int)$_POST['blood_group_id'];
    $bloodBankId = !empty($_POST['blood_bank_id']) ? (int)$_POST['blood_bank_id'] : null;
    $quantity = (int)$_POST['quantity'];
    $requiredDate = sanitize($_POST['required_date']);
    $reason = sanitize($_POST['reason']);
    $urgency = sanitize($_POST['urgency']);
    
    if (empty($bloodGroupId) || empty($quantity)) {
        $error = 'Please fill in all required fields.';
    } else if ($quantity < 100 || $quantity > 2000) {
        $error = 'Quantity must be between 100ml and 2000ml.';
    } else {
        $bbValue = $bloodBankId ? $bloodBankId : 'NULL';
        $insertQuery = "INSERT INTO blood_request (patient_id, blood_bank_id, blood_group_id, quantity_ml, request_date, required_date, reason, urgency, status) 
                        VALUES ($patientId, $bbValue, $bloodGroupId, $quantity, CURDATE(), " . ($requiredDate ? "'$requiredDate'" : "NULL") . ", '$reason', '$urgency', 'pending')";
        
        if (mysqli_query($conn, $insertQuery)) {
            setFlashMessage('success', 'Blood request submitted successfully. We will process it soon.');
            header("Location: history.php");
            exit();
        } else {
            $error = 'Failed to submit request. Please try again.';
        }
    }
}

// Get blood availability
$bloodStock = getBloodStockSummary();

require_once '../includes/patient_sidebar.php';
?>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-notes-medical"></i> Request Blood</h3>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="blood_group_id" class="required">Blood Group Needed</label>
                    <select id="blood_group_id" name="blood_group_id" class="form-control" required>
                        <option value="">Select Blood Group</option>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?php echo $bg['id']; ?>" 
                                <?php echo ($patientInfo['blood_group_id'] ?? 0) == $bg['id'] ? 'selected' : ''; ?>>
                                <?php echo $bg['group_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="required">Quantity (ml)</label>
                    <select id="quantity" name="quantity" class="form-control" required>
                        <option value="">Select Quantity</option>
                        <option value="450">450 ml (1 Unit)</option>
                        <option value="900">900 ml (2 Units)</option>
                        <option value="1350">1350 ml (3 Units)</option>
                        <option value="1800">1800 ml (4 Units)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="blood_bank_id">Preferred Blood Bank</label>
                    <select id="blood_bank_id" name="blood_bank_id" class="form-control">
                        <option value="">Any Available Blood Bank</option>
                        <?php foreach ($bloodBanks as $bb): ?>
                            <option value="<?php echo $bb['id']; ?>"><?php echo htmlspecialchars($bb['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="required_date">Required By Date</label>
                    <input type="date" id="required_date" name="required_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                    <small class="text-muted">Leave blank if needed immediately</small>
                </div>
                
                <div class="form-group">
                    <label for="urgency" class="required">Urgency Level</label>
                    <select id="urgency" name="urgency" class="form-control" required>
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Request</label>
                    <textarea id="reason" name="reason" class="form-control" rows="3" 
                              placeholder="Surgery, accident, medical condition, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-tint"></i> Current Availability</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Blood Group</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloodStock as $stock): ?>
                        <tr>
                            <td><span class="badge badge-danger"><?php echo $stock['group_name']; ?></span></td>
                            <td><?php echo number_format($stock['total_quantity']); ?> ml</td>
                            <td>
                                <?php if ($stock['total_quantity'] >= 2000): ?>
                                    <span class="badge badge-success">Available</span>
                                <?php elseif ($stock['total_quantity'] >= 500): ?>
                                    <span class="badge badge-warning">Limited</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Low</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="alert alert-info mt-2">
                <i class="fas fa-info-circle"></i>
                <span>Blood availability varies by blood bank. We'll find the best match for you.</span>
            </div>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
