<?php
// DeleteRole.php

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

// Get Role_ID from request
$Role_ID = $_GET['id'];

try {
    // Prepare delete role query
    $stmt = $conn->prepare("DELETE FROM tblrole WHERE Role_ID = :Role_ID");
    $stmt->bindParam(':Role_ID', $Role_ID);
    $stmt->execute();

    // Prepare delete associated personnel records query
    $stmt = $conn->prepare("DELETE FROM tblpersonnel WHERE Role_ID = :Role_ID");
    $stmt->bindParam(':Role_ID', $Role_ID);
    $stmt->execute();

    $response = array("success" => true, "message" => "Role deleted successfully");
} catch (PDOException $e) {
    $response = array("success" => false, "error" => $e->getMessage());
}

// Close connection
$conn = null;

// Output response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
?>