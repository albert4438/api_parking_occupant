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

$data = json_decode(file_get_contents('php://input'), true);
$occupantId = $data['occupantId'];
$vehicleId = $data['vehicleId'];
$qrCode = base64_decode($data['qrCode']);   

$query = "UPDATE tbloccupantvehicle SET QR_Code = :qrCode WHERE Occupant_ID = :occupantId AND Vehicle_ID = :vehicleId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':qrCode', $qrCode, PDO::PARAM_LOB);
$stmt->bindParam(':occupantId', $occupantId, PDO::PARAM_INT);
$stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);

$response = array();
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['error'] = $stmt->errorInfo();
}

echo json_encode($response);
?>
