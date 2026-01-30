<?php
/**
 * Helper Functions
 * Blood Bank Management System
 */

require_once __DIR__ . '/database.php';

// Sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}

// Get all blood groups
function getBloodGroups() {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM blood_group ORDER BY group_name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get all states
function getStates() {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM state ORDER BY state_name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get cities by state
function getCitiesByState($stateId) {
    global $conn;
    $stateId = (int)$stateId;
    $result = mysqli_query($conn, "SELECT * FROM city WHERE state_id = $stateId ORDER BY city_name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get locations by city
function getLocationsByCity($cityId) {
    global $conn;
    $cityId = (int)$cityId;
    $result = mysqli_query($conn, "SELECT * FROM location WHERE city_id = $cityId ORDER BY location_name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get blood group name by ID
function getBloodGroupName($id) {
    global $conn;
    $id = (int)$id;
    $result = mysqli_query($conn, "SELECT group_name FROM blood_group WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['group_name'] : 'Unknown';
}

// Get user by ID
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    return mysqli_fetch_assoc($result);
}

// Get total count from table
function getCount($table, $condition = "") {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM $table";
    if ($condition) {
        $query .= " WHERE $condition";
    }
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] ?? 0;
}

// Get blood stock summary
function getBloodStockSummary($bloodBankId = null) {
    global $conn;
    
    $query = "SELECT bg.id, bg.group_name, COALESCE(SUM(bs.quantity_ml), 0) as total_quantity 
              FROM blood_group bg 
              LEFT JOIN blood_stock bs ON bg.id = bs.blood_group_id";
    
    if ($bloodBankId) {
        $bloodBankId = (int)$bloodBankId;
        $query .= " AND bs.blood_bank_id = $bloodBankId";
    }
    
    $query .= " GROUP BY bg.id, bg.group_name ORDER BY bg.group_name";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get total blood quantity
function getTotalBloodQuantity($bloodBankId = null) {
    global $conn;
    
    $query = "SELECT COALESCE(SUM(quantity_ml), 0) as total FROM blood_stock";
    
    if ($bloodBankId) {
        $bloodBankId = (int)$bloodBankId;
        $query .= " WHERE blood_bank_id = $bloodBankId";
    }
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

// Get status badge class
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'approved':
        case 'active':
            return 'badge-success';
        case 'pending':
            return 'badge-warning';
        case 'rejected':
        case 'inactive':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Update blood stock
function updateBloodStock($bloodBankId, $bloodGroupId, $quantity, $operation = 'add') {
    global $conn;
    
    $bloodBankId = (int)$bloodBankId;
    $bloodGroupId = (int)$bloodGroupId;
    $quantity = (int)$quantity;
    
    // Check if stock exists
    $checkQuery = "SELECT id, quantity_ml FROM blood_stock WHERE blood_bank_id = $bloodBankId AND blood_group_id = $bloodGroupId";
    $result = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($operation === 'add') {
            $newQuantity = $row['quantity_ml'] + $quantity;
        } else {
            $newQuantity = max(0, $row['quantity_ml'] - $quantity);
        }
        $updateQuery = "UPDATE blood_stock SET quantity_ml = $newQuantity WHERE id = " . $row['id'];
        return mysqli_query($conn, $updateQuery);
    } else {
        if ($operation === 'add') {
            $insertQuery = "INSERT INTO blood_stock (blood_bank_id, blood_group_id, quantity_ml) VALUES ($bloodBankId, $bloodGroupId, $quantity)";
            return mysqli_query($conn, $insertQuery);
        }
    }
    return false;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

// Generate pagination
function paginate($total, $perPage, $currentPage) {
    $totalPages = ceil($total / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

// Get all blood banks
function getAllBloodBanks() {
    global $conn;
    $result = mysqli_query($conn, "SELECT bb.*, c.city_name 
                                   FROM blood_bank bb 
                                   LEFT JOIN city c ON bb.city_id = c.id 
                                   WHERE bb.status = 'active' 
                                   ORDER BY bb.name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get blood bank by user ID
function getBloodBankByUserId($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = mysqli_query($conn, "SELECT * FROM blood_bank WHERE user_id = $userId");
    return mysqli_fetch_assoc($result);
}

// Get donor by user ID
function getDonorByUserId($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = mysqli_query($conn, "SELECT d.*, u.name, u.email, u.phone, u.status, u.created_at, bg.group_name 
                                   FROM donor d 
                                   JOIN users u ON d.user_id = u.id 
                                   JOIN blood_group bg ON d.blood_group_id = bg.id 
                                   WHERE d.user_id = $userId");
    return mysqli_fetch_assoc($result);
}

// Get patient by user ID
function getPatientByUserId($userId) {
    global $conn;
    $userId = (int)$userId;
    $result = mysqli_query($conn, "SELECT p.*, u.name, u.email, u.phone, u.status, u.created_at, bg.group_name 
                                   FROM patient p 
                                   JOIN users u ON p.user_id = u.id 
                                   LEFT JOIN blood_group bg ON p.blood_group_id = bg.id 
                                   WHERE p.user_id = $userId");
    return mysqli_fetch_assoc($result);
}

// Get all blood banks with location names
function getAllBloodBanksWithLocation() {
    global $conn;
    $result = mysqli_query($conn, "SELECT bb.*, s.state_name, c.city_name, l.location_name 
                                   FROM blood_bank bb 
                                   LEFT JOIN state s ON bb.state_id = s.id 
                                   LEFT JOIN city c ON bb.city_id = c.id 
                                   LEFT JOIN location l ON bb.location_id = l.id 
                                   WHERE bb.status = 'active' 
                                   ORDER BY bb.name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
