<?php
/**
 * Get Locations AJAX Handler
 * Blood Bank Management System
 */

require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;

if ($cityId > 0) {
    $locations = getLocationsByCity($cityId);
    echo json_encode($locations);
} else {
    echo json_encode([]);
}
?>
