<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input data
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate the input
    if (isset($data['vehicleId']) && isset($data['vehicleColor'])) {
        $vehicleId = $data['vehicleId'];
        $vehicleColor = $data['vehicleColor'];

        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare("UPDATE tblvehicle SET Vehicle_Color = :vehicleColor WHERE Vehicle_ID = :vehicleId");
            $stmt->bindParam(':vehicleColor', $vehicleColor);
            $stmt->bindParam(':vehicleId', $vehicleId);

            // Execute the statement and check if the update was successful
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->errorInfo()[2]]);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn = null;
?>
