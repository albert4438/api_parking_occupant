<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Allow POST and OPTIONS requests
header('Access-Control-Allow-Headers: Content-Type');  // Allow Content-Type header
header('Content-Type: application/json');

// Handle preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);  // No need to process further for OPTIONS request
}

include('connection.php');

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check for required keys and handle missing values
if (!isset($data['occupantId']) || !isset($data['profileId']) || !isset($data['profile'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

$occupantId = $data['occupantId'];
$profileId = $data['profileId'];
$personnelId = isset($data['personnelId']) ? $data['personnelId'] : null;  // Make personnelId optional
$profile = $data['profile'];

try {
    $conn->beginTransaction();

    // Only update username and password if personnelId exists
    if (!empty($personnelId)) {
        $personnelUpdateQuery = "UPDATE tblpersonnel SET";

        $updateFields = [];
        if (!empty($profile['username'])) {
            $updateFields[] = "usr_username = :usr_username";
        }
        if (!empty($profile['password'])) {
            $updateFields[] = "usr_password = :usr_password";
        }

        // If there are fields to update in tblpersonnel
        if (!empty($updateFields)) {
            $personnelUpdateQuery .= " " . implode(", ", $updateFields) . " WHERE Personnel_ID = :personnelId";
            $stmt = $conn->prepare($personnelUpdateQuery);

            // Bind username and password only if they are provided
            if (!empty($profile['username'])) {
                $stmt->bindParam(':usr_username', $profile['username']);
            }
            if (!empty($profile['password'])) {
                $stmt->bindParam(':usr_password', $profile['password']);
            }
            $stmt->bindParam(':personnelId', $personnelId);
            $stmt->execute();
        }
    }

    // Always update the phone number and address in tblprofile
    $profileUpdateQuery = "UPDATE tblprofile SET Phonenumber = :Phonenumber";

    // Only update address if it's provided
    if (!empty($profile['address'])) {
        $profileUpdateQuery .= ", Address = :Address";
    }

    $profileUpdateQuery .= " WHERE Profile_ID = :profileId";
    $stmt = $conn->prepare($profileUpdateQuery);

    // Bind the phone number and address if provided
    $stmt->bindParam(':Phonenumber', $profile['phone']);
    $stmt->bindParam(':profileId', $profileId);
    
    if (!empty($profile['address'])) {
        $stmt->bindParam(':Address', $profile['address']);
    }

    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
    exit; // Ensure that no further code is executed
} catch (PDOException $e) {
    $conn->rollback();

    // Add more specific error logging
    error_log('Error in UpdateOccupantDetails: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()  // Return detailed error in response
    ]);
    exit; // Ensure that the response is returned immediately after handling the error
}

$conn = null;
?>
