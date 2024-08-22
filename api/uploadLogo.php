<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $logoData = file_get_contents($_FILES['logo']['tmp_name']);

    try {
        // Check if logo already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM tbllogo");
        $stmt->execute();
        $logoExists = $stmt->fetchColumn() > 0;

        if ($logoExists) {
            // Update existing logo
            $stmt = $conn->prepare("UPDATE tbllogo SET logo_data = :logoData");
        } else {
            // Insert new logo
            $stmt = $conn->prepare("INSERT INTO tbllogo (logo_data) VALUES (:logoData)");
        }

        $stmt->bindParam(':logoData', $logoData, PDO::PARAM_LOB);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->errorInfo()[2]]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn = null;
?>
