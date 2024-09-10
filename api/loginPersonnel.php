<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];

        try {
            // Modified SQL to include Personnel_ID and other necessary fields
            $sql = "SELECT p.Personnel_ID, p.usr_username, pr.Firstname, pr.Lastname, pr.ProfilePicture, p.jobTitle
                    FROM tblpersonnel p 
                    JOIN tblProfile pr ON p.Profile_ID = pr.Profile_ID 
                    WHERE p.usr_username = :username AND p.usr_password = :password";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);
            $stmt->execute();
            $returnValue = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert ProfilePicture to Base64 if it's not empty
            foreach ($returnValue as &$row) {
                if (!empty($row['ProfilePicture'])) {
                    $row['ProfilePicture'] = base64_encode($row['ProfilePicture']);
                }

                // Determine the role based on jobTitle
                if (in_array($row['jobTitle'], ['Admin', 'Manager', 'Supervisor', 'Coordinator'])) {
                    $row['role'] = 'Administrator';
                } elseif (in_array($row['jobTitle'], ['Security Officer', 'Guard', 'Watchman'])) {
                    $row['role'] = 'Security Guard';
                } else {
                    $row['role'] = 'Unknown';
                }
            }

            http_response_code(200);
            echo json_encode($returnValue);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
        }
    } else {
        echo json_encode(["error" => "Missing username or password"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
