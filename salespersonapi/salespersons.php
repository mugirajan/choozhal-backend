<?php
require_once '../db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$query = "SELECT * FROM admintable";

$result = mysqli_query($conn, $query);

$admintable = [];

while ($row = mysqli_fetch_assoc($result)) {
  $admintable[] = $row;
}

echo json_encode($admintable);

?>