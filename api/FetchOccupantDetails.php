<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if (isset($_SERVER['REQUEST_METHOD'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
} else {
    echo json_encode(['error' => 'This script must be run in a web server context']);
    exit();
}

include "connection.php";

$occupantId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($occupantId > 0) {
    $stmt = $conn->prepare("SELECT o.Profile_ID 
                            FROM tbloccupant o
                            WHERE o.Occupant_ID = ?");
    $stmt->execute([$occupantId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $profileId = $result['Profile_ID'];

        $stmt = $conn->prepare("SELECT * 
                                FROM tblprofile 
                                WHERE Profile_ID = ?");
        $stmt->execute([$profileId]);
        $profileResult = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            'profile' => [
                'Firstname' => $profileResult['Firstname'],
                'Middlename' => $profileResult['Middlename'],
                'Lastname' => $profileResult['Lastname'],
                'Birthdate' => $profileResult['Birthdate'],
                'Address' => $profileResult['Address'],
                'Phonenumber' => $profileResult['Phonenumber']
            ]
        ];

        $stmt = $conn->prepare("SELECT p.*, r.* 
                                FROM tblpersonnel p
                                JOIN tblrole r ON p.Role_ID = r.Role_ID
                                WHERE p.Profile_ID = ?");
        $stmt->execute([$profileId]);
        $personnelResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($personnelResult) {
            $response['personnel'] = [
                'Role_ID' => $personnelResult['Role_ID'],
                'usr_username' => $personnelResult['usr_username'],
                'usr_password' => $personnelResult['usr_password'],
                'jobTitle' => $personnelResult['jobTitle'],
                'Status' => $personnelResult['Status']
            ];
        } else {
            $response['personnel'] = [];
        }

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Occupant not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid occupant ID']);
}
?>