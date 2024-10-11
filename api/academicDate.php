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
    case 'getDates':
        getDates($conn);
        break;
    case 'addDate':
        addDate($conn);
        break;
    case 'updateDate': // New case for updating dates
        updateDate($conn);
        break;
}

function getDates($conn) {
    $query = "SELECT id, start_date, end_date FROM tblacademicdates";
    try {
        $stmt = $conn->query($query);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function addDate($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['start_date']) || !isset($data['end_date'])) {
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
        echo json_encode(['error' => 'Invalid date format']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO tblacademicdates (start_date, end_date) VALUES (:start_date, :end_date)");
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function updateDate($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id']) || !isset($data['start_date']) || !isset($data['end_date'])) {
        echo json_encode(['error' => 'Invalid request data']);
        return;
    }

    $id = $data['id'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
        echo json_encode(['error' => 'Invalid date format']);
        return;
    }

    $stmt = $conn->prepare("UPDATE tblacademicdates SET start_date = :start_date, end_date = :end_date WHERE id = :id");
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>