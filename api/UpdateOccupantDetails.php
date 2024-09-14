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

$occupantId = $data['occupantId'];
$profile = $data['profile'];

try {
    $conn->beginTransaction();

    // Update username and password in tblpersonnel
    $personnelUpdateQuery = "
        UPDATE tblpersonnel
        SET usr_username = :usr_username,
            usr_password = :usr_password
        WHERE Personnel_ID = (SELECT Personnel_ID FROM tbloccupant WHERE Occupant_ID = :Occupant_ID)
    ";
    $stmt = $conn->prepare($personnelUpdateQuery);
    $stmt->bindParam(':usr_username', $profile['username']);
    $stmt->bindParam(':usr_password', $profile['password']);
    $stmt->bindParam(':Occupant_ID', $occupantId);
    $stmt->execute();

    // Update address and phone number in tblprofile
    $profileUpdateQuery = "
        UPDATE tblprofile
        SET Address = :Address,
            Phonenumber = :Phonenumber
        WHERE Profile_ID = (SELECT Profile_ID FROM tbloccupant WHERE Occupant_ID = :Occupant_ID)
    ";
    $stmt = $conn->prepare($profileUpdateQuery);
    $stmt->bindParam(':Address', $profile['address']);
    $stmt->bindParam(':Phonenumber', $profile['phone']);
    $stmt->bindParam(':Occupant_ID', $occupantId);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
$conn = null;
?>
