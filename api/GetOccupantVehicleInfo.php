<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        // Debugging: Log received data
        error_log("Received data: " . print_r($data, true));

        if (isset($data['occupantId']) && isset($data['vehicleId'])) {
            $occupantId = htmlspecialchars(strip_tags($data['occupantId']));
            $vehicleId = htmlspecialchars(strip_tags($data['vehicleId']));

            // Begin transaction
            $conn->beginTransaction();

            // Retrieve occupant, vehicle, and profile information
            $stmt = $conn->prepare("
                SELECT o.Occupant_ID, p.Firstname, p.Lastname, p.Phonenumber, p.Address, 
                       v.Vehicle_Type, v.Vehicle_Color, v.Vehicle_Platenumber, v.Vehicle_Model, v.Vehicle_Brand,
                       p.profilePicture
                FROM tbloccupant o
                INNER JOIN tbloccupantvehicle ov ON o.Occupant_ID = ov.Occupant_ID
                INNER JOIN tblvehicle v ON ov.Vehicle_ID = v.Vehicle_ID
                INNER JOIN tblprofile p ON o.Profile_ID = p.Profile_ID
                WHERE o.Occupant_ID = :occupantId AND v.Vehicle_ID = :vehicleId
            ");
            $stmt->bindParam(':occupantId', $occupantId);
            $stmt->bindParam(':vehicleId', $vehicleId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Convert profilePicture to base64
                if ($row['profilePicture'] !== null) {
                    $row['profilePicture'] = base64_encode($row['profilePicture']);
                }

                // Commit transaction
                $conn->commit();

                // Debugging: Log the fetched data
                error_log("Fetched data: " . print_r($row, true));

                echo json_encode([
                    "status" => "success",
                    "data" => $row,
                ]);
            } else {
                // Rollback transaction
                $conn->rollBack();

                echo json_encode([
                    "status" => "error",
                    "message" => "No data found",
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input",
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request method",
        ]);
    }
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollBack();

    error_log("Error: " . $e->getMessage());

    echo json_encode([
        "status" => "error",
        "message" => "An error occurred: " . $e->getMessage(),
    ]);
}
?>
