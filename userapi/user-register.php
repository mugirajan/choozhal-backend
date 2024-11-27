<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

$uname = $data['uname'];
$phone = $data['phone'];
$email = $data['email'];
$pro_id = $data['pro_id'];
$pro_name = $data['pro_name'];
$sales_person = $data['sales_person'];
$area = $data['area'];
$state = $data['state'];
$district = $data['district'];
$city = $data['city'];
$address = $data['address'];

  $query = "INSERT INTO users (uname, phone, email, pro_name, pro_id, sales_person, area, state, district, city, address) VALUES ('$uname', '$phone', '$email', '$pro_name', '$pro_id', '$sales_person', '$area', '$state', '$district', '$city', '$address')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'User registered successfully']);
  } else {
    echo json_encode(['error' => 'Registration failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}

?>