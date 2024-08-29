<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['vehicle_id'])) {
        $vehicleId = $data['vehicle_id'];

        $stmt = $conn->prepare('
            SELECT Action_Type 
            FROM tblparkinglog 
            WHERE Vehicle_ID = :vehicle_id 
            ORDER BY timestamp DESC 
            LIMIT 1
        ');
        $stmt->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                echo json_encode(['success' => true, 'last_action_type' => $result['Action_Type']]);
            } else {
                echo json_encode(['success' => true, 'last_action_type' => null]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch last action type']);
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
