<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";
$data = json_decode(file_get_contents("php://input"));

if (isset($data->occupant_ids) && is_array($data->occupant_ids) && !empty($data->occupant_ids)) {
    try {
        $conn->beginTransaction();
        foreach ($data->occupant_ids as $occupant_id) {
            // Archive the occupant
            $stmt = $conn->prepare("
                UPDATE tbloccupant
                SET Status = 'archived'
                WHERE Occupant_ID = :occupant_id
            ");
            $stmt->bindParam(':occupant_id', $occupant_id, PDO::PARAM_INT);  // Ensure occupant_id is an integer
            $stmt->execute();
        }
        $conn->commit();
        http_response_code(200);
        echo json_encode(array('success' => true));
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => 'Error archiving occupants: ' . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Invalid or empty occupant_ids array.'));
}
?>
