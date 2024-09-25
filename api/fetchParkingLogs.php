<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

// Retrieve filters from GET request
$actionType = isset($_GET['actionType']) ? $_GET['actionType'] : 'All';
$vehicleType = isset($_GET['vehicleType']) ? $_GET['vehicleType'] : null;
$vehicleBrand = isset($_GET['vehicleBrand']) ? $_GET['vehicleBrand'] : null;
$vehicleModel = isset($_GET['vehicleModel']) ? $_GET['vehicleModel'] : null;
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

try {
    // Updated query to fetch parking lot information
    $query = "
        SELECT 
            pl.log_id,
            CONCAT(pfPersonnel.Firstname, ' ', pfPersonnel.Middlename, ' ', pfPersonnel.Lastname) AS personnel_fullname,
            CONCAT(v.Vehicle_Brand, ' ', v.Vehicle_Model, ' (', v.Vehicle_Platenumber, ')') AS vehicle,
            CONCAT(pfOccupant.Firstname, ' ', pfOccupant.Middlename, ' ', pfOccupant.Lastname) AS occupant_fullname,
            pl.action_type,
            pl.timestamp,
            lot.Parking_Lot_Name AS parking_lot_name, -- Fetch parking lot name
            v.Vehicle_Type AS vehicle_type,
            v.Vehicle_Brand AS vehicle_brand,
            v.Vehicle_Model AS vehicle_model
        FROM tblparkinglog pl
        JOIN tblpersonnel pn ON pl.Personnel_ID = pn.Personnel_ID
        JOIN tblprofile pfPersonnel ON pn.Profile_ID = pfPersonnel.Profile_ID
        JOIN tblvehicle v ON pl.Vehicle_ID = v.Vehicle_ID
        JOIN tbloccupant oc ON pl.Occupant_ID = oc.Occupant_ID
        JOIN tblprofile pfOccupant ON oc.Profile_ID = pfOccupant.Profile_ID
        JOIN tblparkinglot lot ON pl.Parking_lot_ID = lot.Parking_lot_ID -- Join with parking lot table
        WHERE 1 = 1
    ";

    // Add filters if applicable
    if ($actionType !== 'All') {
        $query .= " AND pl.action_type = :actionType";
    }
    if ($vehicleType) {
        $query .= " AND v.Vehicle_Type = :vehicleType";
    }
    if ($vehicleBrand) {
        $query .= " AND v.Vehicle_Brand = :vehicleBrand";
    }
    if ($vehicleModel) {
        $query .= " AND v.Vehicle_Model = :vehicleModel";
    }
    if ($startDate && $endDate) {
        $query .= " AND pl.timestamp BETWEEN :startDate AND :endDate";
    }

    // Sorting the results by timestamp
    $query .= " ORDER BY pl.timestamp DESC";

    $stmt = $conn->prepare($query);

    // Bind values for the filters
    if ($actionType !== 'All') {
        $stmt->bindParam(':actionType', $actionType);
    }
    if ($vehicleType) {
        $stmt->bindParam(':vehicleType', $vehicleType);
    }
    if ($vehicleBrand) {
        $stmt->bindParam(':vehicleBrand', $vehicleBrand);
    }
    if ($vehicleModel) {
        $stmt->bindParam(':vehicleModel', $vehicleModel);
    }
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    // Execute the query
    $stmt->execute();

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(array('success' => true, 'logs' => $logs));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Database error: '. $e->getMessage()));
}
