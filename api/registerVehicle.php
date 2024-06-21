<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

include "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['vehicle_type']) && isset($data['vehicle_color']) && isset($data['vehicle_platenumber']) && isset($data['vehicle_model']) && isset($data['vehicle_brand'])) {
        $vehicle_type = $data['vehicle_type'];
        $vehicle_color = $data['vehicle_color'];
        $vehicle_platenumber = $data['vehicle_platenumber'];
        $vehicle_model = $data['vehicle_model'];
        $vehicle_brand = $data['vehicle_brand'];

        $sql = "INSERT INTO vehicle (vehicle_type, vehicle_color, vehicle_platenumber, vehicle_model, vehicle_brand) VALUES (:vehicle_type, :vehicle_color, :vehicle_platenumber, :vehicle_model, :vehicle_brand)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":vehicle_type", $vehicle_type);
        $stmt->bindParam(":vehicle_color", $vehicle_color);
        $stmt->bindParam(":vehicle_platenumber", $vehicle_platenumber);
        $stmt->bindParam(":vehicle_model", $vehicle_model);
        $stmt->bindParam(":vehicle_brand", $vehicle_brand);
        $stmt->execute();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Missing required vehicle data"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
