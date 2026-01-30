<?php
/**
 * Patient Dashboard
 * Blood Bank Management System
 */

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
requireRole('patient');

$patientInfo = getPatientByUserId(getUserId());
$patientId = $patientInfo['id'] ?? 0;

// Get statistics
$totalRequests = getCount('blood_request', "patient_id = $patientId");
$approvedRequests = getCount('blood_request', "patient_id = $patientId AND status = 'approved'");
$pendingRequests = getCount('blood_request', "patient_id = $patientId AND status = 'pending'");
$rejectedRequests = getCount('blood_request', "patient_id = $patientId AND status = 'rejected'");

// Get recent requests
$recentRequestsQuery = "SELECT br.*, bg.group_name, bb.name as blood_bank_name 
                        FROM blood_request br 
                        JOIN blood_group bg ON br.blood_group_id = bg.id 
                        LEFT JOIN blood_bank bb ON br.blood_bank_id = bb.id 
                        WHERE br.patient_id = $patientId 
                        ORDER BY br.created_at DESC LIMIT 5";
$recentRequests = mysqli_fetch_all(mysqli_query($conn, $recentRequestsQuery), MYSQLI_ASSOC);

// Get blood availability
$bloodStock = getBloodStockSummary();

require_once '../includes/patient_sidebar.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $totalRequests; ?></h4>
            <p>Total Requests</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $pendingRequests; ?></h4>
            <p>Pending</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedRequests; ?></h4>
            <p>Approved</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $rejectedRequests; ?></h4>
            <p>Rejected</p>
        </div>
    </div>
</div>

<!-- Blood Availability -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tint"></i> Blood Availability</h3>
        <a href="request.php" class="btn btn-sm btn-primary">Request Blood</a>
    </div>
    <div class="card-body">
        <div class="blood-group-grid">
            <?php foreach ($bloodStock as $stock): ?>
                <div class="blood-card <?php echo $stock['total_quantity'] < 500 ? 'low-stock' : ''; ?>">
                    <div class="blood-type"><?php echo $stock['group_name']; ?></div>
                    <div class="blood-quantity"><?php echo number_format($stock['total_quantity']); ?> ml</div>
                    <div class="blood-label">
                        <?php 
                        if ($stock['total_quantity'] >= 2000) echo 'Available';
                        else if ($stock['total_quantity'] >= 500) echo 'Limited';
                        else echo 'Low Stock';
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Requests -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Requests</h3>
        <a href="history.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="card-body">
        <?php if (count($recentRequests) > 0): ?>
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
                        <?php foreach ($recentRequests as $request): ?>
                            <tr>
                                <td><?php echo formatDate($request['request_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                <td><?php echo $request['quantity_ml']; ?> ml</td>
                                <td><?php echo htmlspecialchars($request['blood_bank_name'] ?? 'Any Available'); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($request['status']); ?>"><?php echo ucfirst($request['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-notes-medical"></i>
                <h4>No Requests Yet</h4>
                <p>You haven't made any blood requests yet.</p>
                <a href="request.php" class="btn btn-primary">Request Blood</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.blood-card.low-stock {
    background: linear-gradient(135deg, #ffeef0, #fff);
    border-color: #dc3545;
}
.blood-card.low-stock .blood-label {
    color: #dc3545;
}
</style>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
