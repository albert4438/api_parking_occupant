<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

try {
    $stmt = $conn->prepare("
        SELECT o.Occupant_ID, p.Firstname, p.Lastname, p.Phonenumber, p.ProfilePicture, r.Links
        FROM tbloccupant o
        INNER JOIN tblprofile p ON o.Profile_ID = p.Profile_ID
        LEFT JOIN tblrole r ON o.Role_ID = r.Role_ID
        WHERE o.Status = 'archived'
    ");

    $stmt->execute();
    
    $occupants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert BLOB to Base64
    foreach ($occupants as &$occupant) {
        if (!empty($occupant['ProfilePicture'])) {
            $occupant['ProfilePicture'] = base64_encode($occupant['ProfilePicture']);
        }
    }
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'occupants' => $occupants));
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Error fetching archived occupants: '. $e->getMessage()));
}
?>
