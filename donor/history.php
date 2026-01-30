<?php
/**
 * Donation History - Donor
 * Blood Bank Management System
 */

$pageTitle = 'Donation History';
require_once '../includes/header.php';
requireRole('donor');

$donorInfo = getDonorByUserId(getUserId());
$donorId = $donorInfo['id'] ?? 0;

// Get all donations
$donationsQuery = "SELECT d.*, bg.group_name, bb.name as blood_bank_name, bb.address as blood_bank_address 
                   FROM donation d 
                   JOIN blood_group bg ON d.blood_group_id = bg.id 
                   LEFT JOIN blood_bank bb ON d.blood_bank_id = bb.id 
                   WHERE d.donor_id = $donorId 
                   ORDER BY d.created_at DESC";
$donations = mysqli_fetch_all(mysqli_query($conn, $donationsQuery), MYSQLI_ASSOC);

// Calculate statistics
$totalDonated = 0;
$approvedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;

foreach ($donations as $d) {
    if ($d['status'] === 'approved') {
        $totalDonated += $d['quantity_ml'];
        $approvedCount++;
    } else if ($d['status'] === 'pending') {
        $pendingCount++;
    } else {
        $rejectedCount++;
    }
}

$livesSaved = floor($totalDonated / 450) * 3;

require_once '../includes/donor_sidebar.php';
?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedCount; ?></h4>
            <p>Approved</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $pendingCount; ?></h4>
            <p>Pending</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalDonated); ?> ml</h4>
            <p>Total Donated</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-details">
            <h4>~<?php echo $livesSaved; ?></h4>
            <p>Lives Saved</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> All Donations</h3>
        <span class="badge badge-primary"><?php echo count($donations); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($donations) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Blood Bank</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $index => $donation): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo formatDate($donation['donation_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $donation['group_name']; ?></span></td>
                                <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                <td>
                                    <?php echo htmlspecialchars($donation['blood_bank_name'] ?? 'N/A'); ?>
                                    <?php if ($donation['blood_bank_address']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($donation['blood_bank_address']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?php echo getStatusBadge($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                <td><?php echo formatDateTime($donation['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h4>No Donation History</h4>
                <p>You haven't made any donations yet.</p>
                <a href="donate.php" class="btn btn-primary">Donate Now</a>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
