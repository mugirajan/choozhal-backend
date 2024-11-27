<?php
require_once '../db.php';
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

$cat_name = $data['cat_name'];


$query = "INSERT INTO category (cat_name) 
          VALUES ('$cat_name')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Category added successfully']);
  } else {
    echo json_encode(['error' => 'Category added failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}

?>