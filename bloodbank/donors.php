<?php
/**
 * View Donors - Blood Bank
 * Blood Bank Management System
 */

$pageTitle = 'Donors';
require_once '../includes/header.php';
requireRole('bloodbank');

$bloodBankInfo = getBloodBankByUserId(getUserId());
$bloodBankId = $bloodBankInfo['id'] ?? 0;

// Get donors who have donated to this blood bank
$donorsQuery = "SELECT DISTINCT d.*, u.name, u.email, u.phone, u.status, bg.group_name,
                       (SELECT COUNT(*) FROM donation WHERE donor_id = d.id AND blood_bank_id = $bloodBankId) as donation_count,
                       (SELECT SUM(quantity_ml) FROM donation WHERE donor_id = d.id AND blood_bank_id = $bloodBankId AND status = 'approved') as total_donated
                FROM donor d 
                JOIN users u ON d.user_id = u.id 
                JOIN blood_group bg ON d.blood_group_id = bg.id 
                JOIN donation dn ON d.id = dn.donor_id AND dn.blood_bank_id = $bloodBankId
                ORDER BY total_donated DESC";
$donors = mysqli_fetch_all(mysqli_query($conn, $donorsQuery), MYSQLI_ASSOC);

require_once '../includes/bloodbank_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-hand-holding-heart"></i> Donors</h3>
        <span class="badge badge-primary"><?php echo count($donors); ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (count($donors) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Blood Group</th>
                            <th>Donations</th>
                            <th>Total Donated</th>
                            <th>Last Donation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $index => $donor): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $donor['group_name']; ?></span></td>
                                <td><?php echo $donor['donation_count']; ?></td>
                                <td><?php echo number_format($donor['total_donated'] ?? 0); ?> ml</td>
                                <td><?php echo $donor['last_donation_date'] ? formatDate($donor['last_donation_date']) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-holding-heart"></i>
                <h4>No Donors Yet</h4>
                <p>Donors who donate to your blood bank will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
