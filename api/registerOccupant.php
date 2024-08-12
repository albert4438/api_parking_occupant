<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (validateData($data)) {
        $sanitizedData = sanitizeData($data);

        if (!validateImage($sanitizedData['image'])) {
            http_response_code(400);
            echo json_encode(array('error' => 'Invalid image format or size.'));
            exit();
        }

        try {
            $conn->beginTransaction();
            $profileId = insertIntoTblprofile($conn, $sanitizedData);
            $occupantId = insertIntoTbloccupant($conn, $profileId, $sanitizedData);
            $roleId = getRoleId($conn, $sanitizedData['role']);

            if ($roleId && isAdminOrGuard($sanitizedData['role'])) {
                insertIntoTblpersonnel($conn, $profileId, $roleId, $sanitizedData, $occupantId);
            }

            $conn->commit();
            http_response_code(201);
            echo json_encode(array('success' => true, 'message' => 'Registration successful'));
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log('PDOException - '. $e->getMessage());
            http_response_code(500);
            echo json_encode(array('error' => 'Registration failed: '. $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Incomplete data provided'));
    }
} else {
    http_response_code(405);
    echo json_encode(array('error' => 'Invalid request method'));
}

function validateData($data) {
    return isset($data['role']) &&
           isset($data['firstName']) &&
           isset($data['lastName']) &&
           isset($data['birthdate']) &&
           isset($data['address']) &&
           isset($data['phonenumber']) &&
           isset($data['image']) &&
           (isAdminOrGuard($data['role']) ?
               isset($data['username']) && 
               isset($data['password']) && 
               isset($data['jobTitle']) && 
               isset($data['status']) 
               : true);
}

function sanitizeData($data) {
    return [
        'role' => htmlspecialchars(strip_tags($data['role'])),
        'firstName' => htmlspecialchars(strip_tags($data['firstName'])),
        'middleName' => isset($data['middleName']) ? htmlspecialchars(strip_tags($data['middleName'])) : null,
        'lastName' => htmlspecialchars(strip_tags($data['lastName'])),
        'birthdate' => htmlspecialchars(strip_tags($data['birthdate'])),
        'address' => htmlspecialchars(strip_tags($data['address'])),
        'phonenumber' => htmlspecialchars(strip_tags($data['phonenumber'])),
        'image' => $data['image'], // No longer decode the image here
        'username' => isset($data['username']) ? htmlspecialchars(strip_tags($data['username'])) : null,
        'password' => isset($data['password']) ? htmlspecialchars(strip_tags($data['password'])) : null,
        'jobTitle' => isset($data['jobTitle']) ? htmlspecialchars(strip_tags($data['jobTitle'])) : null,
        'status' => isset($data['status']) ? htmlspecialchars(strip_tags($data['status'])) : null,
    ];
}

function validateImage($imageData) {
    $imageInfo = getimagesizefromstring(base64_decode($imageData));
    $validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    return $imageInfo && in_array($imageInfo['mime'], $validTypes) && strlen(base64_decode($imageData)) <= $maxSize;
}

function insertIntoTblprofile($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tblprofile (Firstname, Middlename, Lastname, Birthdate, Address, Phonenumber, ProfilePicture)
        VALUES (:firstName, :middleName, :lastName, :birthdate, :address, :phonenumber, :profilePicture)
    ");
    $stmt->bindParam(':firstName', $data['firstName']);
    $stmt->bindParam(':middleName', $data['middleName']);
    $stmt->bindParam(':lastName', $data['lastName']);
    $stmt->bindParam(':birthdate', $data['birthdate']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':phonenumber', $data['phonenumber']);
    $stmt->bindParam(':profilePicture', base64_decode($data['image']), PDO::PARAM_LOB); // Decode the base64 encoded image
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
    return $role === 'Administrator' || $role === 'Security Guard';
}

function insertIntoTblpersonnel($conn, $profileId, $roleId, $data, $occupantId) {
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
