<?php
/**
 * Manage Donations - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Manage Donations';
require_once '../includes/header.php';
requireRole('admin');

$bloodBanks = getAllBloodBanks();

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        // Get donation details
        $donationQuery = mysqli_query($conn, "SELECT * FROM donation WHERE id = $id");
        $donation = mysqli_fetch_assoc($donationQuery);
        
        if ($donation) {
            // Update status
            mysqli_query($conn, "UPDATE donation SET status = '$status' WHERE id = $id");
            
            // If approved, update blood stock and donor's last donation date
            if ($status === 'approved' && $donation['blood_bank_id']) {
                updateBloodStock($donation['blood_bank_id'], $donation['blood_group_id'], $donation['quantity_ml'], 'add');
                
                // Update donor's last donation date and total donations
                mysqli_query($conn, "UPDATE donor SET last_donation_date = '{$donation['donation_date']}', 
                                    total_donations = total_donations + 1 WHERE id = {$donation['donor_id']}");
            }
            
            setFlashMessage('success', 'Donation ' . $status . ' successfully.');
        }
    }
    header("Location: donations.php");
    exit();
}

// Get filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Get all donations
$donationsQuery = "SELECT d.*, dn.user_id, u.name as donor_name, bg.group_name, bb.name as blood_bank_name 
                   FROM donation d 
                   JOIN donor dn ON d.donor_id = dn.id 
                   JOIN users u ON dn.user_id = u.id 
                   JOIN blood_group bg ON d.blood_group_id = bg.id 
                   LEFT JOIN blood_bank bb ON d.blood_bank_id = bb.id";

if ($statusFilter) {
    $donationsQuery .= " WHERE d.status = '$statusFilter'";
}

$donationsQuery .= " ORDER BY d.created_at DESC";
$donations = mysqli_fetch_all(mysqli_query($conn, $donationsQuery), MYSQLI_ASSOC);

require_once '../includes/admin_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Donations</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="d-flex gap-2 align-items-center flex-wrap">
            <div class="form-group mb-0" style="min-width: 200px;">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="donations.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-syringe"></i> All Donations</h3>
        <span class="badge badge-primary"><?php echo count($donations); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($donations) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Donor Name</th>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Blood Bank</th>
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
                                <td><span class="badge badge-danger"><?php echo $donation['group_name']; ?></span></td>
                                <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                <td><?php echo htmlspecialchars($donation['blood_bank_name'] ?? 'N/A'); ?></td>
                                <td><?php echo formatDate($donation['donation_date']); ?></td>
                                <td><span class="badge <?php echo getStatusBadge($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                <td class="action-btns">
                                    <?php if ($donation['status'] === 'pending'): ?>
                                        <a href="donations.php?action=approve&id=<?php echo $donation['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="donations.php?action=reject&id=<?php echo $donation['id']; ?>" 
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
                <i class="fas fa-syringe"></i>
                <h4>No Donations Found</h4>
                <p>Donations will appear here when donors make donations.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
