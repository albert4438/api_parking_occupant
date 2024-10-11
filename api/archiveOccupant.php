<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond to preflight request with appropriate headers
    http_response_code(200);
    exit();
}

include "connection.php";

$data = json_decode(file_get_contents("php://input"));

if (isset($data->occupant_id)) {
    $occupant_id = $data->occupant_id;

    try {
        // Archive the occupant
        $stmt = $conn->prepare("
            UPDATE tbloccupant
            SET Status = 'archived'
            WHERE Occupant_ID = :occupant_id
        ");
        $stmt->bindParam(':occupant_id', $occupant_id);
        $stmt->execute();

        // Invalidate the QR code status
        $stmt2 = $conn->prepare("
            UPDATE tbloccupantvehicle
            SET QR_Status = 'INVALID' 
            WHERE Occupant_ID = :occupant_id
        ");
        $stmt2->bindParam(':occupant_id', $occupant_id);
        $stmt2->execute();

        // Always return a success response
        http_response_code(200);
        echo json_encode(array('success' => true));
    } catch (PDOException $e) {
        // Return an error response
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => 'Error archiving occupant: ' . $e->getMessage()));
    }
} else {
    // Invalid input response
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Invalid input'));
}
?>
