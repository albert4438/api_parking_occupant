<?php
// Set up CORS headers to allow requests from any origin
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Handle OPTIONS request (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Include database connection
require_once 'connection.php';

// Read input JSON data
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(array("error" => "Invalid input data"));
    exit();
}

// Validate and sanitize input
$role_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$role_name = isset($data->role) ? trim($data->role) : null;

if (!$role_id || !$role_name) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(array("error" => "Role ID and Role Name are required"));
    exit();
}

try {
    // Update role in the database using prepared statement
    $stmt = $conn->prepare("UPDATE tblrole SET RoleName = :role_name WHERE Role_ID = :role_id");
    $stmt->bindParam(':role_name', $role_name);
    $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        echo json_encode(array("success" => true, "message" => "Role updated successfully"));
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(array("error" => "Failed to update role"));
    }
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(array("error" => "Database error: " . $e->getMessage()));
}

// Close the connection
$conn = null;
?>
