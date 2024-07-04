<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With");

// Include database connection
include "connection.php";

// Handle OPTIONS request (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

try {
  // Handle POST request
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // Get POST data
      $data = json_decode(file_get_contents("php://input"), true);

      // Validate incoming data
      if (validateData($data)) {
          // Sanitize data
          $sanitizedData = sanitizeData($data);

          try {
              // Begin transaction
              $conn->beginTransaction();

              // Insert into tblvehicle
              $vehicleId = insertIntoTblvehicle($conn, $sanitizedData);

              // Insert into tbloccupantvehicle
              insertIntoTblOccupantVehicle($conn, $sanitizedData['occupantId'], $vehicleId);

              // Commit transaction
              $conn->commit();

              // Return success response
              http_response_code(201); // Created
              echo json_encode(array('success' => true, 'message' => 'Vehicle added successfully'));
          } catch (PDOException $e) {
              // Rollback transaction on error
              $conn->rollBack();

              // Log detailed error message
              error_log('PDOException - '. $e->getMessage());
              error_log('Error adding vehicle: '. print_r($data, true));
              error_log('SQL query: '. $stmt->queryString);

              // Return error response
              http_response_code(500); // Internal Server Error
              echo json_encode(array('error' => 'Error adding vehicle: '. $e->getMessage()));
          } catch (Exception $e) {
              // Log error message
              error_log('Exception - '. $e->getMessage());

              // Return error response
              http_response_code(500); // Internal Server Error
              echo json_encode(array('error' => 'Error adding vehicle: '. $e->getMessage()));
          }
      } else {
          // Return bad request response if required fields are missing
          http_response_code(400); // Bad Request
          echo json_encode(array('error' => 'Incomplete data provided'));
      }
  }
} catch (Exception $e) {
  // Log error message
  error_log('Exception - '. $e->getMessage());

  // Return error response
  http_response_code(500); // Internal Server Error
  echo json_encode(array('error' => 'Error adding vehicle: '. $e->getMessage()));
}

function validateData($data) {
    return isset($data['vehicleType']) &&
           isset($data['vehicleColor']) &&
           isset($data['vehiclePlateNumber']) &&
           isset($data['vehicleModel']) &&
           isset($data['vehicleBrand']) &&
           isset($data['occupantId']);
}

function sanitizeData($data) {
    return [
        'vehicleType' => htmlspecialchars(strip_tags($data['vehicleType'])),
        'vehicleColor' => htmlspecialchars(strip_tags($data['vehicleColor'])),
        'vehiclePlateNumber' => htmlspecialchars(strip_tags($data['vehiclePlateNumber'])),
        'vehicleModel' => htmlspecialchars(strip_tags($data['vehicleModel'])),
        'vehicleBrand' => htmlspecialchars(strip_tags($data['vehicleBrand'])),
        'occupantId' => htmlspecialchars(strip_tags($data['occupantId'])),
    ];
}

function insertIntoTblvehicle($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO tblvehicle (Vehicle_Type, Vehicle_Color, Vehicle_Platenumber, Vehicle_Model, Vehicle_Brand)
        VALUES (:vehicleType, :vehicleColor, :vehiclePlateNumber, :vehicleModel, :vehicleBrand)
    ");
    $stmt->bindParam(':vehicleType', $data['vehicleType']);
    $stmt->bindParam(':vehicleColor', $data['vehicleColor']);
    $stmt->bindParam(':vehiclePlateNumber', $data['vehiclePlateNumber']);
    $stmt->bindParam(':vehicleModel', $data['vehicleModel']);
    $stmt->bindParam(':vehicleBrand', $data['vehicleBrand']);
    $stmt->execute();

    // Return the last inserted ID
    return $conn->lastInsertId();
}

function insertIntoTblOccupantVehicle($conn, $occupantId, $vehicleId) {
    $stmt = $conn->prepare("
        INSERT INTO tbloccupantvehicle (Occupant_ID, Vehicle_ID)
        VALUES (:occupantId, :vehicleId)
    ");
    $stmt->bindParam(':occupantId', $occupantId);
    $stmt->bindParam(':vehicleId', $vehicleId);
    $stmt->execute();
}
?>