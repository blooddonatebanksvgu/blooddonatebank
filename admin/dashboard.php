<?php
/**
 * Admin Dashboard
 * Blood Bank Management System
 */

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
requireRole('admin');

// Get statistics
$totalDonors = getCount('donor');
$totalPatients = getCount('patient');
$totalBloodBanks = getCount('blood_bank');
$totalDonations = getCount('donation');
$pendingDonations = getCount('donation', "status = 'pending'");
$totalRequests = getCount('blood_request');
$pendingRequests = getCount('blood_request', "status = 'pending'");
$approvedRequests = getCount('blood_request', "status = 'approved'");
$totalBloodQuantity = getTotalBloodQuantity();
$bloodStock = getBloodStockSummary();

// Get recent donations
$recentDonationsQuery = "SELECT d.*, dn.user_id, u.name as donor_name, bg.group_name 
                         FROM donation d 
                         JOIN donor dn ON d.donor_id = dn.id 
                         JOIN users u ON dn.user_id = u.id 
                         JOIN blood_group bg ON d.blood_group_id = bg.id 
                         ORDER BY d.created_at DESC LIMIT 5";
$recentDonations = mysqli_fetch_all(mysqli_query($conn, $recentDonationsQuery), MYSQLI_ASSOC);

// Get recent blood requests
$recentRequestsQuery = "SELECT br.*, p.user_id, u.name as patient_name, bg.group_name 
                        FROM blood_request br 
                        JOIN patient p ON br.patient_id = p.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN blood_group bg ON br.blood_group_id = bg.id 
                        ORDER BY br.created_at DESC LIMIT 5";
$recentRequests = mysqli_fetch_all(mysqli_query($conn, $recentRequestsQuery), MYSQLI_ASSOC);

require_once '../includes/admin_sidebar.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-hand-holding-heart"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalDonors); ?></h4>
            <p>Total Donors</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalPatients); ?></h4>
            <p>Total Patients</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-notes-medical"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalRequests); ?></h4>
            <p>Blood Requests</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($approvedRequests); ?></h4>
            <p>Approved Requests</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalBloodQuantity); ?> ml</h4>
            <p>Total Blood Units</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-secondary">
            <i class="fas fa-hospital"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo number_format($totalBloodBanks); ?></h4>
            <p>Blood Banks</p>
        </div>
    </div>
</div>

<!-- Blood Group Stock Cards -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tint"></i> Blood Group Stock</h3>
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
    <!-- Pending Donations -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-syringe"></i> Recent Donations</h3>
            <span class="badge badge-warning"><?php echo $pendingDonations; ?> Pending</span>
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
                    <a href="donations.php" class="btn btn-sm btn-outline">View All Donations</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-syringe"></i>
                    <h4>No Donations Yet</h4>
                    <p>Donations will appear here once donors make donations.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Blood Requests -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-notes-medical"></i> Recent Requests</h3>
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
                    <a href="blood_requests.php" class="btn btn-sm btn-outline">View All Requests</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-notes-medical"></i>
                    <h4>No Blood Requests Yet</h4>
                    <p>Requests will appear here once patients request blood.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div><!-- End content-wrapper -->
</main><!-- End main-content -->

<?php require_once '../includes/footer.php'; ?>
