<?php
require_once '../db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  $name = $data['name'];
  $description = $data['description'];
  $price = $data['price'];
  $image = $data['image'];
  $serial_number = $data['serial_number'];
  $category_id = $data['category_id'];

  $query = "INSERT INTO product (name, description, price, image, serial_number, category_id) 
             VALUES ('$name', '$description', '$price', '$image', '$serial_number', '$category_id')";

  if (mysqli_query($conn, $query)) {
    echo json_encode(['message' => 'Product added successfully']);
  } else {
    echo json_encode(['error' => 'Product added failed']);
  }
} else {
  echo json_encode(['error' => 'Invalid request method']);
}
?>