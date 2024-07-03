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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $occupantId = $data['occupantId'];
    $qrCodeImage = $data['qrCodeImage'];

    // Save the QR code image to the tbloccupant table
    $stmt = $conn->prepare("UPDATE tbloccupant SET QR_Code = :qrCodeImage WHERE Occupant_ID = :occupantId");
    $stmt->bindParam(':qrCodeImage', $qrCodeImage, PDO::PARAM_LOB);
    $stmt->bindParam(':occupantId', $occupantId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $response = array('success' => true, 'message' => 'QR code saved successfully');
    } else {
        $response = array('success' => false, 'error' => 'Failed to save QR code');
    }

    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
}
?>