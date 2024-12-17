<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $pro_id = $data['pro_id'];
  $user_id = $data['user_id'];
  $s_num = $data['snum'];
  $admin_id = $data['admin_id'];

  $query = "INSERT INTO sale_records (pro_id, user_id, serial_num, admin_id, created_by) VALUES ('$pro_id', '$user_id', '$s_num', '$admin_id', '$admin_id')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'User registered successfully']);
  } else {
    echo json_encode(['error' => 'Registration failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>