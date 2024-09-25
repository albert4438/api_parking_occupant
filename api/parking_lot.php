<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include 'connection.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

switch ($action) {
    case 'getLots':
        getLots($conn);
        break;
    case 'addLot':
        addLot($conn);
        break;
    case 'updateLot':
        updateLot($conn);
        break;
}

function getLots($conn) {
    $query = "SELECT Parking_lot_ID, Parking_Lot_Name FROM tblparkinglot";
    try {
        $stmt = $conn->query($query);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function addLot($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    // Ensure the required data is received
    if (!isset($data['Parking_Lot_Name'])) {
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    // Validate received data
    $name = $data['Parking_Lot_Name'];

    if (!is_string($name)) {
        echo json_encode(['error' => 'Invalid request data types']);
        return;
    }

    // Prepare the SQL statement for executing
    $stmt = $conn->prepare("INSERT INTO tblparkinglot (Parking_Lot_Name) VALUES (:name)");
    $stmt->bindParam(':name', $name);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function updateLot($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    // Ensure the required data is received
    if (!isset($data['id']) || !isset($data['Parking_Lot_Name'])) {
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    // Validate received data
    $id = $data['id'];
    $name = $data['Parking_Lot_Name'];

    if (!is_numeric($id) || !is_string($name)) {
        echo json_encode(['error' => 'Invalid request data types']);
        return;
    }

    // Prepare the SQL statement for executing
    $stmt = $conn->prepare("UPDATE tblparkinglot SET Parking_Lot_Name = :name WHERE Parking_lot_ID = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
