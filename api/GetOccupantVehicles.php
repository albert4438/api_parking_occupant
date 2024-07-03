<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Include database connection
include "connection.php";

$occupantId = $_GET['id'];

try {
  $stmt = $conn->prepare("SELECT * FROM tbloccupantvehicle WHERE occupant_id = :occupantId");
  $stmt->bindParam(':occupantId', $occupantId);
  $stmt->execute();
  $occupantVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  http_response_code(200); // OK
  echo json_encode($occupantVehicles);
} catch (PDOException $e) {
  http_response_code(500); // Internal Server Error
  echo json_encode(array('error' => 'Error fetching occupant vehicles: '. $e->getMessage()));
}
?>