<?php
/**
 * Request History - Patient
 * Blood Bank Management System
 */

$pageTitle = 'Request History';
require_once '../includes/header.php';
requireRole('patient');

$patientInfo = getPatientByUserId(getUserId());
$patientId = $patientInfo['id'] ?? 0;

// Get all requests
$requestsQuery = "SELECT br.*, bg.group_name, bb.name as blood_bank_name, bb.phone as blood_bank_phone 
                  FROM blood_request br 
                  JOIN blood_group bg ON br.blood_group_id = bg.id 
                  LEFT JOIN blood_bank bb ON br.blood_bank_id = bb.id 
                  WHERE br.patient_id = $patientId 
                  ORDER BY br.created_at DESC";
$requests = mysqli_fetch_all(mysqli_query($conn, $requestsQuery), MYSQLI_ASSOC);

// Calculate statistics
$totalQuantity = 0;
$approvedCount = 0;
$pendingCount = 0;

foreach ($requests as $r) {
    if ($r['status'] === 'approved') {
        $totalQuantity += $r['quantity_ml'];
        $approvedCount++;
    } else if ($r['status'] === 'pending') {
        $pendingCount++;
    }
}

require_once '../includes/patient_sidebar.php';
?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo count($requests); ?></h4>
            <p>Total Requests</p>
        </div>
    </div>
    
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
            <h4><?php echo number_format($totalQuantity); ?> ml</h4>
            <p>Blood Received</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> All Requests</h3>
        <a href="request.php" class="btn btn-sm btn-primary">New Request</a>
    </div>
    <div class="card-body">
        <?php if (count($requests) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request Date</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Required By</th>
                            <th>Blood Bank</th>
                            <th>Urgency</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $request): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo formatDate($request['request_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                <td><?php echo $request['quantity_ml']; ?> ml</td>
                                <td><?php echo $request['required_date'] ? formatDate($request['required_date']) : 'ASAP'; ?></td>
                                <td>
                                    <?php if ($request['blood_bank_name']): ?>
                                        <?php echo htmlspecialchars($request['blood_bank_name']); ?>
                                        <?php if ($request['blood_bank_phone']): ?>
                                            <br><small class="text-muted"><?php echo $request['blood_bank_phone']; ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Any Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $urgencyBadge = 'badge-info';
                                    if (($request['urgency'] ?? 'normal') === 'urgent') $urgencyBadge = 'badge-warning';
                                    else if (($request['urgency'] ?? 'normal') === 'emergency') $urgencyBadge = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $urgencyBadge; ?>"><?php echo ucfirst($request['urgency'] ?? 'normal'); ?></span>
                                </td>
                                <td><span class="badge <?php echo getStatusBadge($request['status']); ?>"><?php echo ucfirst($request['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h4>No Request History</h4>
                <p>You haven't made any blood requests yet.</p>
                <a href="request.php" class="btn btn-primary">Request Blood</a>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
