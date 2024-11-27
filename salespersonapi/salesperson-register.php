<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'];
$phone = $data['phone'];
$email = $data['email'];
$password = $data['password'];
$branch = $data['branch'];
$role = $data['role'];
$region = $data['region'];
$area = $data['area'];
$state = $data['state'];
$district = $data['district'];
$city = $data['city'];
$address = $data['address'];

$query = "INSERT INTO admintable (name, phone, email, password, branch, role, region, area, state, district, city, address) 
          VALUES ('$name', '$phone', '$email', '$password', '$branch', '$role', '$region', '$area', '$state', '$district', '$city', '$address')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Admin registered successfully']);
  } else {
    echo json_encode(['error' => 'Admin Registration failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}

?>