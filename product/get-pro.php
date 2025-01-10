<?php
require_once '../connect/db.php';


$query = "SELECT p.*, c.cat_name 
          FROM products p 
          INNER JOIN category c 
          ON p.p_Category = c.id";

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