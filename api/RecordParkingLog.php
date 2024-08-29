<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

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

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        isset($data['occupant_id']) && 
        isset($data['vehicle_id']) && 
        isset($data['action_type']) && 
        isset($data['personnel_id'])
    ) {
        $occupantId = $data['occupant_id'];
        $vehicleId = $data['vehicle_id'];
        $actionType = $data['action_type'];
        $personnelId = $data['personnel_id'];

        // Prepare the SQL statement to insert into tblparkinglog
        $stmt = $conn->prepare('
            INSERT INTO tblparkinglog (Personnel_ID, Vehicle_ID, Occupant_ID, Action_Type) 
            VALUES (:personnel_id, :vehicle_id, :occupant_id, :action_type)
        ');
        $stmt->bindParam(':personnel_id', $personnelId, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->bindParam(':occupant_id', $occupantId, PDO::PARAM_INT);
        $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Log recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record log']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

?>
