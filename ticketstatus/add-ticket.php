<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$sql = "SELECT 
  t.ticket_id, 
  t.message, 
  t.status, 
  t.last_updated,
  u.uname AS user_name, 
  u.email AS user_email, 
  u.phone AS user_phone
FROM 
  tickets t
JOIN 
  users u ON t.user_id = u.id";

$result = $conn->query($sql);

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

echo json_encode($tickets);

$conn->close();
  
?>