<?php
/**
 * Donations - Blood Bank
 * Blood Bank Management System
 */

$pageTitle = 'Donations';
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
        
        $donationQuery = mysqli_query($conn, "SELECT * FROM donation WHERE id = $id AND blood_bank_id = $bloodBankId");
        $donation = mysqli_fetch_assoc($donationQuery);
        
        if ($donation) {
            mysqli_query($conn, "UPDATE donation SET status = '$status' WHERE id = $id");
            
            if ($status === 'approved') {
                updateBloodStock($bloodBankId, $donation['blood_group_id'], $donation['quantity_ml'], 'add');
                mysqli_query($conn, "UPDATE donor SET last_donation_date = '{$donation['donation_date']}', total_donations = total_donations + 1 WHERE id = {$donation['donor_id']}");
            }
            
            setFlashMessage('success', 'Donation ' . $status . ' successfully.');
        }
    }
    header("Location: donations.php");
    exit();
}

// Get all donations for this blood bank
$donationsQuery = "SELECT d.*, dn.user_id, u.name as donor_name, u.phone, u.email, bg.group_name 
                   FROM donation d 
                   JOIN donor dn ON d.donor_id = dn.id 
                   JOIN users u ON dn.user_id = u.id 
                   JOIN blood_group bg ON d.blood_group_id = bg.id 
                   WHERE d.blood_bank_id = $bloodBankId 
                   ORDER BY d.created_at DESC";
$donations = mysqli_fetch_all(mysqli_query($conn, $donationsQuery), MYSQLI_ASSOC);

require_once '../includes/bloodbank_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-syringe"></i> Blood Donations</h3>
        <span class="badge badge-primary"><?php echo count($donations); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($donations) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Donor</th>
                            <th>Contact</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Donation Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $index => $donation): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($donation['phone']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($donation['email']); ?></small>
                                </td>
                                <td><span class="badge badge-danger"><?php echo $donation['group_name']; ?></span></td>
                                <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                <td><?php echo formatDate($donation['donation_date']); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                <td class="action-btns">
                                    <?php if ($donation['status'] === 'pending'): ?>
                                        <a href="donations.php?action=approve&id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="donations.php?action=reject&id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-danger" title="Reject">
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
                <i class="fas fa-syringe"></i>
                <h4>No Donations Yet</h4>
                <p>Donations will appear here when donors donate blood to your bank.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
