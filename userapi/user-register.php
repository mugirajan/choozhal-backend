<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $first_name = $data['first_name'];
  $last_name = $data['last_name'];
  $email = $data['email'];
  $mobile_no = $data['mobile_no'];
  $dob = $data['dob'];
  $gender = $data['gender'];
  $address = $data['address'];
  $area = $data['area'];
  $city = $data['city'];
  $district = $data['district'];
  $state = $data['state'];
  $pincode = $data['pincode'];
  $profile_pic = $data['profile_pic'];
  $admin_id = $data['created_by'];

  $query = "INSERT INTO customers (first_name, last_name, email, mobile_no, dob, gender, address, area, city, district, state, pincode, profile_pic, created_by) VALUES ('$first_name', '$last_name', '$email', '$mobile_no', '$dob', '$gender', '$address', '$area', '$city', '$district', '$state', '$pincode', '$profile_pic', '$admin_id')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Customer registered successfully']);
  } else {
    echo json_encode(['error' => 'Registration failed: ' . mysqli_error($conn)]);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>