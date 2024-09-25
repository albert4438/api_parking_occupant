<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php"; // Include your database connection file

try {
    // Query to fetch all parking lots
    $query = "SELECT Parking_lot_ID, Parking_Lot_Name FROM tblparkinglot";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch the results as an associative array
    $parkingLots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the parking lots in JSON format
    http_response_code(200);
    echo json_encode(array('success' => true, 'parking_lots' => $parkingLots));
    
} catch (PDOException $e) {
    // Return error message if something goes wrong
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
}
?>
