<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

$stmt = $conn->prepare("SELECT logo_data FROM tbllogo LIMIT 1");
$stmt->execute();
$logoData = $stmt->fetchColumn();

if ($logoData === false) {
    echo json_encode(['error' => 'No logo found or query failed']);
    exit();
}

$response = ['logoData' => null];
if ($logoData) {
    $response['logoData'] = base64_encode($logoData);
}

echo json_encode($response);

$conn = null;
?>
