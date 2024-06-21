<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "connection.php";

$sql = "SELECT * FROM park_logs";
$stmt = $conn->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($logs);
?>
