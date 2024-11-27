<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$query = "SELECT p.*, c.cat_name AS cat_name
           FROM product p
           JOIN category c ON p.category_id = c.id";

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