<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$query = "SELECT * FROM category";

$result = mysqli_query($conn, $query);

$category = [];

while ($row = mysqli_fetch_assoc($result)) {
  $category[] = $row;
}

echo json_encode($category);

?>