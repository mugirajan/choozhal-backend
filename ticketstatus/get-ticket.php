<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$query = "SELECT t.*, u.uname, u.email,u.sales_person,u.sales_person_id,u.pro_name 
          FROM tickets t 
          INNER JOIN users u ON t.user_id = u.id";

$result = $conn->query($query);

if (!$result) {
  echo json_encode(["error" => "Error executing query: " . $conn->error]);
  exit;
}

$tickets = array();
while($row = $result->fetch_assoc()) {
  $tickets[] = $row;
}

echo json_encode($tickets);

$conn->close();
?>