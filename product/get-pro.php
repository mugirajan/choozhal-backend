<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$query = "SELECT * FROM products";

$result = mysqli_query($conn, $query);

if (!$result) {
  echo 'Error: ' . mysqli_error($conn);
  exit;
}

$product = [];

while ($row = mysqli_fetch_assoc($result)) {
  $product[] = $row;
}

echo json_encode($product);
?>