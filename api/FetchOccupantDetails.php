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

        // Check if the profile picture exists
        $profilePictureUrl = '';
        if ($profileResult['profilePicture']) {
            $profilePictureBase64 = base64_encode($profileResult['profilePicture']);
            $profilePictureUrl = 'data:image/jpeg;base64,' . $profilePictureBase64;
        }

        // Parse the address back into region, province, municipality, and barangay
        $addressParts = explode(',', $profileResult['Address']);
        $region = trim($addressParts[0] ?? '');
        $province = trim($addressParts[1] ?? '');
        $municipality = trim($addressParts[2] ?? '');
        $barangay = trim($addressParts[3] ?? '');
        

        $response = [
            'profile' => [
                'Profile_ID' => $profileId, // Added Profile_ID
                'Firstname' => $profileResult['Firstname'],
                'Middlename' => $profileResult['Middlename'],
                'Lastname' => $profileResult['Lastname'],
                'Birthdate' => $profileResult['Birthdate'],
                'Address' => $profileResult['Address'],
                'Phonenumber' => $profileResult['Phonenumber'],
                'ProfilePicture' => $profilePictureUrl,
                'Region' => $region,                // Added Region
                'Province' => $province,            // Added Province
                'Municipality' => $municipality,    // Added Municipality
                'Barangay' => $barangay             // Added Barangay
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
                'Personnel_ID' => $personnelResult['Personnel_ID'], // Added Personnel_ID
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
