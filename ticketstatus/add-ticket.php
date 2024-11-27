<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$sql = "SELECT 
            tickets.ticket_id, 
            tickets.message, 
            tickets.status, 
            tickets.last_updated,
            users.uname AS user_name, 
            users.email AS user_email, 
            users.phone AS user_phone
        FROM 
            tickets
        JOIN 
            users ON tickets.user_id = users.id";

$result = $conn->query($sql);

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

echo json_encode($tickets);

$conn->close();
  
?>