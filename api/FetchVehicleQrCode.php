<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$occupantId = $_GET['occupantId'];
$vehicleId = $_GET['vehicleId'];

$query = "SELECT QR_Code FROM tbloccupantvehicle WHERE Occupant_ID = :occupantId AND Vehicle_ID = :vehicleId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':occupantId', $occupantId, PDO::PARAM_INT);
$stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
$stmt->execute();
$qrCode = $stmt->fetchColumn();

$response = array();
if ($qrCode) {
    // Ensure QR code is in base64 format
    if (base64_encode(base64_decode($qrCode, true)) === $qrCode) {
        $response['qrCode'] = $qrCode;
    } else {
        $response['qrCode'] = base64_encode($qrCode);
    }
} else {
    $response['qrCode'] = null;
}

echo json_encode($response);
?>
