<?php
/**
 * Get Cities AJAX Handler
 * Blood Bank Management System
 */

require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$stateId = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;

if ($stateId > 0) {
    $cities = getCitiesByState($stateId);
    echo json_encode($cities);
} else {
    echo json_encode([]);
}
?>
