<?php
/**
 * Blood Bank Dashboard
 * Blood Bank Management System
 */

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
requireRole('bloodbank');

// Get blood bank info
$bloodBankInfo = getBloodBankByUserId(getUserId());
$bloodBankId = $bloodBankInfo['id'] ?? 0;

// Get statistics
$bloodStock = getBloodStockSummary($bloodBankId);
$totalBloodQuantity = getTotalBloodQuantity($bloodBankId);

$pendingDonations = getCount('donation', "blood_bank_id = $bloodBankId AND status = 'pending'");
$approvedDonations = getCount('donation', "blood_bank_id = $bloodBankId AND status = 'approved'");
$pendingRequests = getCount('blood_request', "blood_bank_id = $bloodBankId AND status = 'pending'");
$approvedRequests = getCount('blood_request', "blood_bank_id = $bloodBankId AND status = 'approved'");

// Get recent blood requests
$recentRequestsQuery = "SELECT br.*, p.user_id, u.name as patient_name, u.phone, bg.group_name 
                        FROM blood_request br 
                        JOIN patient p ON br.patient_id = p.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN blood_group bg ON br.blood_group_id = bg.id 
                        WHERE br.blood_bank_id = $bloodBankId 
                        ORDER BY br.created_at DESC LIMIT 5";
$recentRequests = mysqli_fetch_all(mysqli_query($conn, $recentRequestsQuery), MYSQLI_ASSOC);

// Get recent donations
$recentDonationsQuery = "SELECT d.*, dn.user_id, u.name as donor_name, bg.group_name 
                         FROM donation d 
                         JOIN donor dn ON d.donor_id = dn.id 
                         JOIN users u ON dn.user_id = u.id 
                         JOIN blood_group bg ON d.blood_group_id = bg.id 
                         WHERE d.blood_bank_id = $bloodBankId 
                         ORDER BY d.created_at DESC LIMIT 5";
$recentDonations = mysqli_fetch_all(mysqli_query($conn, $recentDonationsQuery), MYSQLI_ASSOC);

require_once '../includes/bloodbank_sidebar.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalBloodQuantity); ?> ml</h4>
            <p>Total Blood Stock</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $pendingRequests; ?></h4>
            <p>Pending Requests</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedRequests; ?></h4>
            <p>Approved Requests</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-syringe"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedDonations; ?></h4>
            <p>Donations Received</p>
        </div>
    </div>
</div>

<!-- Blood Group Stock Cards -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tint"></i> Blood Stock Overview</h3>
        <a href="blood_stock.php" class="btn btn-sm btn-primary">Manage Stock</a>
    </div>
    <div class="card-body">
        <div class="blood-group-grid">
            <?php foreach ($bloodStock as $stock): ?>
                <div class="blood-card">
                    <div class="blood-type"><?php echo $stock['group_name']; ?></div>
                    <div class="blood-quantity"><?php echo number_format($stock['total_quantity']); ?> ml</div>
                    <div class="blood-label"><?php echo floor($stock['total_quantity'] / 450); ?> Units</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="form-row">
    <!-- Recent Requests -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-notes-medical"></i> Recent Blood Requests</h3>
            <span class="badge badge-warning"><?php echo $pendingRequests; ?> Pending</span>
        </div>
        <div class="card-body">
            <?php if (count($recentRequests) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Blood</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                    <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                    <td><?php echo $request['quantity_ml']; ?> ml</td>
                                    <td><span class="badge <?php echo getStatusBadge($request['status']); ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <a href="blood_requests.php" class="btn btn-sm btn-outline">View All</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-notes-medical"></i>
                    <p>No blood requests yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Donations -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-syringe"></i> Recent Donations</h3>
        </div>
        <div class="card-body">
            <?php if (count($recentDonations) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Blood</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDonations as $donation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                    <td><span class="badge badge-danger"><?php echo $donation['group_name']; ?></span></td>
                                    <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                    <td><span class="badge <?php echo getStatusBadge($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <a href="donations.php" class="btn btn-sm btn-outline">View All</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-syringe"></i>
                    <p>No donations yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
