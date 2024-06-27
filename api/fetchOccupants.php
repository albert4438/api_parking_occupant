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

include "connection.php";

try {
    $stmt = $conn->prepare("
        SELECT p.Firstname, p.Lastname, p.Phonenumber
FROM tbloccupant o
        INNER JOIN tblprofile p ON o.Profile_ID = p.Profile_ID
    ");
    $stmt->execute();
    
    $occupants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'occupants' => $occupants));
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Error fetching occupants: '. $e->getMessage()));
}
?>