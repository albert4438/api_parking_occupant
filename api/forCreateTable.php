<?php
include "connection.php";

$sql = "CREATE TABLE IF NOT EXISTS occupant (
  id INT AUTO_INCREMENT PRIMARY KEY,
  profile_id INT NOT NULL,
  veh_id INT NOT NULL,
  status VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_profile_id (profile_id),
  INDEX idx_veh_id (veh_id),
  INDEX idx_status (status)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully";
} else {
    echo "Error creating table: ";
}
?>