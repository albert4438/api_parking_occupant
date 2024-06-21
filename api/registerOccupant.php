<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['profile_id']) && isset($data['veh_id']) && isset($data['status'])) {
        $profile_id = $data['profile_id'];
        $veh_id = $data['veh_id'];
        $status = $data['status'];

        $sql = "INSERT INTO occupant (profile_id, veh_id, status) VALUES (:profile_id, :veh_id, :status)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":profile_id", $profile_id);
        $stmt->bindParam(":veh_id", $veh_id);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Missing profile_id, veh_id or status"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
