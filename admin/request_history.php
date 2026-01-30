<?php
/**
 * Request History - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Request History';
require_once '../includes/header.php';
requireRole('admin');

// Get all blood requests with full history
$requestsQuery = "SELECT br.*, p.user_id, u.name as patient_name, u.phone, u.email, 
                         bg.group_name, bb.name as blood_bank_name 
                  FROM blood_request br 
                  JOIN patient p ON br.patient_id = p.id 
                  JOIN users u ON p.user_id = u.id 
                  JOIN blood_group bg ON br.blood_group_id = bg.id 
                  LEFT JOIN blood_bank bb ON br.blood_bank_id = bb.id 
                  ORDER BY br.created_at DESC";
$requests = mysqli_fetch_all(mysqli_query($conn, $requestsQuery), MYSQLI_ASSOC);

// Statistics
$totalRequests = count($requests);
$approvedCount = 0;
$rejectedCount = 0;
$pendingCount = 0;

foreach ($requests as $r) {
    if ($r['status'] === 'approved') $approvedCount++;
    else if ($r['status'] === 'rejected') $rejectedCount++;
    else $pendingCount++;
}

require_once '../includes/admin_sidebar.php';
?>

<!-- Statistics -->
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
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $approvedCount; ?></h4>
            <p>Approved</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo $rejectedCount; ?></h4>
            <p>Rejected</p>
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
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Complete Request History</h3>
        <div>
            <input type="text" id="searchInput" class="form-control" placeholder="Search..." style="width: 200px; display: inline-block;">
        </div>
    </div>
    <div class="card-body">
        <?php if (count($requests) > 0): ?>
            <div class="table-responsive">
                <table class="table" id="historyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Required Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $request): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($request['phone']); ?></small><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                </td>
                                <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                <td><?php echo $request['quantity_ml']; ?> ml</td>
                                <td><?php echo formatDate($request['request_date']); ?></td>
                                <td><?php echo $request['required_date'] ? formatDate($request['required_date']) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($request['reason'] ?? 'N/A'); ?></td>
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
                <p>Blood request history will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll('#historyTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
