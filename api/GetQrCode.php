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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $occupantId = $_GET['occupantId'];

  // Retrieve the QR code image from the tbloccupant table
  $stmt = $conn->prepare("SELECT QR_Code FROM tbloccupant WHERE Occupant_ID = :occupantId");
  $stmt->bindParam(':occupantId', $occupantId);
  $stmt->execute();
  $result = $stmt->fetch();

  if ($result) {
    $qrCodeImage = $result['QR_Code'];
    $response = array('success' => true, 'qrCode' => base64_encode($qrCodeImage));
  } else {
    $response = array('success' => false, 'error' => 'QR code not found');
  }

  echo json_encode($response);
} else {
  http_response_code(405);
  echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
}
?>