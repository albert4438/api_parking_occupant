<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type');  
header('Content-Type: application/json');

// Handle preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include('connection.php');

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check for required keys and handle missing values
if (!isset($data['vehicleId']) || !isset($data['newStatus'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

$vehicleId = $data['vehicleId'];
$newStatus = $data['newStatus'];

try {
    $conn->beginTransaction();

    // Update the QR_Status in the database
    $query = "UPDATE tbloccupantvehicle SET QR_Status = :newStatus WHERE Vehicle_ID = :vehicleId";
    $stmt = $conn->prepare($query);

    // Bind the parameters
    $stmt->bindParam(':newStatus', $newStatus);
    $stmt->bindParam(':vehicleId', $vehicleId);

    // Execute the query
    $stmt->execute();

    $conn->commit();

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    $conn->rollback();
    error_log('Error updating vehicle status: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$conn = null;
?>
