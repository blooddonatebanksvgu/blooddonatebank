<?php
/**
 * Blood Stock - Blood Bank
 * Blood Bank Management System
 */

$pageTitle = 'Blood Stock';
require_once '../includes/header.php';
requireRole('bloodbank');

$bloodGroups = getBloodGroups();
$bloodBankInfo = getBloodBankByUserId(getUserId());
$bloodBankId = $bloodBankInfo['id'] ?? 0;

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodGroupId = (int)$_POST['blood_group_id'];
    $quantity = (int)$_POST['quantity'];
    $operation = $_POST['operation'];
    
    if ($bloodGroupId && $quantity > 0) {
        if ($operation === 'set') {
            $checkQuery = mysqli_query($conn, "SELECT id FROM blood_stock WHERE blood_bank_id = $bloodBankId AND blood_group_id = $bloodGroupId");
            if (mysqli_num_rows($checkQuery) > 0) {
                mysqli_query($conn, "UPDATE blood_stock SET quantity_ml = $quantity WHERE blood_bank_id = $bloodBankId AND blood_group_id = $bloodGroupId");
            } else {
                mysqli_query($conn, "INSERT INTO blood_stock (blood_bank_id, blood_group_id, quantity_ml) VALUES ($bloodBankId, $bloodGroupId, $quantity)");
            }
        } else {
            updateBloodStock($bloodBankId, $bloodGroupId, $quantity, $operation);
        }
        setFlashMessage('success', 'Blood stock updated successfully.');
    }
    header("Location: blood_stock.php");
    exit();
}

// Get blood stock for this blood bank
$stockQuery = "SELECT bs.*, bg.group_name 
               FROM blood_stock bs 
               JOIN blood_group bg ON bs.blood_group_id = bg.id 
               WHERE bs.blood_bank_id = $bloodBankId 
               ORDER BY bg.group_name";
$stocks = mysqli_fetch_all(mysqli_query($conn, $stockQuery), MYSQLI_ASSOC);

$totalStock = getBloodStockSummary($bloodBankId);

require_once '../includes/bloodbank_sidebar.php';
?>

<!-- Blood Stock Overview -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tint"></i> Current Blood Stock</h3>
    </div>
    <div class="card-body">
        <div class="blood-group-grid">
            <?php foreach ($totalStock as $stock): ?>
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
    <!-- Update Stock Form -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-edit"></i> Update Stock</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="blood_group_id" class="required">Blood Group</label>
                    <select id="blood_group_id" name="blood_group_id" class="form-control" required>
                        <option value="">Select Blood Group</option>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?php echo $bg['id']; ?>"><?php echo $bg['group_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="required">Quantity (ml)</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" required placeholder="Enter quantity">
                </div>
                
                <div class="form-group">
                    <label for="operation" class="required">Operation</label>
                    <select id="operation" name="operation" class="form-control" required>
                        <option value="add">Add to Stock</option>
                        <option value="subtract">Remove from Stock</option>
                        <option value="set">Set Exact Value</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Stock Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Stock Details</h3>
        </div>
        <div class="card-body">
            <?php if (count($stocks) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Quantity</th>
                            <th>Units</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <tr>
                                <td><span class="badge badge-danger"><?php echo $stock['group_name']; ?></span></td>
                                <td><?php echo number_format($stock['quantity_ml']); ?> ml</td>
                                <td><?php echo floor($stock['quantity_ml'] / 450); ?></td>
                                <td><?php echo formatDateTime($stock['last_updated']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-boxes"></i>
                    <h4>No Stock Records</h4>
                    <p>Add stock using the form.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
