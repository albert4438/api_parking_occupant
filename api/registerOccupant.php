<?php
// Set up CORS headers to allow requests from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Include database connection
include "connection.php";

// Handle OPTIONS request (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate incoming data
    if (validateData($data)) {
        // Sanitize data
        $sanitizedData = sanitizeData($data);

        try {
            // Begin transaction
            $conn->beginTransaction();

            // Insert into tblprofile
            $profileId = insertIntoTblprofile($conn, $sanitizedData);

            // Insert into tbloccupant
            $occupantId = insertIntoTbloccupant($conn, $profileId, $sanitizedData);

            // Retrieve Role_ID based on role name
            $roleId = getRoleId($conn, $sanitizedData['role']);

            // Insert into tblpersonnel for Admin/Guard
            if ($roleId && isAdminOrGuard($sanitizedData['role'])) {
                insertIntoTblpersonnel($conn, $profileId, $roleId, $sanitizedData, $occupantId);
            }

            // Commit transaction
            $conn->commit();

            // Return success response
            http_response_code(201); // Created
            echo json_encode(array('success' => true, 'message' => 'Registration successful'));
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();

            // Log detailed error message
            error_log('PDOException - '. $e->getMessage());

            // Return error response
            http_response_code(500); // Internal Server Error
            echo json_encode(array('error' => 'Registration failed: '. $e->getMessage()));
        }
    } else {
        // Return bad request response if required fields are missing
        http_response_code(400); // Bad Request
        echo json_encode(array('error' => 'Incomplete data provided'));
    }
} else {
    // Return method not allowed if not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('error' => 'Invalid request method'));
}

// Helper functions

function validateData($data) {
    return isset($data['role']) &&
           isset($data['firstName']) &&
           isset($data['lastName']) &&
           isset($data['birthdate']) &&
           isset($data['address']) &&
           isset($data['username']) &&
           isset($data['password']) &&
           isset($data['jobTitle']) &&
           isset($data['status']);
}

function sanitizeData($data) {
    return [
        'role' => htmlspecialchars(strip_tags($data['role'])),
        'firstName' => htmlspecialchars(strip_tags($data['firstName'])),
        'middleName' => isset($data['middleName'])? htmlspecialchars(strip_tags($data['middleName'])) : null,
        'lastName' => htmlspecialchars(strip_tags($data['lastName'])),
        'birthdate' => htmlspecialchars(strip_tags($data['birthdate'])),
        'address' => htmlspecialchars(strip_tags($data['address'])),
        'username' => htmlspecialchars(strip_tags($data['username'])),
        'password' => htmlspecialchars(strip_tags($data['password'])),
        'jobTitle' => htmlspecialchars(strip_tags($data['jobTitle'])),
        'status' => htmlspecialchars(strip_tags($data['status'])),
        'phonenumber' => isset($data['phonenumber'])? htmlspecialchars(strip_tags($data['phonenumber'])) : null,
    ];
}

function insertIntoTblprofile($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tblprofile (Firstname, Middlename, Lastname, Birthdate, Address, Phonenumber)
        VALUES (:firstName, :middleName, :lastName, :birthdate, :address, :phonenumber)
    ");
    $stmt->bindParam(':firstName', $data['firstName']);
    $stmt->bindParam(':middleName', $data['middleName']);
    $stmt->bindParam(':lastName', $data['lastName']);
    $stmt->bindParam(':birthdate', $data['birthdate']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':phonenumber', $data['phonenumber']);
    $stmt->execute();
    return $conn->lastInsertId();
}

function insertIntoTbloccupant($conn, $profileId, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tbloccupant (Profile_ID, Status)
        VALUES (:profileId, :status)
    ");
    $stmt->bindParam(':profileId', $profileId);
    $stmt->bindParam(':status', $data['status']);
    $stmt->execute();
    return $conn->lastInsertId();
}

function getRoleId($conn, $role) {
    $stmt = $conn->prepare("SELECT Role_ID FROM tblrole WHERE Links = :role");
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function isAdminOrGuard($role) {
    return $role === 'Admin' || $role === 'Guard';
}

function insertIntoTblpersonnel($conn, $profileId, $roleId, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tblpersonnel (Profile_ID, Role_ID, usr_username, usr_password, jobTitle, Status)
        VALUES (:profileId, :roleId, :username, :password, :jobTitle, :status)
    ");
    $stmt->bindParam(':profileId', $profileId);
    $stmt->bindParam(':roleId', $roleId);
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':password', $data['password']);
    $stmt->bindParam(':jobTitle', $data['jobTitle']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->execute();
}

?>
