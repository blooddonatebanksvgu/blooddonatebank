<?php
/**
 * Make Donation - Donor
 * Blood Bank Management System
 */

$pageTitle = 'Donate Blood';
require_once '../includes/header.php';
requireRole('donor');

$donorInfo = getDonorByUserId(getUserId());
$donorId = $donorInfo['id'] ?? 0;
$bloodGroups = getBloodGroups();
$bloodBanks = getAllBloodBanks();

$error = '';
$success = '';

// Check eligibility
$canDonate = true;
$daysUntilEligible = 0;
if ($donorInfo['last_donation_date']) {
    $lastDonation = new DateTime($donorInfo['last_donation_date']);
    $today = new DateTime();
    $diff = $today->diff($lastDonation);
    $daysSinceLastDonation = $diff->days;
    
    if ($daysSinceLastDonation < 56) {
        $canDonate = false;
        $daysUntilEligible = 56 - $daysSinceLastDonation;
    }
}

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canDonate) {
    $bloodBankId = (int)$_POST['blood_bank_id'];
    $bloodGroupId = $donorInfo['blood_group_id'];
    $quantity = (int)$_POST['quantity'];
    $donationDate = sanitize($_POST['donation_date']);
    $notes = sanitize($_POST['notes']);
    
    if (empty($bloodBankId) || empty($quantity) || empty($donationDate)) {
        $error = 'Please fill in all required fields.';
    } else if ($quantity < 200 || $quantity > 500) {
        $error = 'Donation quantity must be between 200ml and 500ml.';
    } else {
        $insertQuery = "INSERT INTO donation (donor_id, blood_bank_id, blood_group_id, quantity_ml, donation_date, notes, status) 
                        VALUES ($donorId, $bloodBankId, $bloodGroupId, $quantity, '$donationDate', '$notes', 'pending')";
        
        if (mysqli_query($conn, $insertQuery)) {
            setFlashMessage('success', 'Donation request submitted successfully. Waiting for approval.');
            header("Location: history.php");
            exit();
        } else {
            $error = 'Failed to submit donation request.';
        }
    }
}

require_once '../includes/donor_sidebar.php';
?>

<?php if (!$canDonate): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>You are not eligible to donate yet. Please wait <strong><?php echo $daysUntilEligible; ?> more days</strong>.</span>
    </div>
    
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-clock" style="font-size: 4rem; color: var(--warning-color);"></i>
            <h3 class="mt-2">Donation Cooldown</h3>
            <p>For your safety, you need to wait at least 56 days between donations.</p>
            <p><strong>Last Donation:</strong> <?php echo formatDate($donorInfo['last_donation_date']); ?></p>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
<?php else: ?>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-plus"></i> New Blood Donation</h3>
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
                    <label>Your Blood Group</label>
                    <input type="text" class="form-control" value="<?php echo $donorInfo['group_name']; ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="blood_bank_id" class="required">Select Blood Bank</label>
                    <select id="blood_bank_id" name="blood_bank_id" class="form-control" required>
                        <option value="">Choose a Blood Bank</option>
                        <?php foreach ($bloodBanks as $bb): ?>
                            <option value="<?php echo $bb['id']; ?>"><?php echo htmlspecialchars($bb['name']); ?> 
                                <?php if ($bb['city_name']): ?>(<?php echo $bb['city_name']; ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="required">Quantity (ml)</label>
                    <select id="quantity" name="quantity" class="form-control" required>
                        <option value="">Select Quantity</option>
                        <option value="350">350 ml (Standard)</option>
                        <option value="450">450 ml (One Unit)</option>
                        <option value="200">200 ml (Minimum)</option>
                        <option value="500">500 ml (Maximum)</option>
                    </select>
                    <small class="text-muted">Standard donation is 350-450ml</small>
                </div>
                
                <div class="form-group">
                    <label for="donation_date" class="required">Preferred Date</label>
                    <input type="date" id="donation_date" name="donation_date" class="form-control" required 
                           min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2" 
                              placeholder="Any health conditions or special instructions..."></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-heart"></i> Submit Donation Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Donation Guidelines</h3>
        </div>
        <div class="card-body">
            <h4>Before Donating:</h4>
            <ul>
                <li>Get a good night's sleep</li>
                <li>Eat a healthy meal (avoid fatty foods)</li>
                <li>Drink plenty of water</li>
                <li>Bring a valid ID</li>
            </ul>
            
            <h4>Eligibility Requirements:</h4>
            <ul>
                <li>Age: 18-65 years</li>
                <li>Weight: At least 50 kg</li>
                <li>Hemoglobin: At least 12.5 g/dL</li>
                <li>No tattoos/piercings in last 6 months</li>
                <li>No major surgeries in last 6 months</li>
                <li>56 days since last donation</li>
            </ul>
            
            <div class="alert alert-info mt-2">
                <i class="fas fa-lightbulb"></i>
                <span>One donation can save up to 3 lives!</span>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
