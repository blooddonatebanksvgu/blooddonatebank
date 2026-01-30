<?php
/**
 * Donor Dashboard
 * Blood Bank Management System
 */

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
requireRole('donor');

$donorInfo = getDonorByUserId(getUserId());
$donorId = $donorInfo['id'] ?? 0;
$bloodGroup = $donorInfo['group_name'] ?? 'N/A';

// Get statistics
$totalDonations = getCount('donation', "donor_id = $donorId");
$approvedDonations = getCount('donation', "donor_id = $donorId AND status = 'approved'");
$pendingDonations = getCount('donation', "donor_id = $donorId AND status = 'pending'");
$totalDonated = 0;

$totalQuery = mysqli_query($conn, "SELECT SUM(quantity_ml) as total FROM donation WHERE donor_id = $donorId AND status = 'approved'");
$totalResult = mysqli_fetch_assoc($totalQuery);
$totalDonated = $totalResult['total'] ?? 0;

// Get recent donations
$recentDonationsQuery = "SELECT d.*, bg.group_name, bb.name as blood_bank_name 
                         FROM donation d 
                         JOIN blood_group bg ON d.blood_group_id = bg.id 
                         LEFT JOIN blood_bank bb ON d.blood_bank_id = bb.id 
                         WHERE d.donor_id = $donorId 
                         ORDER BY d.created_at DESC LIMIT 5";
$recentDonations = mysqli_fetch_all(mysqli_query($conn, $recentDonationsQuery), MYSQLI_ASSOC);

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

require_once '../includes/donor_sidebar.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $bloodGroup; ?></h4>
            <p>Blood Group</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedDonations; ?></h4>
            <p>Total Donations</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalDonated); ?> ml</h4>
            <p>Blood Donated</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $pendingDonations; ?></h4>
            <p>Pending</p>
        </div>
    </div>
</div>

<!-- Donation Eligibility -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-check-circle"></i> Donation Eligibility</h3>
    </div>
    <div class="card-body">
        <?php if ($canDonate): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>You are eligible to donate blood! Help save lives today.</span>
            </div>
            <a href="donate.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Donate Blood Now
            </a>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i>
                <span>You can donate again in <strong><?php echo $daysUntilEligible; ?> days</strong>. (Minimum 56 days gap between donations)</span>
            </div>
        <?php endif; ?>
        
        <?php if ($donorInfo['last_donation_date']): ?>
            <p class="mt-2">
                <strong>Last Donation:</strong> <?php echo formatDate($donorInfo['last_donation_date']); ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Donations -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Donations</h3>
        <a href="history.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <?php if (count($recentDonations) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Blood Bank</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentDonations as $donation): ?>
                            <tr>
                                <td><?php echo formatDate($donation['donation_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $donation['group_name']; ?></span></td>
                                <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                <td><?php echo htmlspecialchars($donation['blood_bank_name'] ?? 'N/A'); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <h4>No Donations Yet</h4>
                <p>Make your first donation and help save lives!</p>
                <?php if ($canDonate): ?>
                    <a href="donate.php" class="btn btn-primary">Donate Now</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
