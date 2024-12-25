<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $usr_fname = $data['usr_fname'];
  $usr_lname = $data['usr_lname'];
  $usr_email = $data['usr_email'];
  $usr_pass = $data['usr_pass'];
  $usr_role = $data['usr_role'];
  $usr_dob = $data['usr_dob'];
  $address = $data['address'];
  $usr_phone = $data['usr_phone'];
  $date_of_joining = $data['date_of_joining'];
  $branch = $data['branch'];
  $area = $data['area'];
  $is_active = isset($data['is_active']) ? $data['is_active'] : 1; 
  $created_by = $data['usr_fname'];
  $updated_by = $data['usr_fname'];
  $created_at = date('Y-m-d H:i:s');
  $updated_at = date('Y-m-d H:i:s');
  $is_deleted = isset($data['is_deleted']) ? $data['is_deleted'] : 0; 

  $query = "INSERT INTO usr_details (usr_fname, usr_lname, usr_email, usr_pass, usr_role, usr_dob, address, usr_phone, date_of_joining, branch, area, is_active, created_by, updated_by, created_at, updated_at, is_deleted) 
            VALUES ('$usr_fname', '$usr_lname', '$usr_email', '$usr_pass', '$usr_role', '$usr_dob', '$address', '$usr_phone', '$date_of_joining', '$branch', '$area', '$is_active', '$created_by', '$updated_by', '$created_at', '$updated_at', '$is_deleted')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Admin registered successfully']);
  } else {
    echo json_encode(['error' => 'Admin Registration failed', 'details' => mysqli_error($conn)]);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
$conn->close();
?>
