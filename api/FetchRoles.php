<?php
// Set up CORS headers to allow requests from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Allow OPTIONS method to handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $query = "SELECT Links FROM tblrole";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($result) {
            http_response_code(200);
            echo json_encode(array('success' => true, 'roles' => $result));
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'No roles found'));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Error fetching roles: ' . $e->getMessage()));
    }
} else {
    http_response_code(405);
    echo json_encode(array('error' => 'Invalid request method'));
}
?>
