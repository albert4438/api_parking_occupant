<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include('connection.php');

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

$occupantId = $data['occupantId'];
$personnel = $data['personnel'];
$profile = $data['profile'];

try {
    // Begin transaction
    $conn->beginTransaction();

    // Update personnel details
    $personnelUpdateQuery = "
        UPDATE tblpersonnel
        SET Role_ID = :Role_ID,
            usr_username = :usr_username,
            usr_password = :usr_password,
            jobTitle = :jobTitle,
            Status = :Status
        WHERE Personnel_ID = :Personnel_ID
    ";
    $stmt = $conn->prepare($personnelUpdateQuery);
    $stmt->bindParam(':Role_ID', $personnel['Role_ID']);
    $stmt->bindParam(':usr_username', $personnel['usr_username']);
    $stmt->bindParam(':usr_password', $personnel['usr_password']);
    $stmt->bindParam(':jobTitle', $personnel['jobTitle']);
    $stmt->bindParam(':Status', $personnel['Status']);
    $stmt->bindParam(':Personnel_ID', $occupantId);
    $stmt->execute();

    // Update profile details
    $profileUpdateQuery = "
        UPDATE tblprofile
        SET Firstname = :Firstname,
            Middlename = :Middlename,
            Lastname = :Lastname,
            Birthdate = :Birthdate,
            Address = :Address,
            Phonenumber = :Phonenumber
        WHERE Profile_ID = (SELECT Profile_ID FROM tblpersonnel WHERE Personnel_ID = :Personnel_ID)
    ";
    $stmt = $conn->prepare($profileUpdateQuery);
    $stmt->bindParam(':Firstname', $profile['Firstname']);
    $stmt->bindParam(':Middlename', $profile['Middlename']);
    $stmt->bindParam(':Lastname', $profile['Lastname']);
    $stmt->bindParam(':Birthdate', $profile['Birthdate']);
    $stmt->bindParam(':Address', $profile['Address']);
    $stmt->bindParam(':Phonenumber', $profile['Phonenumber']);
    $stmt->bindParam(':Personnel_ID', $occupantId);
    $stmt->execute();

    // Commit transaction if all queries succeed
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

// Close connection
$conn = null;
?>
