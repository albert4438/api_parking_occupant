<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if (isset($_SERVER['REQUEST_METHOD'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
} else {
    echo json_encode(['error' => 'This script must be run in a web server context']);
    exit();
}

$occupantId = isset($_GET['id'])? intval($_GET['id']) : 0;

if ($occupantId > 0) {
    // Modified SQL to include QR_Status from tbloccupantvehicle
    $stmt = $conn->prepare("SELECT 
                            v.Vehicle_ID,
                            v.Vehicle_type, 
                            v.Vehicle_color, 
                            v.Vehicle_platenumber, 
                            v.Vehicle_model, 
                            v.Vehicle_brand,
                            ov.Occupant_ID,
                            ov.QR_Status  -- Include the QR_Status column
                            FROM tbloccupantvehicle ov
                            JOIN tblvehicle v ON ov.Vehicle_ID = v.Vehicle_ID
                            WHERE ov.Occupant_ID = ?");

    $stmt->execute([$occupantId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['vehicles' => $result, 'Success' => true]);
    } else {
        echo json_encode(['error' => 'No vehicles found for occupant', 'Success' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid occupant ID', 'Success' => false]);
}
?>
