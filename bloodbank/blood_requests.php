<?php
/**
 * Blood Requests - Blood Bank
 * Blood Bank Management System
 */

$pageTitle = 'Blood Requests';
require_once '../includes/header.php';
requireRole('bloodbank');

$bloodBankInfo = getBloodBankByUserId(getUserId());
$bloodBankId = $bloodBankInfo['id'] ?? 0;

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $requestQuery = mysqli_query($conn, "SELECT * FROM blood_request WHERE id = $id AND blood_bank_id = $bloodBankId");
        $request = mysqli_fetch_assoc($requestQuery);
        
        if ($request) {
            if ($action === 'approve') {
                // Check stock
                $stockQuery = mysqli_query($conn, "SELECT quantity_ml FROM blood_stock WHERE blood_bank_id = $bloodBankId AND blood_group_id = {$request['blood_group_id']}");
                $stock = mysqli_fetch_assoc($stockQuery);
                
                if (!$stock || $stock['quantity_ml'] < $request['quantity_ml']) {
                    setFlashMessage('danger', 'Insufficient blood stock to approve this request.');
                    header("Location: blood_requests.php");
                    exit();
                }
                
                // Deduct from stock
                mysqli_query($conn, "UPDATE blood_stock SET quantity_ml = quantity_ml - {$request['quantity_ml']} WHERE blood_bank_id = $bloodBankId AND blood_group_id = {$request['blood_group_id']}");
            }
            
            mysqli_query($conn, "UPDATE blood_request SET status = '$status' WHERE id = $id");
            setFlashMessage('success', 'Blood request ' . $status . ' successfully.');
        }
    }
    header("Location: blood_requests.php");
    exit();
}

// Get filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Get all blood requests for this blood bank
$requestsQuery = "SELECT br.*, p.user_id, u.name as patient_name, u.phone, u.email, bg.group_name 
                  FROM blood_request br 
                  JOIN patient p ON br.patient_id = p.id 
                  JOIN users u ON p.user_id = u.id 
                  JOIN blood_group bg ON br.blood_group_id = bg.id 
                  WHERE br.blood_bank_id = $bloodBankId";

if ($statusFilter) {
    $requestsQuery .= " AND br.status = '$statusFilter'";
}

$requestsQuery .= " ORDER BY br.created_at DESC";
$requests = mysqli_fetch_all(mysqli_query($conn, $requestsQuery), MYSQLI_ASSOC);

require_once '../includes/bloodbank_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="d-flex gap-2 align-items-center flex-wrap">
            <select name="status" class="form-control" style="width: 200px;">
                <option value="">All Status</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="blood_requests.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-notes-medical"></i> Blood Requests</h3>
        <span class="badge badge-primary"><?php echo count($requests); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($requests) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Required Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $request): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($request['phone']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                </td>
                                <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                <td><?php echo $request['quantity_ml']; ?> ml</td>
                                <td><?php echo $request['required_date'] ? formatDate($request['required_date']) : 'ASAP'; ?></td>
                                <td><?php echo htmlspecialchars($request['reason'] ?? 'N/A'); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($request['status']); ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                <td class="action-btns">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <a href="blood_requests.php?action=approve&id=<?php echo $request['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="blood_requests.php?action=reject&id=<?php echo $request['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-notes-medical"></i>
                <h4>No Blood Requests</h4>
                <p>Blood requests will appear here when patients request blood from your bank.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
