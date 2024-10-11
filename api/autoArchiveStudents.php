<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

try {
    // Get the current date
    $currentDate = date('Y-m-d');

    // Get the latest end_date from tblacademicdates
    $stmt = $conn->prepare("
        SELECT end_date FROM tblacademicdates
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute();
    $academicDate = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the current date is past the end_date
    if ($academicDate && $currentDate >= $academicDate['end_date']) {
        // Archive Occupant(those without a Personnel_ID)
        $archiveStmt = $conn->prepare("
            UPDATE tbloccupant
            SET Status = 'archived'
            WHERE Occupant_ID IN (
                SELECT o.Occupant_ID
                FROM tbloccupant o
                LEFT JOIN tblpersonnel p ON o.Profile_ID = p.Profile_ID
                WHERE p.Personnel_ID IS NULL
            )
        ");
        $archiveStmt->execute();

        // Invalidate the QR codes for archived students
        $invalidateStmt = $conn->prepare("
            UPDATE tbloccupantvehicle
            SET QR_Status = 'INVALID'
            WHERE Occupant_ID IN (
                SELECT o.Occupant_ID
                FROM tbloccupant o
                LEFT JOIN tblpersonnel p ON o.Profile_ID = p.Profile_ID
                WHERE p.Personnel_ID IS NULL
            )
        ");
        $invalidateStmt->execute();

        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => 'Students archived successfully.'));
    } else {
        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => 'No students to archive.'));
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Error archiving students: ' . $e->getMessage()));
}
?>
