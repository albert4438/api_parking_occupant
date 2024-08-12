<?php
// Set up CORS headers to allow requests from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Allow OPTIONS method to handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connection.php';

// Endpoint to check the last log
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['occupantId']) && isset($_GET['vehicleId'])) {
    $occupantId = $_GET['occupantId'];
    $vehicleId = $_GET['vehicleId'];
    
    $sql = "SELECT * FROM tblparkinglog 
            WHERE Occupant_ID = :occupantId AND Vehicle_ID = :vehicleId 
            ORDER BY parklog_ID DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':occupantId', $occupantId, PDO::PARAM_INT);
    $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
    $stmt->execute();
    $lastLog = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($lastLog);
}

// Endpoint to record the log
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['occupantId']) || !isset($data['vehicleId']) || !isset($data['isTimeIn'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }
    
    $occupantId = $data['occupantId'];
    $vehicleId = $data['vehicleId'];
    $isTimeIn = $data['isTimeIn'];
    
    $currentDateTime = date('Y-m-d H:i:s');
    
    if ($isTimeIn) {
        // Log time_in
        $sql = "INSERT INTO tblparkinglog (time_in, Occupant_ID, Vehicle_ID) VALUES (:currentDateTime, :occupantId, :vehicleId)";
    } else {
        // Log time_out
        $sql = "UPDATE tblparkinglog SET time_out = :currentDateTime 
                WHERE Occupant_ID = :occupantId AND Vehicle_ID = :vehicleId AND time_out IS NULL";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':currentDateTime', $currentDateTime);
    $stmt->bindParam(':occupantId', $occupantId, PDO::PARAM_INT);
    $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
    
    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to execute query']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
