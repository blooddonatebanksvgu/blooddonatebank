<?php
/**
 * Request History - Blood Bank
 * Blood Bank Management System
 */

$pageTitle = 'Request History';
require_once '../includes/header.php';
requireRole('bloodbank');

$bloodBankInfo = getBloodBankByUserId(getUserId());
$bloodBankId = $bloodBankInfo['id'] ?? 0;

// Get all requests for this blood bank
$requestsQuery = "SELECT br.*, p.user_id, u.name as patient_name, u.phone, bg.group_name 
                  FROM blood_request br 
                  JOIN patient p ON br.patient_id = p.id 
                  JOIN users u ON p.user_id = u.id 
                  JOIN blood_group bg ON br.blood_group_id = bg.id 
                  WHERE br.blood_bank_id = $bloodBankId 
                  ORDER BY br.created_at DESC";
$requests = mysqli_fetch_all(mysqli_query($conn, $requestsQuery), MYSQLI_ASSOC);

require_once '../includes/bloodbank_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Request History</h3>
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
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Required Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $request): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['phone']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $request['group_name']; ?></span></td>
                                <td><?php echo $request['quantity_ml']; ?> ml</td>
                                <td><?php echo formatDate($request['request_date']); ?></td>
                                <td><?php echo $request['required_date'] ? formatDate($request['required_date']) : 'ASAP'; ?></td>
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
                <p>Request history will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
