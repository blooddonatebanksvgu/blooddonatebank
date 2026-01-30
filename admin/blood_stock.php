<?php
/**
 * Manage Blood Stock - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Blood Stock';
require_once '../includes/header.php';
requireRole('admin');

$bloodGroups = getBloodGroups();
$bloodBanks = getAllBloodBanks();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodBankId = (int)$_POST['blood_bank_id'];
    $bloodGroupId = (int)$_POST['blood_group_id'];
    $quantity = (int)$_POST['quantity'];
    $operation = $_POST['operation'];
    
    if ($bloodBankId && $bloodGroupId && $quantity > 0) {
        if ($operation === 'set') {
            // Check if stock exists
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
    } else {
        setFlashMessage('danger', 'Please fill in all fields correctly.');
    }
    header("Location: blood_stock.php");
    exit();
}

// Get filter
$bloodBankFilter = isset($_GET['blood_bank']) ? (int)$_GET['blood_bank'] : 0;

// Get blood stock
$stockQuery = "SELECT bs.*, bg.group_name, bb.name as blood_bank_name 
               FROM blood_stock bs 
               JOIN blood_group bg ON bs.blood_group_id = bg.id 
               JOIN blood_bank bb ON bs.blood_bank_id = bb.id";

if ($bloodBankFilter) {
    $stockQuery .= " WHERE bs.blood_bank_id = $bloodBankFilter";
}

$stockQuery .= " ORDER BY bb.name, bg.group_name";
$stocks = mysqli_fetch_all(mysqli_query($conn, $stockQuery), MYSQLI_ASSOC);

// Get total stock summary
$totalStock = getBloodStockSummary();

require_once '../includes/admin_sidebar.php';
?>

<!-- Blood Stock Overview -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tint"></i> Overall Blood Stock</h3>
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
                    <label for="blood_bank_id" class="required">Blood Bank</label>
                    <select id="blood_bank_id" name="blood_bank_id" class="form-control" required>
                        <option value="">Select Blood Bank</option>
                        <?php foreach ($bloodBanks as $bb): ?>
                            <option value="<?php echo $bb['id']; ?>"><?php echo htmlspecialchars($bb['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
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
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" required placeholder="Enter quantity in ml">
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
    
    <!-- Filter -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Filter by Blood Bank</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="form-group">
                    <select name="blood_bank" class="form-control" onchange="this.form.submit()">
                        <option value="">All Blood Banks</option>
                        <?php foreach ($bloodBanks as $bb): ?>
                            <option value="<?php echo $bb['id']; ?>" <?php echo $bloodBankFilter == $bb['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bb['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <?php if ($bloodBankFilter): ?>
                <a href="blood_stock.php" class="btn btn-secondary btn-block mt-2">
                    <i class="fas fa-times"></i> Clear Filter
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Blood Stock Details</h3>
        <span class="badge badge-primary"><?php echo count($stocks); ?> Records</span>
    </div>
    <div class="card-body">
        <?php if (count($stocks) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Blood Bank</th>
                            <th>Blood Group</th>
                            <th>Quantity (ml)</th>
                            <th>Units</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $index => $stock): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($stock['blood_bank_name']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $stock['group_name']; ?></span></td>
                                <td><?php echo number_format($stock['quantity_ml']); ?> ml</td>
                                <td><?php echo floor($stock['quantity_ml'] / 450); ?></td>
                                <td><?php echo formatDateTime($stock['last_updated']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-boxes"></i>
                <h4>No Stock Records Found</h4>
                <p>Add stock using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
