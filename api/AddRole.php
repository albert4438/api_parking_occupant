<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

include "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (validateData($data)) {
        $sanitizedData = sanitizeData($data);

        try {
            $conn->beginTransaction();

            // Insert into tblrole
            $roleId = insertIntoTblrole($conn, $sanitizedData);

            $conn->commit();

            http_response_code(201); // Created
            echo json_encode(array('success' => true, 'message' => 'Registration successful'));
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log('PDOException - '. $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(array('error' => 'Registration failed: '. $e->getMessage()));
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array('error' => 'Incomplete data provided'));
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('error' => 'Invalid request method'));
}

function validateData($data) {
    return isset($data['role']);
}

function sanitizeData($data) {
    return [
        'role' => htmlspecialchars(strip_tags($data['role'])),
    ];
}

function insertIntoTblrole($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tblrole (Links) -- Assuming Links is the correct column for your data
        VALUES (:links)
    ");
    $stmt->bindParam(':links', $data['role']); // Make sure this matches your database schema
    $stmt->execute();
    return $conn->lastInsertId();
}
?>
