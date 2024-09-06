<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

try {
    $stmt = $conn->prepare("
        SELECT 
            pl.log_id,
            CONCAT(pfPersonnel.Firstname, ' ', pfPersonnel.Middlename, ' ', pfPersonnel.Lastname) AS personnel_fullname,
            CONCAT(v.Vehicle_Brand, ' ', v.Vehicle_Model, ' (', v.Vehicle_Platenumber, ')') AS vehicle,
            CONCAT(pfOccupant.Firstname, ' ', pfOccupant.Middlename, ' ', pfOccupant.Lastname) AS occupant_fullname,
            pl.action_type,
            pl.timestamp
        FROM tblparkinglog pl
        JOIN tblpersonnel pn ON pl.Personnel_ID = pn.Personnel_ID
        JOIN tblprofile pfPersonnel ON pn.Profile_ID = pfPersonnel.Profile_ID
        JOIN tblvehicle v ON pl.Vehicle_ID = v.Vehicle_ID
        JOIN tbloccupant oc ON pl.Occupant_ID = oc.Occupant_ID
        JOIN tblprofile pfOccupant ON oc.Profile_ID = pfOccupant.Profile_ID
        ORDER BY pl.timestamp DESC
    ");

    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode(array('success' => true, 'logs' => $logs));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Database error: '. $e->getMessage()));
}
?>
