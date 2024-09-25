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
    case 'getInstitutionName':
        getInstitutionName($conn);
        break;
    case 'updateInstitutionName':
        updateInstitutionName($conn);
        break;
    case 'uploadInstitutionName':
        uploadInstitutionName($conn);
        break;
}

function getInstitutionName($conn) {
    $query = "SELECT institution_name FROM tblinstitutionname LIMIT 1"; // Assuming only one institution name entry exists
    try {
        $stmt = $conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function updateInstitutionName($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['institution_name'])) {
        echo json_encode(['error' => 'Institution name is required']);
        return;
    }

    $institutionName = $data['institution_name'];

    $stmt = $conn->prepare("UPDATE tblinstitutionname SET institution_name = :institution_name LIMIT 1");
    $stmt->bindParam(':institution_name', $institutionName);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function uploadInstitutionName($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['institution_name'])) {
        echo json_encode(['error' => 'Institution name is required']);
        return;
    }

    $institutionName = $data['institution_name'];

    $stmt = $conn->prepare("INSERT INTO tblinstitutionname (institution_name) VALUES (:institution_name)");
    $stmt->bindParam(':institution_name', $institutionName);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>